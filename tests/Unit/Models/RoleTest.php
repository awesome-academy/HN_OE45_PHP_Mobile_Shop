<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Tests\ModelTestCase;

class RoleTest extends ModelTestCase
{
    public function testModelConfiguration()
    {
        $this->runConfigurationAssertions(
            new Role(),
            [
                'fillable' => [
                    'name',
                ],
            ]
        );
    }

    public function testUsersRelation()
    {
        $role = new Role();

        $this->assertHasManyRelation($role->users(), $role, new User(), 'role_id');
    }
}
