<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Provider;
use App\Models\TeamMember;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SetupAdminAndFixRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-admin {--email=admin@isaiahnailbar.com} {--password=password123} {--name=Administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure Spatie roles exist, configure an admin user, and verify active providers/team';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info("   Isaiah Nail Bar — Admin & Role Setup Helper   ");
        $this->info("=================================================");

        // 1. Ensure Spatie roles exist
        $this->line("\n[1/4] Ensuring Spatie roles exist...");
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $providerRole = Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $this->info("  ✓ Roles verified: admin, provider, customer");

        // 2. Setup or update Admin User
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        $this->line("\n[2/4] Setting up Admin Account ($email)...");
        $adminUser = User::where('email', $email)->first();

        if ($adminUser) {
            $adminUser->name = $name ?: $adminUser->name;
            $adminUser->password = Hash::make($password);
            $adminUser->save();
            $this->info("  ✓ Existing user found. Password updated to: $password");
        } else {
            $adminUser = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            $this->info("  ✓ Created new Admin user: $email (Password: $password)");
        }

        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
            $this->info("  ✓ Assigned 'admin' role to $email");
        } else {
            $this->info("  ✓ User already has 'admin' role");
        }

        // 3. Fix and verify Providers & Team Members
        $this->line("\n[3/4] Checking Providers & Team Members...");
        
        $totalProviders = Provider::count();
        $inactiveProviders = Provider::where('active', false)->orWhereNull('active')->count();
        if ($inactiveProviders > 0) {
            Provider::where('active', false)->orWhereNull('active')->update(['active' => true]);
            $this->warn("  ! Activated $inactiveProviders provider(s) that were marked inactive or null.");
        }
        $this->info("  ✓ Active Providers: $totalProviders");

        $totalTeam = TeamMember::count();
        $inactiveTeam = TeamMember::where('active', false)->orWhereNull('active')->count();
        if ($inactiveTeam > 0) {
            TeamMember::where('active', false)->orWhereNull('active')->update(['active' => true]);
            $this->warn("  ! Activated $inactiveTeam team member(s) that were marked inactive or null.");
        }
        $this->info("  ✓ Active Team Members: $totalTeam");

        $totalServices = Service::count();
        $unapprovedServices = Service::where('approved', false)->orWhereNull('approved')->count();
        if ($unapprovedServices > 0) {
            Service::where('approved', false)->orWhereNull('approved')->update(['approved' => true]);
            $this->warn("  ! Approved $unapprovedServices service(s) that were unapproved or null.");
        }
        $this->info("  ✓ Total Services: $totalServices (all marked approved)");

        // 4. Summary Table of Users
        $this->line("\n[4/4] Current Users & Roles in Database:");
        $users = User::with('roles')->get()->map(function ($u) {
            return [
                'ID' => $u->id,
                'Name' => $u->name,
                'Email' => $u->email,
                'Roles' => $u->roles->pluck('name')->implode(', ') ?: 'NO ROLE ASSIGNED',
            ];
        });
        $this->table(['ID', 'Name', 'Email', 'Roles'], $users);

        $this->info("\n=================================================");
        $this->info("Setup complete! You can now log into the admin portal:");
        $this->info("  URL:      https://isaiahnailbar.com/login");
        $this->info("  Email:    $email");
        $this->info("  Password: $password");
        $this->info("=================================================");

        return 0;
    }
}
