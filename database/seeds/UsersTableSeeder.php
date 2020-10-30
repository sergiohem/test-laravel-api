<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() > 0) {
            echo "Seeder de usuários já fpi executado!";
        } else {
            $user = new User;
            $user->email = 'user@teste.com';
            $user->name = 'Usuario Teste';
            $user->password = bcrypt("12345678");
            $user->save();
        }
    }
}
