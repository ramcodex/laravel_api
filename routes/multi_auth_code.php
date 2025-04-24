<?php 
/**
 * Laravel Multi-Auth System with Role Management
 * 
 * This code demonstrates how to set up a multi-auth system in Laravel
 * where a super admin can change user roles. It includes:
 * 
 * 1. Database migrations for tables
 * 2. Models with relationships
 * 3. Authentication configuration
 * 4. Controllers for auth logic
 * 5. Role management functionality
 * 6. Middleware for route protection
 * 7. Route definitions
 * 8. Basic view examples
 */

// =============================================
// DATABASE MIGRATIONS
// =============================================

// Create users table - database/migrations/xxxx_create_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type')->default('user'); // 'user', 'admin', 'super_admin', etc.
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}

// Create roles table - database/migrations/xxxx_create_roles_table.php
class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}

// Create role_user pivot table - database/migrations/xxxx_create_role_user_table.php
class CreateRoleUserTable extends Migration
{
    public function up()
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}

// Create permissions table - database/migrations/xxxx_create_permissions_table.php
class CreatePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}

// Create permission_role pivot table - database/migrations/xxxx_create_permission_role_table.php
class CreatePermissionRoleTable extends Migration
{
    public function up()
    {
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permission_role');
    }
}

// =============================================
// MODELS
// =============================================

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($roleSlug)
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasPermission($permissionSlug)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function isSuperAdmin()
    {
        return $this->user_type === 'super_admin' || $this->hasRole('super-admin');
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin' || $this->hasRole('admin');
    }
    
    public function isUser()
    {
        return $this->user_type === 'user';
    }
}

// app/Models/Role.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}

// app/Models/Permission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

// =============================================
// AUTH CONFIGURATION
// =============================================

// config/auth.php
return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'super_admin' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];

// =============================================
// MIDDLEWARE
// =============================================

// app/Http/Middleware/CheckUserType.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    public function handle(Request $request, Closure $next, $userType)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        if ($userType === 'super_admin' && $user->isSuperAdmin()) {
            return $next($request);
        }
        
        if ($userType === 'admin' && ($user->isAdmin() || $user->isSuperAdmin())) {
            return $next($request);
        }
        
        if ($userType === 'user' && ($user->isUser() || $user->isAdmin() || $user->isSuperAdmin())) {
            return $next($request);
        }

        return redirect('/')->with('error', 'You do not have permission to access this resource.');
    }
}

// app/Http/Middleware/CheckRole.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        $rolesList = explode('|', $roles);

        foreach ($rolesList as $roleSlug) {
            if ($user->hasRole($roleSlug)) {
                return $next($request);
            }
        }

        return redirect('/')->with('error', 'You do not have permission to access this resource.');
    }
}

// app/Http/Middleware/CheckPermission.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permissions)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        $permissionsList = explode('|', $permissions);

        foreach ($permissionsList as $permissionSlug) {
            if ($user->hasPermission($permissionSlug)) {
                return $next($request);
            }
        }

        return redirect('/')->with('error', 'You do not have permission to access this resource.');
    }
}

// Register middleware in app/Http/Kernel.php
protected $routeMiddleware = [
    // ... existing middleware
    'user.type' => \App\Http\Middleware\CheckUserType::class,
    'role' => \App\Http\Middleware\CheckRole::class,
    'permission' => \App\Http\Middleware\CheckPermission::class,
];

// =============================================
// AUTHENTICATION CONTROLLERS
// =============================================

// app/Http/Controllers/Auth/LoginController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }
}

// app/Http/Controllers/Auth/RegisterController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'user_type' => 'user', // Default type
        ]);

        // Assign default user role
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        }

        return $user;
    }
}

// =============================================
// ROLE MANAGEMENT CONTROLLERS
// =============================================

// app/Http/Controllers/Admin/RoleController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user.type:super_admin');
    }

    public function index()
    {
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}

// app/Http/Controllers/Admin/UserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user.type:super_admin');
    }

    public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        $userTypes = ['user', 'admin', 'super_admin'];
        return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'userTypes'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'user_type' => 'required|string|in:user,admin,super_admin',
            'roles' => 'nullable|array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
        ]);

        // Sync roles
        $user->roles()->sync($request->roles ?? []);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }
}

// =============================================
// ROUTES
// =============================================

// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;

// Authentication Routes
Auth::routes();

// Home Route
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    // User Routes
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

    // Admin Routes
    Route::middleware(['user.type:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });

    // Super Admin Routes
    Route::middleware(['user.type:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.super-dashboard');
        })->name('dashboard');

        // Role Management
        Route::resource('roles', RoleController::class);
        
        // User Management
        Route::resource('users', UserController::class)->except(['show', 'create', 'store']);
    });
});

// Alternatively, you can use role middleware
Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function () {
    // Routes accessible to both admin and super-admin
});

// =============================================
// VIEWS (BASIC EXAMPLES)
// =============================================

// resources/views/admin/users/index.blade.php
@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>User Management</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Roles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->user_type) }}</td>
                <td>
                    @foreach ($user->roles as $role)
                        <span class="badge bg-info">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('super-admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

// resources/views/admin/users/edit.blade.php
@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Edit User</h1>
    <form action="{{ route('super-admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
        </div>
        
        <div class="mb-3">
            <label for="user_type" class="form-label">User Type</label>
            <select class="form-control" id="user_type" name="user_type" required>
                @foreach ($userTypes as $type)
                    <option value="{{ $type }}" {{ $user->user_type == $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Roles</label>
            @foreach ($roles as $role)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                    {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                <label class="form-check-label" for="role_{{ $role->id }}">
                    {{ $role->name }}
                </label>
            </div>
            @endforeach
        </div>
        
        <button type="submit" class="btn btn-primary">Update User</button>
    </form>
</div>
@endsection

// =============================================
// DATABASE SEEDERS
// =============================================

// database/seeders/RoleSeeder.php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create basic roles
        Role::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Super Administrator with all privileges']);
        Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator with most privileges']);
        Role::create(['name' => 'Editor', 'slug' => 'editor', 'description' => 'Can edit content']);
        Role::create(['name' => 'User', 'slug' => 'user', 'description' => 'Regular user']);
    }
}

// database/seeders/PermissionSeeder.php
namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $manageUsers = Permission::create(['name' => 'Manage Users', 'slug' => 'manage-users']);
        $manageRoles = Permission::create(['name' => 'Manage Roles', 'slug' => 'manage-roles']);
        $createContent = Permission::create(['name' => 'Create Content', 'slug' => 'create-content']);
        $editContent = Permission::create(['name' => 'Edit Content', 'slug' => 'edit-content']);
        $deleteContent = Permission::create(['name' => 'Delete Content', 'slug' => 'delete-content']);
        $viewAdminDashboard = Permission::create(['name' => 'View Admin Dashboard', 'slug' => 'view-admin-dashboard']);

        // Assign permissions to roles
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $superAdminRole->permissions()->attach([
            $manageUsers->id, $manageRoles->id, $createContent->id, 
            $editContent->id, $deleteContent->id, $viewAdminDashboard->id
        ]);

        $adminRole = Role::where('slug', 'admin')->first();
        $adminRole->permissions()->attach([
            $createContent->id, $editContent->id, $deleteContent->id, $viewAdminDashboard->id
        ]);

        $editorRole = Role::where('slug', 'editor')->first();
        $editorRole->permissions()->attach([
            $createContent->id, $editContent->id
        ]);
    }
}

// database/seeders/UserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create super admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'super_admin',
        ]);
        $superAdmin->roles()->attach(Role::where('slug', 'super-admin')->first()->id);

        // Create admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first()->id);

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'user',
        ]);
        $user->roles()->attach(Role::where('slug', 'user')->first()->id);
    }
}

// =============================================
// SERVICE PROVIDER REGISTRATION
// =============================================

// app/Providers/AuthServiceProvider.php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Define policies here
    ];

    public function boot()
    {
        $this->registerPolicies();

        // Define Gates for permissions
        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('manage-users');
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasPermission('manage-roles');
        });

        Gate::define('create-content', function (User $user) {
            return $user->hasPermission('create-content');
        });

        Gate::define('edit-content', function (User $user) {
            return $user->hasPermission('edit-content');
        });

        Gate::define('delete-content', function (User $user) {
            return $user->hasPermission('delete-content');
        });

        Gate::define('view-admin-dashboard', function (User $user) {
            return $user->hasPermission('view-admin-dashboard');
        });
    }
}