<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Sms\OtpManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated_without_phone_change(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1234567890', // Same phone
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_phone_change_redirects_to_phone_verification(): void
    {
        // Mock the OtpManager to avoid actual SMS sending
        $this->mock(OtpManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')
                ->once()
                ->andReturn(new \App\Contracts\Sms\OtpResult(
                    success: true,
                    expiresInSeconds: 300,
                    cooldownSeconds: 60,
                    attemptsRemaining: 5
                ));
        });

        $user = User::factory()->create([
            'phone' => '+1234567890',
            'phone_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+0987654321',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('phone.verification.notice'));

        $user->refresh();

        $this->assertSame('+0987654321', $user->phone);
        $this->assertNull($user->phone_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'phone' => $user->phone,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_phone_verification_status_is_unchanged_when_phone_is_unchanged(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
            'phone_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => $user->phone,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->phone_verified_at);
    }

    public function test_phone_is_normalized_to_e164_format(): void
    {
        // Mock the OtpManager since phone is changing
        $this->mock(OtpManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')
                ->once()
                ->andReturn(new \App\Contracts\Sms\OtpResult(
                    success: true,
                    expiresInSeconds: 300,
                    cooldownSeconds: 60,
                    attemptsRemaining: 5
                ));
        });

        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '1 (555) 123-4567',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('phone.verification.notice'));

        $user->refresh();

        $this->assertSame('+15551234567', $user->phone);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $response = $this
            ->actingAs($user, 'web')
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest('web');
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_phone_must_be_unique(): void
    {
        $existingUser = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $user = User::factory()->create([
            'phone' => '+0987654321',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+1234567890',
            ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_otp_is_sent_when_phone_changes(): void
    {
        // Assert OtpManager->send is called with correct parameters
        $this->mock(OtpManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')
                ->once()
                ->with('+0987654321', 'registration')
                ->andReturn(new \App\Contracts\Sms\OtpResult(
                    success: true,
                    expiresInSeconds: 300,
                    cooldownSeconds: 60,
                    attemptsRemaining: 5
                ));
        });

        $user = User::factory()->create([
            'phone' => '+1234567890',
        ]);

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '+0987654321',
            ]);
    }
}
