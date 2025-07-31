<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use App\Models\Ngo;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Tanzim',
            'email' => 'tanzim@example.com',
            'phone' => '017xxxxxxx',
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'User registered successfully',
                     'user' => ['email' => 'tanzim@example.com']
                 ]);
    }

    public function test_user_can_login_with_correct_credentials()
    {
        // Create a user manually
        $user = User::create([
            'name' => 'Tanzim',
            'email' => 'tanzim@example.com',
            'phone' => '017xxxxxxx',
            'password' => Hash::make('secret123'),
            'role' => 'general',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'tanzim@example.com',
            'password' => 'secret123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'token'
                ]);
    }

    public function test_authenticated_user_can_view_their_profile()
    {
        $user = User::create([
            'name' => 'Tanzim',
            'email' => 'tanzim@example.com',
            'phone' => '017xxxxxxx',
            'password' => Hash::make('secret123'),
            'role' => 'general',
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'email' => 'tanzim@example.com',
                    'name' => 'Tanzim'
                ]);
    }

    public function test_authenticated_user_can_logout_successfully()
    {
        $user = User::create([
            'name' => 'Tanzim',
            'email' => 'tanzim@example.com',
            'phone' => '017xxxxxxx',
            'password' => Hash::make('secret123'),
            'role' => 'general',
        ]);

        $token = $user->createToken('logout_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Logged out'
                ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    public function test_socialite_login_creates_general_user()
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn('test@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('General User');

        Socialite::shouldReceive('driver->stateless->user')->andReturn($socialiteUser);

        $response = $this->get('/auth/callback');

        $response->assertRedirectContains('token=');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'general',
        ]);
    }

    public function test_socialite_login_creates_ngo_user_when_domain_matches()
    {
        Ngo::create([
            'name' => 'BRAC',
            'rep_contact' => 'BRAC Rep',
            'rep_designation' => 'Director',
            'rep_email' => 'rep@brac.com',
            'rep_phone' => '017xxxxxxx',
            'description' => 'Test NGO',
            'website' => 'https://brac.com',
            'status' => 'approved',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn('newuser@brac.com');
        $socialiteUser->shouldReceive('getName')->andReturn('BRAC Staff');

        Socialite::shouldReceive('driver->stateless->user')->andReturn($socialiteUser);

        $response = $this->get('/auth/callback');

        $response->assertRedirectContains('token=');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@brac.com',
            'role' => 'ngo',
        ]);
    }
}

