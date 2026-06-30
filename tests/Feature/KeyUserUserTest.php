<?php

namespace Tests\Feature;

use App\Models\KeyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeyUserUserTest extends TestCase
{
    use RefreshDatabase;

    protected $keyUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keyUser = KeyUser::factory()->create();
    }

    public function test_key_user_can_view_users_list()
    {
        $response = $this->actingAs($this->keyUser, 'key_user')
            ->get(route('key-user.user.index'));

        $response->assertStatus(200);
    }

    public function test_key_user_can_view_create_user_form()
    {
        $response = $this->actingAs($this->keyUser, 'key_user')
            ->get(route('key-user.user.create'));

        $response->assertStatus(200);
    }

    public function test_key_user_can_create_user()
    {
        $userData = [
            'bp_code' => 'BA001',
            'name' => 'Test User',
            'full_name' => 'Test User Full Name',
            'email' => 'test@example.com',
            'email_id' => 'test@example.com',
            'mobile_no' => '1234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'status' => 1,
        ];

        $response = $this->actingAs($this->keyUser, 'key_user')
            ->post(route('key-user.user.store'), $userData);

        $response->assertRedirect(route('key-user.user.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }
}