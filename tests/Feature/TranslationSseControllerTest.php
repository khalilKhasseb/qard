<?php

namespace Tests\Feature;

use App\Models\BusinessCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TranslationSseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_returns_sse_headers(): void
    {
        $user = User::factory()->create();
        $card = BusinessCard::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->get("/api/ai-translate/events/{$card->id}");

        $response->assertOk();
        $this->assertInstanceOf(StreamedResponse::class, $response->baseResponse);
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $response->assertHeader('Connection', 'keep-alive');
        $response->assertHeader('X-Accel-Buffering', 'no');
    }
}
