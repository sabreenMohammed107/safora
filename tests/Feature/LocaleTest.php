<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_default_to_arabic_rtl(): void
    {
        $this->get(route('portal.login'))
            ->assertOk()
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee(__('portal.auth.heading', locale: 'ar'));
    }

    public function test_switching_to_english_flips_direction(): void
    {
        $this->from(route('portal.login'))
            ->post(route('locale.update', 'en'))
            ->assertRedirect(route('portal.login'));

        $this->get(route('portal.login'))
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('Sign in');
    }

    public function test_switch_is_saved_on_the_signed_in_admin(): void
    {
        $admin = Admin::factory()->create(['locale' => 'ar']);

        $this->actingAs($admin, 'admin')->post(route('locale.update', 'en'));

        $this->assertSame('en', $admin->fresh()->locale);
    }

    public function test_language_picked_before_sign_in_wins_and_is_saved(): void
    {
        $admin = Admin::factory()->create(['locale' => 'ar']);

        $this->post(route('locale.update', 'en'));
        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password']);

        $this->assertSame('en', session('locale'));
        $this->assertSame('en', $admin->fresh()->locale);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->post(route('locale.update', 'fr'))->assertNotFound();
    }
}
