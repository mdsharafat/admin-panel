<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Role;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = ['admin','author','editor','hrm','sales'];
        foreach ($roles as $key => $value) {
            $role = new Role();
            $role->name = $value;
            $role->created_at =Carbon::now()->setTime(23,59,59)->format('Y-m-d H:i:s');
            $role->updated_at =Carbon::now()->setTime(23,59,59)->format('Y-m-d H:i:s');
            $role->save();
        }
    }
}

?>