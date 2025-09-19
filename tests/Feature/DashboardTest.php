<?php

use App\Models\User;

test('guests are redirected to the login page when accessing filament dashboard', function () {
    $response = $this->get(route('filament.siimut.pages.dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the filament dashboard', function () {
    // Register gates from UserResource
    \App\Filament\Siimut\Resources\Users\UserResource::registerGates();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('filament.siimut.pages.dashboard'));
    $response->assertStatus(200);
});
