<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns healthy status with correct JSON structure', function () {
    $response = $this->getJson('/health');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'checks' => [
                'database',
                'cache',
            ],
            'timestamp',
        ])
        ->assertJson([
            'status' => 'healthy',
            'checks' => [
                'database' => 'ok',
                'cache' => 'ok',
            ],
        ]);
});

it('does not require authentication', function () {
    $response = $this->getJson('/health');

    $response->assertOk();
});
