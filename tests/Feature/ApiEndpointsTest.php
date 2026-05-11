<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login(): void
    {
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $registerResponse
            ->assertStatus(201)
            ->assertJsonStructure(['message', 'token', 'user']);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonStructure(['message', 'token', 'user']);
    }

    public function test_authenticated_user_can_manage_tickets(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($owner);

        $createResponse = $this->postJson('/api/tickets', [
            'title' => 'Internet issue',
            'description' => 'No internet connection in office',
            'status' => 'open',
            'priority' => 'high',
            'user_id' => $owner->id,
            'assigned_to' => $assignee->id,
            'device_id' => $device->id,
        ]);

        $createResponse->assertStatus(201);
        $ticketId = $createResponse->json('ticket.id');

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(1);

        $this->getJson("/api/tickets/{$ticketId}")
            ->assertOk()
            ->assertJsonPath('id', $ticketId);

        $this->putJson("/api/tickets/{$ticketId}", [
            'title' => 'Internet issue updated',
            'description' => 'Intermittent internet connection',
            'status' => 'in_progress',
            'priority' => 'medium',
            'user_id' => $owner->id,
            'assigned_to' => $assignee->id,
            'device_id' => $device->id,
        ])->assertOk();

        $this->deleteJson("/api/tickets/{$ticketId}")
            ->assertOk();
    }

    public function test_authenticated_user_can_assign_and_list_devices(): void
    {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create();
        $device = Device::factory()->create([
            'status' => 'available',
            'assigned_user_id' => null,
        ]);
        Sanctum::actingAs($admin);

        $this->postJson('/api/devices/assign', [
            'device_id' => $device->id,
            'user_id' => $targetUser->id,
        ])
            ->assertOk()
            ->assertJsonPath('device.assigned_user_id', $targetUser->id);

        $this->getJson('/api/devices')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_api_returns_429_when_rate_limit_is_exceeded(): void
    {
        User::factory()->create([
            'email' => 'rate-limit@example.com',
            'password' => 'password123',
        ]);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'rate-limit@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => 'rate-limit@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
