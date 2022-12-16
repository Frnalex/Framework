<?php

namespace App\Auth;

use Framework\Database\Table;

class UserTable extends Table
{
    protected string $table = 'users';
    protected string $entity = User::class;
}
