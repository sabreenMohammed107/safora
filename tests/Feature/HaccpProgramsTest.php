<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AdminPermission;
use App\Enums\ProgramStatus;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Models\HaccpProgram;
use App\Models\HaccpProgramGroup;
use Database\Seeders\HaccpProgramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HaccpProgramsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HaccpProgramSeeder::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(HaccpProgram $program, array $overrides = []): array
    {
        $data = [
            'group_id' => $program->group_id,
            'icon' => $program->icon,
            'status' => $program->status->value,
        ];

        foreach (HaccpProgram::CONTENT_FIELDS as $field) {
            $data[$field] = ['ar' => $program->getTranslation($field, 'ar', false), 'en' => $program->getTranslation($field, 'en', false)];
        }

        return array_replace_recursive($data, $overrides);
    }

    public function test_seeder_loads_v17_library_idempotently(): void
    {
        $this->seed(HaccpProgramSeeder::class);

        $this->assertSame(7, HaccpProgramGroup::count());
        $this->assertSame(22, HaccpProgram::count());

        $pest = HaccpProgram::where('code', 'PRP-01')->sole();
        $this->assertSame('مكافحة الآفات', $pest->getTranslation('name', 'ar'));
        $this->assertSame('Pest control', $pest->getTranslation('name', 'en'));
        $this->assertSame('hyg', $pest->group->key);
        $this->assertCount(3, $pest->checkList('ar'));
        $this->assertFalse($pest->is_custom);
        $this->assertContains('steps', $pest->missingTranslations());
        $this->assertNotContains('name', $pest->missingTranslations());
    }

    public function test_untranslated_content_falls_back_to_arabic(): void
    {
        $pest = HaccpProgram::where('code', 'PRP-01')->sole();

        app()->setLocale('en');

        $this->assertSame($pest->getTranslation('steps', 'ar'), $pest->steps);
    }

    public function test_command_center_renders_and_selects_program(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.haccp.programs.index', ['program' => 'PRP-13']))
            ->assertOk()
            ->assertSee('PRP-13')
            ->assertSee('التخزين وسلسلة التبريد');
    }

    public function test_admin_edits_content_in_both_languages(): void
    {
        $program = HaccpProgram::where('code', 'PRP-01')->sole();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.haccp.programs.update', $program), $this->payload($program, [
                'steps' => ['en' => '1. Survey the site boundaries.'],
                'status' => 'draft',
            ]))
            ->assertRedirect(route('admin.haccp.programs.index', ['program' => 'PRP-01']));

        $program->refresh();
        $this->assertSame('1. Survey the site boundaries.', $program->getTranslation('steps', 'en'));
        $this->assertSame(ProgramStatus::Draft, $program->status);
        $this->assertNotContains('steps', $program->missingTranslations());
    }

    public function test_arabic_name_is_required(): void
    {
        $program = HaccpProgram::where('code', 'PRP-01')->sole();

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->put(route('admin.haccp.programs.update', $program), $this->payload($program, ['name' => ['ar' => '']]))
            ->assertSessionHasErrors('name.ar');
    }

    public function test_new_program_gets_next_code_and_only_custom_programs_can_be_deleted(): void
    {
        $library = HaccpProgram::where('code', 'PRP-01')->sole();
        $admin = $this->actingAs(Admin::factory()->create(), 'admin');

        $admin->post(route('admin.haccp.programs.store'), $this->payload($library, ['name' => ['ar' => 'برنامج الحلال', 'en' => 'Halal assurance']]))
            ->assertRedirect(route('admin.haccp.programs.index', ['program' => 'PRP-23']));

        $custom = HaccpProgram::where('code', 'PRP-23')->sole();
        $this->assertTrue($custom->is_custom);

        $admin->delete(route('admin.haccp.programs.destroy', $library))->assertSessionHas('error');
        $this->assertModelExists($library);

        $admin->delete(route('admin.haccp.programs.destroy', $custom))->assertRedirect(route('admin.haccp.programs.index'));
        $this->assertModelMissing($custom);
    }

    public function test_requires_haccp_permission(): void
    {
        $staff = Admin::factory()->withRole(AdminRole::factory()->granting([AdminPermission::ClientsView])->create())->create();

        $this->actingAs($staff, 'admin')->get(route('admin.haccp.programs.index'))->assertForbidden();
    }
}
