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
            'name' => 'John Testador',
            'email' => 'testapi@tray.com.br',
            'password' => Hash::make('password'),
            'email_verified_at' => now()
        ]);
    }
}
