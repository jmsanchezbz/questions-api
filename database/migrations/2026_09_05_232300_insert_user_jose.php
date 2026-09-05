<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insert([
            'id'                => 1,
            'name'              => 'jose',
            'username'          => 'jose',
            'email'             => 'jose@mail.com',
            'email_verified_at' => null,
            'password'          => '$2y$12$3MVjAZSLT1x4Zh6S/TtNj.dp7wbzayzjUdDRaeN4wMxjRQ0NUkg7i',
            'role'              => 'admin',
            'remember_token'    => null,
            'created_at'        => '2024-06-02 17:50:49',
            'updated_at'        => '2026-08-19 08:56:26',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('id', 1)->delete();
    }
};
