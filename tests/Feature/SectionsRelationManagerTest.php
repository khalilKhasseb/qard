<?php

use App\Filament\Resources\BusinessCardResource\RelationManagers\SectionsRelationManager;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\User;
use Livewire\Livewire;

it('can get record title as string even when title is an array', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);
    $section = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'title' => ['en' => 'English Title', 'ar' => 'Arabic Title'],
    ]);

    $livewire = Livewire::test(SectionsRelationManager::class, [
        'ownerRecord' => $card,
        'pageClass' => 'App\Filament\Resources\BusinessCardResource\Pages\EditBusinessCard',
    ]);

    $table = $livewire->instance()->getTable();
    $title = $table->getRecordTitle($section);

    expect($title)->toBeString();
});
