<?php

namespace Tests;


use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Traits\InteractWithDomain;

abstract class RouteTestCase extends TestCase
{
    use InteractWithDomain, RefreshDatabase, DatabaseMigrations;

    function assertPagination(TestResponse $response, array $query, int $total, array $data): TestResponse
    {
        $lastPage = intdiv((100 - 1), $query['limit']) + 1;
        return $response
            ->assertJsonStructure(['data', 'current_page', 'per_page', 'total', 'last_page'])
            ->assertJsonPath('current_page', $query['page'])
            ->assertJsonPath('per_page', $query['limit'])
            ->assertJsonPath('total', $total)
            ->assertJsonPath('last_page', $lastPage)
            ->assertJsonPath('has_previous_page', $query['page'] > 1)
            ->assertJsonPath('has_next_page', $query['page'] < $lastPage)
            ->assertJsonCount($query['limit'], 'data')
            ->assertJsonPath('data', array_slice($data, ($query['page'] - 1) * $query['limit'], $query['limit']));
    }
}
