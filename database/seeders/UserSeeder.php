<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->userRepository->create([
            'name' => env('USER_NAME_ROOT','Joscrino Testador'),
            'email' => env('USER_EMAIL_ROOT','teste@testcompany.com.br'),
            'password' => Hash::make(env('USER_PASSWORD_ROOT','password')),
            'email_verified_at' => now()
        ]);
    }
}
