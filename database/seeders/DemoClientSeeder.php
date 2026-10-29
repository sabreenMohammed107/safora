<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\ClientStatus;
use App\Enums\ClientUserRole;
use App\Enums\SectorSource;
use App\Models\Client;
use App\Models\ClientBranch;
use App\Models\ClientRole;
use App\Models\Plan;
use App\Models\Sector;
use App\Models\SectorCategory;
use Illuminate\Database\Seeder;

/**
 * Local demo data mirroring the prototype's seedData() (docs/html-assets/11_ClientCenter.html)
 * and the test accounts in docs/sprints/00_README.md. Password for every account: Safora@2026.
 * Requires the reference seeders. Skips clients that already exist.
 */
class DemoClientSeeder extends Seeder
{
    public const string PASSWORD = 'Safora@2026';

    public function run(): void
    {
        $clients = [
            ['CL-1001', 'مستشفى الرعاية التخصصي', 'د. سارة القحطاني', '+966 50 123 4567', 'ops@alreaya-hospital.example.com', 'CAT-01', '1010456789', ClientStatus::Active, 220,
                ['plan' => Plan::ISO, 'cycle' => BillingCycle::Annual, 'start' => -165, 'end' => 200]],
            ['CL-1002', 'مطاعم نكهة الشام', 'خالد العتيبي', '+966 55 987 6543', 'info@nakha-sham.example.com', 'CAT-02', '4030123456', ClientStatus::Active, 75,
                ['plan' => Plan::HACCP, 'cycle' => BillingCycle::Monthly, 'start' => -18, 'end' => 12]],
            ['CL-1003', 'فندق واحة الرياض', 'نورة الدوسري', '+966 54 222 3344', 'quality@waha-hotel.example.com', 'CAT-03', '1010998877', ClientStatus::Pending, 2, null],
            ['CL-1004', 'مصنع ألبان الوادي', 'م. فهد الشمري', '+966 56 777 8899', 'fsms@alwadi-dairy.example.com', 'CAT-04', '300123456700003', ClientStatus::Pending, 1, null],
            ['CL-1005', 'مطبخ الضيافة المركزي', 'عبدالله الحربي', '+966 53 444 5566', 'admin@diyafa-kitchen.example.com', 'CAT-05', '2050112233', ClientStatus::Suspended, 140,
                ['plan' => Plan::HACCP, 'cycle' => BillingCycle::Annual, 'start' => -130, 'end' => 235, 'reason' => 'تأخر سداد الفاتورة الثانية أكثر من 30 يوماً']],
            ['CL-1006', 'مخبز السنابل الذهبية', 'ريم الزهراني', '+966 58 111 2233', 'hello@sanabel-bakery.example.com', 'CAT-06', '4031556677', ClientStatus::Active, 60,
                ['plan' => Plan::ISO, 'cycle' => BillingCycle::Monthly, 'start' => -35, 'end' => -5]],
            ['CL-1007', 'مطعم البحر الأزرق', 'ماجد السبيعي', '+966 59 333 1122', 'contact@blue-sea.example.com', 'CAT-02', '4030778899', ClientStatus::Rejected, 20,
                ['reason' => 'رقم السجل التجاري غير مطابق لاسم المنشأة المسجل']],
        ];

        foreach ($clients as [$code, $company, $contact, $phone, $email, $category, $cr, $status, $registeredDaysAgo, $terms]) {
            if (Client::where('code', $code)->exists()) {
                continue;
            }

            $client = $this->createClient($code, $company, $contact, $phone, $email, $category, $cr, $status, $registeredDaysAgo, $terms ?? []);

            if ($code === 'CL-1001') {
                $this->addHospitalBranchTeam($client);
            }
        }

        $this->grantSector('CL-1001', 'Bakery/Pasta', SectorSource::Added);
        $this->grantSector('CL-1002', 'Infant/Dietetic', SectorSource::Granted);
    }

    /**
     * @param  array{plan?: string, cycle?: BillingCycle, start?: int, end?: int, reason?: string}  $terms
     */
    private function createClient(string $code, string $company, string $contact, string $phone, string $email, string $category, string $cr, ClientStatus $status, int $registeredDaysAgo, array $terms): Client
    {
        $registeredAt = now()->subDays($registeredDaysAgo)->setTime(9, 12);

        $client = Client::create([
            'company' => $company,
            'cr_number' => $cr,
            'contact_name' => $contact,
            'email' => $email,
            'phone' => $phone,
            'sector_category_id' => SectorCategory::where('code', $category)->value('id'),
            'status' => $status,
            'status_reason' => $terms['reason'] ?? null,
            'registered_at' => $registeredAt,
            'approved_at' => $status === ClientStatus::Pending || $status === ClientStatus::Rejected ? null : $registeredAt->copy()->addDay(),
        ]);
        $client->forceFill(['code' => $code])->save();

        $client->branches()->create(['code' => 'BR-01', 'name' => 'الفرع الرئيسي', 'city' => 'الرياض', 'manager' => $contact, 'phone' => $phone, 'active' => true]);
        ClientRole::createDefaultsFor($client);

        $client->users()->create([
            'name' => $contact,
            'email' => $email,
            'role' => ClientUserRole::Owner,
            'password' => self::PASSWORD,
            'locale' => 'ar',
            'active' => true,
        ]);

        if (isset($terms['plan'])) {
            $client->subscriptions()->create([
                'plan_id' => Plan::where('code', $terms['plan'])->value('id'),
                'cycle' => $terms['cycle'],
                'discount' => 0,
                'start_date' => today()->addDays($terms['start']),
                'end_date' => today()->addDays($terms['end']),
            ]);
        }

        $client->logActivity('registered', actorName: $contact);

        match ($status) {
            ClientStatus::Active, ClientStatus::Suspended => $client->logActivity('approved', ['plan' => $terms['plan'], 'cycle' => $terms['cycle']->value]),
            ClientStatus::Rejected => $client->logActivity('rejected', ['reason' => $terms['reason']]),
            ClientStatus::Pending => null,
        };

        if ($status === ClientStatus::Suspended) {
            $client->logActivity('suspended', ['reason' => $terms['reason']]);
        }

        return $client;
    }

    /**
     * The Jeddah branch with its manager and user (docs/sprints/00_README.md demo accounts).
     */
    private function addHospitalBranchTeam(Client $client): void
    {
        $jeddah = ClientBranch::create([
            'client_id' => $client->id,
            'code' => 'BR-02',
            'name' => 'فرع جدة',
            'city' => 'جدة',
            'manager' => 'م. ليان الغامدي',
            'phone' => '+966 50 765 4321',
            'active' => true,
        ]);

        $roles = $client->roles()->pluck('id', 'key');

        foreach ([
            ['م. ليان الغامدي', 'jeddah@alreaya-hospital.example.com', 'branch_manager'],
            ['أحمد الزهراني', 'qa.jeddah@alreaya-hospital.example.com', 'branch_user'],
        ] as [$name, $email, $roleKey]) {
            $client->users()->create([
                'branch_id' => $jeddah->id,
                'name' => $name,
                'email' => $email,
                'role' => ClientUserRole::Member,
                'client_role_id' => $roles[$roleKey],
                'password' => self::PASSWORD,
                'locale' => 'ar',
                'active' => true,
            ]);
        }
    }

    private function grantSector(string $clientCode, string $sectorKey, SectorSource $source): void
    {
        $client = Client::where('code', $clientCode)->first();
        $sector = Sector::where('key', $sectorKey)->first();

        if ($client && $sector) {
            $client->extraSectors()->syncWithoutDetaching([$sector->id => ['source' => $source]]);
        }
    }
}
