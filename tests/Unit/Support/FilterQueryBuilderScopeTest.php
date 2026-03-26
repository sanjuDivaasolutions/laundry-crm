<?php

use App\Support\FilterQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function () {
    $this->builder = new FilterQueryBuilder;
});

it('rejects dangerous scope names', function (string $scopeName) {
    $query = Mockery::mock(Builder::class);

    $filter = [
        'operator' => 'scope',
        'query_1' => [$scopeName],
    ];

    $this->builder->scope($filter, $query);
})->with([
    'delete',
    'forceDelete',
    'withoutGlobalScopes',
    'truncate',
    'restore',
])->throws(\InvalidArgumentException::class);

it('allows whitelisted scope names', function (string $scopeName) {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive($scopeName)->once()->andReturnSelf();

    $filter = [
        'operator' => 'scope',
        'query_1' => [$scopeName],
    ];

    $this->builder->scope($filter, $query);

    // If we reach here without exception, it passed
    expect(true)->toBeTrue();
})->with([
    'active',
    'ordered',
    'pending',
    'today',
    'urgent',
]);
