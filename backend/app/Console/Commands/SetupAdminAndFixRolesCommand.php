<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Provider;
use App\Models\TeamMember;
use App\Models\Service;
use App\Models\Category;
use App\Models\HeroSlide;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
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

        // 4. Normalize and verify image paths
        $this->line("\n[4/5] Normalizing & Verifying Image Paths in Database...");
        $normalizePath = function (?string $path): ?string {
            if (!$path) return null;
            $path = trim(str_replace('\\', '/', $path));
            if (preg_match('#^https?://[^/]+/storage/(.+)#i', $path, $m)) {
                return $m[1];
            }
            if (preg_match('#^/?storage/(.+)#i', $path, $m)) {
                return $m[1];
            }
            return ltrim($path, '/');
        };

        $fixedServices = 0;
        foreach (Service::whereNotNull('image')->get() as $s) {
            $cleaned = $normalizePath($s->image);
            if ($cleaned !== $s->image) {
                $s->image = $cleaned;
                $s->save();
                $fixedServices++;
            }
        }
        if ($fixedServices > 0) {
            $this->info("  ✓ Normalized $fixedServices service image path(s)");
        } else {
            $this->info("  ✓ Service image paths are clean");
        }

        $fixedProviders = 0;
        foreach (Provider::whereNotNull('photo')->get() as $p) {
            $cleaned = $normalizePath($p->photo);
            if ($cleaned !== $p->photo) {
                $p->photo = $cleaned;
                $p->save();
                $fixedProviders++;
            }
        }
        if ($fixedProviders > 0) {
            $this->info("  ✓ Normalized $fixedProviders provider photo path(s)");
        } else {
            $this->info("  ✓ Provider photo paths are clean");
        }

        $fixedTeam = 0;
        foreach (TeamMember::whereNotNull('photo')->get() as $t) {
            $cleaned = $normalizePath($t->photo);
            if ($cleaned !== $t->photo) {
                $t->photo = $cleaned;
                $t->save();
                $fixedTeam++;
            }
        }
        if ($fixedTeam > 0) {
            $this->info("  ✓ Normalized $fixedTeam team member photo path(s)");
        } else {
            $this->info("  ✓ Team member photo paths are clean");
        }

        // Check storage symlink
        $storageLink = public_path('storage');
        if (is_link($storageLink) || is_dir($storageLink)) {
            $this->info("  ✓ public/storage link exists");
        } else {
            $this->warn("  ! public/storage is missing. Run: php artisan storage:link");
        }

        // Check storage file count
        $diskPath = storage_path('app/public');
        if (is_dir($diskPath)) {
            $serviceFiles = is_dir("$diskPath/services") ? count(File::files("$diskPath/services")) : 0;
            $providerFiles = is_dir("$diskPath/providers") ? count(File::files("$diskPath/providers")) : 0;
            $teamFiles = is_dir("$diskPath/team_members") ? count(File::files("$diskPath/team_members")) : 0;
            $galleryFiles = is_dir("$diskPath/gallery") ? count(File::files("$diskPath/gallery")) : 0;
            $this->info("  ✓ Found files in storage: services ($serviceFiles), providers ($providerFiles), team ($teamFiles), gallery ($galleryFiles)");
        }

        // 5. Summary Table of Users
        $this->line("\n[5/5] Current Users & Roles in Database:");
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
