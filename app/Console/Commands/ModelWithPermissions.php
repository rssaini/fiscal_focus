<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModelWithPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:ModelwithPermissions {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model and generate default permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Generate the model
        Artisan::call('make:model', ['name' => $name]);

        // Define permissions to create
        $permissions = [
            'create-' . $name,
            'view-' . $name,
            'edit-' . $name,
            'delete-' . $name,
        ];

        $role = Role::where('name', 'Admin')->first();
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $permission->assignRole($role);
            $this->info("Permission '$permissionName' created or exists.");
        }

        $this->info('Model and permissions created successfully.');
    }
}
