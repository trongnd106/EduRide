<?php

namespace Tests\Unit\Services;

use App\Constants\Role as RoleConstant;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends BaseService {
    public function model(): string
    {
        return User::class;
    }
}

class BaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserServiceTest();
    }

    public function test_model_method()
    {
        $this->assertEquals(User::class, $this->userService->model(), 'Base Service model() does not return correct Model Instance');
    }

    public function test_store_method()
    {
        $user = User::factory()->make();

        $userCreated = $this->userService->store($user->getAttributes());

        $this->assertInstanceOf(User::class, $userCreated);
        $this->assertArraySubset(Arr::only($user->getAttributes(), $user->getFillable()), $userCreated->getAttributes());
        $this->assertDatabaseHas('users', Arr::only($userCreated->getAttributes(), $userCreated->getFillable()));
    }

    public function test_store_method_with_relations_already_exist()
    {
        $user = User::factory()->make();
        $attributes = $user->getAttributes();

        $role = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_ADMIN, 'guard_name' => Guard::getDefaultName(User::class)]);
        $attributes['roles'][] = $role;

        $userCreated = $this->userService->store($attributes);

        $this->assertDatabaseHas('users', Arr::only($user->getAttributes(), $user->getFillable()));
        $this->assertInstanceOf(Role::class, $userCreated->roles[0]);
        $this->assertEquals($role->getAttributes(), $userCreated->roles[0]->getAttributes());
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $userCreated->id
        ]);
    }

    public function test_store_method_with_relations_cascade()
    {
        $user = User::factory()->make();
        $role = ['name' => RoleConstant::ROLE_ADMIN, 'guard_name' => Guard::getDefaultName(User::class)];

        $attributes = $user->getAttributes();
        $attributes['roles'][] = $role;

        $userCreated = $this->userService->store($attributes);
        $roleCreated = Role::query()->where($role)->first();

        $this->assertDatabaseHas('users', Arr::only($user->getAttributes(), $user->getFillable()));
        $this->assertNotNull($roleCreated);
        $this->assertInstanceOf(Role::class, $roleCreated);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $roleCreated->id,
            'model_type' => User::class,
            'model_id' => $userCreated->id
        ]);
    }

    public function test_find_all_method_with_all_columns()
    {
        $users = User::factory()->withDeleted()->count(10)->create();

        $usersFound = $this->userService->findAll();

        $this->assertCount(10, $usersFound);
        foreach ($usersFound as $key => $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertTrue($user->is($users[$key]));
            $this->assertEquals($users[$key]->getAttributes(), $user->getAttributes());
        }
    }

    public function test_find_all_method_with_a_few_columns()
    {
        $users = User::factory()->count(10)->create();

        $usersFound = $this->userService->findAll(['username', 'email']);

        $this->assertCount(10, $usersFound);
        foreach ($usersFound as $key => $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertFalse($user->is($users[$key]));
            $this->assertNotEquals($users[$key]->getAttributes(), $user->getAttributes());
            $this->assertEquals(Arr::only($users[$key]->getAttributes(), ['username', 'email']), $user->getAttributes());
        }
    }

    public function test_show_method()
    {
        $user = User::factory()->withDeleted()->create();
        $userId = $user->id;

        $userFound = $this->userService->show($userId);

        $this->assertNotNull($userFound);
        $this->assertInstanceOf(User::class, $userFound);
        $this->assertTrue($user->is($userFound));
        $this->assertEquals($user->getAttributes(), $userFound->getAttributes());
    }

    public function test_show_with_trashes_method()
    {
        $user = User::factory()->create();
        $userId = $user->id;
        $user->delete();

        $userFoundWithTrash = $this->userService->show($userId, [], [], [], true);
        $this->assertNotNull($userFoundWithTrash);
        $this->assertInstanceOf(User::class, $userFoundWithTrash);
        $this->assertTrue($user->is($userFoundWithTrash));
        $this->assertEquals($user->getAttributes(), $userFoundWithTrash->getAttributes());

        $this->expectException(ModelNotFoundException::class);
        $this->userService->show($userId);
    }

    public function test_update_method()
    {
        $userOrigin = User::factory()->create();
        $userNew = User::factory()->make();

        $user = $this->userService->update($userOrigin, $userNew->getAttributes());

        $this->assertInstanceOf(User::class, $user);
        $this->assertArraySubset(Arr::only($userNew->getAttributes(), $userNew->getFillable()), $user->getAttributes());
        $this->assertDatabaseHas('users', Arr::only($userNew->getAttributes(), $userNew->getFillable()));
    }

    public function test_update_method_with_relations_already_exist()
    {
        $userOrigin = User::factory()->create();
        $userNew = User::factory()->make();
        $attributes = $userNew->getAttributes();

        $guard = Guard::getDefaultName(User::class);
        $roleAdmin = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_ADMIN, 'guard_name' => $guard]);
        $roleStaff = Role::query()->firstOrCreate(['name' => RoleConstant::ROLE_CONCIERGE, 'guard_name' => $guard]);
        $userOrigin->assignRole($roleAdmin);
        $attributes['roles'] = [$roleStaff];

        $user = $this->userService->update($userOrigin, $attributes);

        $this->assertInstanceOf(User::class, $user);
        $this->assertArraySubset(Arr::only($userNew->getAttributes(), $userNew->getFillable()), $user->getAttributes());
        $this->assertDatabaseHas('users', Arr::only($userNew->getAttributes(), $userNew->getFillable()));
        $this->assertCount(1, $user->roles);
        $this->assertTrue($roleStaff->is($user->roles[0]));
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $roleStaff->id,
            'model_type' => User::class,
            'model_id' => $user->id
        ]);
        $this->assertDatabaseMissing('model_has_roles', [
            'role_id' => $roleAdmin->id,
            'model_type' => User::class,
            'model_id' => $user->id
        ]);
    }

    public function test_update_method_with_relations_create_cascade()
    {
        $userOrigin = User::factory()->create();
        $userNew = User::factory()->make();
        $role = ['name' => RoleConstant::ROLE_ADMIN, 'guard_name' => Guard::getDefaultName(User::class)];

        $attributes = $userNew->getAttributes();
        $attributes['roles'][] = $role;

        $user = $this->userService->update($userOrigin, $attributes);
        $roleCreated = Role::query()->where($role)->first();

        $this->assertInstanceOf(User::class, $user);
        $this->assertArraySubset(Arr::only($userNew->getAttributes(), $userNew->getFillable()), $user->getAttributes());
        $this->assertDatabaseHas('users', Arr::only($userNew->getAttributes(), $userNew->getFillable()));
        $this->assertNotNull($roleCreated);
        $this->assertInstanceOf(Role::class, $roleCreated);
        $this->assertCount(1, $user->roles);
        $this->assertTrue($roleCreated->is($user->roles[0]));
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $roleCreated->id,
            'model_type' => User::class,
            'model_id' => $user->id
        ]);
    }

    public function test_destroy_method_hard()
    {
        $user = User::factory()->create();

        $result = $this->userService->destroy($user, true);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', $user->getAttributes());
    }

    public function test_destroy_method_soft()
    {
        $user = User::factory()->create();
        $attributes = $user->getAttributes();

        $result = $this->userService->destroy($user);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', $attributes);
        $this->assertDatabaseMissing('users', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_restore_method()
    {
        $user = User::factory()->create();
        $attributes = $user->getAttributes();
        $user->delete();

        $result = $this->userService->restore($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', Arr::set($attributes, 'deleted_at', null));
    }

    public function test_paginate_method()
    {
        $total = 20;
        $limit = 10;
        $paginatedUsers = array_chunk(User::factory()->withDeleted()->count($total)->create()->toArray(), $limit);

        foreach ($paginatedUsers as $index => $users) {
            $page = $index + 1;
            $params = compact('page', 'limit');
            $result = $this->userService->paginate($params);

            $lastPage = ceil($total / $limit);
            $this->assertArraySubset([
                'per_page' => $limit,
                'total' => $total,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $index * $limit + 1,
                'to' => $index * $limit + count($users),
                'data' => $users
            ], $result->toArray());
        }
    }
}
