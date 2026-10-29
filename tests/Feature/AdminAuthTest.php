<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ClientUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('ADMIN CONSOLE');
    }

    public function test_admin_signs_in_and_locale_is_restored(): void
    {
        $admin = Admin::factory()->create(['locale' => 'en']);

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertSame('en', session('locale'));
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = Admin::factory()->create();

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'wrong'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_sign_in_is_throttled_after_five_failures(): void
    {
        $this->freezeTime();
        $admin = Admin::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'wrong']);
        }

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('auth.throttle', ['seconds' => 60])]);

        $this->assertGuest('admin');
    }

    public function test_guests_are_sent_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.clients.index'))->assertRedirect(route('admin.login'));
    }

    public function test_client_users_cannot_reach_the_admin_area(): void
    {
        $this->actingAs(ClientUser::factory()->create(), 'client')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_signed_in_admin_skips_login_screen(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }
}
