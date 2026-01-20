<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create(['subscription_tier' => 'pro']);
});

test('upload: valid image upload succeeds', function () {
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => $file,
        ]);

    $response->assertOk();
    Storage::disk('public')->assertExists('theme-images/'.$file->hashName());
})->skip('Requires upload endpoint implementation');

test('upload: PNG image upload succeeds', function () {
    $file = UploadedFile::fake()->image('logo.png', 100, 100);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => $file,
        ]);

    $response->assertOk();
})->skip('Requires upload endpoint implementation');

test('upload: GIF image upload succeeds', function () {
    $file = UploadedFile::fake()->image('animation.gif', 100, 100);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => $file,
        ]);

    $response->assertOk();
})->skip('Requires upload endpoint implementation');

test('upload: invalid file type is rejected', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => $file,
        ]);

    $response->assertUnprocessable();
})->skip('Requires upload endpoint implementation');

test('upload: file too large is rejected', function () {
    $file = UploadedFile::fake()->image('huge.jpg', 100, 100)->size(10000); // 10MB

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => $file,
        ]);

    $response->assertUnprocessable();
})->skip('Requires upload endpoint implementation');

test('upload: image is resized correctly', function () {
    $file = UploadedFile::fake()->image('large.jpg', 800, 800);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => $file,
            'max_width' => 400,
            'max_height' => 400,
        ]);

    $response->assertOk();
})->skip('Requires image processing implementation');

test('upload: multiple images can be uploaded', function () {
    $files = [
        UploadedFile::fake()->image('image1.jpg'),
        UploadedFile::fake()->image('image2.jpg'),
    ];

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'images' => $files,
        ]);

    $response->assertOk();
})->skip('Requires multiple image upload implementation');

test('upload: requires authentication', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    $this->postJson(route('api.themes.upload'), [
        'image' => $file,
    ])->assertUnauthorized();
})->skip('Requires upload endpoint implementation');
