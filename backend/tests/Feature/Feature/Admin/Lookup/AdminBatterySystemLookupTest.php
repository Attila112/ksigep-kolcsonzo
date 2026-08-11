<?php

namespace Tests\Feature\Admin\Lookup;

use App\Models\BatterySystem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminBatterySystemLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_battery_systems_for_lookup(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        BatterySystem::factory()
            ->makita()
            ->create();

        BatterySystem::factory()
            ->parkside()
            ->create();

        $response = $this->getJson(
            '/api/admin/battery-systems'
        );

        $response
            ->assertOk()
            ->assertJsonCount(2, 'battery_systems')
            ->assertJsonPath(
                'battery_systems.0.manufacturer',
                'Makita'
            );
    }

    public function test_admin_battery_system_lookup_includes_inactive_systems(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        BatterySystem::factory()
            ->inactive()
            ->create();

        $this->getJson(
            '/api/admin/battery-systems'
        )
            ->assertOk()
            ->assertJsonCount(
                1,
                'battery_systems'
            );
    }

    public function test_customer_cannot_access_battery_system_lookup(): void
    {
        $customer = User::factory()->customer()->create();

        Sanctum::actingAs($customer);

        $this->getJson(
            '/api/admin/battery-systems'
        )
            ->assertForbidden();
    }

    public function test_guest_cannot_access_battery_system_lookup(): void
    {
        $this->getJson(
            '/api/admin/battery-systems'
        )
            ->assertUnauthorized();
    }
}