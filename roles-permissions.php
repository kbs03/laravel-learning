## 1. Setup the Project

```bash
composer create-project laravel/laravel role-permission-system
cd role-permission-system
```

## 2. Install Required Packages

```bash
composer require spatie/laravel-permission
```

## 3. Publish Configuration and Migrations

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## 4. Run Migrations

```bash
php artisan migrate
```

## 5. Update User Model

```php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
use HasRoles;

// ... rest of your model code
}
```

## 6. Create Seeders

```bash
php artisan make:seeder PermissionSeeder
php artisan make:seeder RoleSeeder
php artisan make:seeder UserSeeder
```

```php
// database/seeders/PermissionSeeder.php
use Spatie\Permission\Models\Permission;

public function run()
{
$permissions = [
'view_dashboard',
'manage_users',
'manage_roles',
'manage_posts',
'edit_posts',
'delete_posts',
// Add more permissions as needed
];

foreach ($permissions as $permission) {
Permission::create(['name' => $permission]);
}
}
```

```php
// database/seeders/RoleSeeder.php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

public function run()
{
$adminRole = Role::create(['name' => 'admin']);
$adminRole->givePermissionTo(Permission::all());

$editorRole = Role::create(['name' => 'editor']);
$editorRole->givePermissionTo(['manage_posts', 'edit_posts']);

$userRole = Role::create(['name' => 'user']);
$userRole->givePermissionTo(['view_dashboard']);
}
```

```php
// database/seeders/DatabaseSeeder.php
public function run()
{
$this->call([
PermissionSeeder::class,
RoleSeeder::class,
UserSeeder::class,
]);
}
```

## 7. Create Controllers

```bash
php artisan make:controller RoleController --resource
php artisan make:controller PermissionController --resource
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return view('roles.index', compact('roles', 'permissions'));
      
    }
 
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
    
        $role = Role::create(['name' => $request->name]);
    
        // Get permission names instead of IDs
        $permissions = Permission::whereIn('id', $request->permissions)
                        ->pluck('name')
                        ->toArray();
    
        $role->givePermissionTo($permissions);
    
        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
    }
    
   
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }
    

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
    
        $role->update(['name' => $request->name]);
    
        // Get permission names instead of IDs
        $permissions = Permission::whereIn('id', $request->permissions)
                        ->pluck('name')
                        ->toArray();
    
        $role->syncPermissions($permissions);
    
          return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }
    

    public function destroy(Role $role)
{
    // Prevent deletion of admin role
    if ($role->name === 'admin') {
        return redirect()->route('roles.index')
            ->with('error', 'Cannot delete admin role');
    }

    $role->delete();
    return redirect()->route('roles.index')
        ->with('success', 'Role deleted successfully');
}

}
```

## 8. Create Views

```bash
mkdir -p resources/views/roles
touch resources/views/roles/{index,create,edit}.blade.php
```

```php
<!-- resources/views/roles/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Roles</h2>
    <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">Create New Role</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Permissions</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td>
                    @foreach($role->permissions as $permission)
                    <span class="badge bg-primary">{{ $permission->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
```

```php
<!-- resources/views/roles/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Role</h2>
    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div class="row">
                @foreach($permissions as $permission)
                <div class="col-md-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]"
                            value="{{ $permission->id }}" id="permission_{{ $permission->id }}">
                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Role</button>
    </form>
</div>
@endsection
```


```php
<!-- resources/views/roles/edit.blade.php -->


@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Role</h2>
    <form method="POST" action="{{ route('roles.update', $role->id) }}">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ old('name', $role->name) }}" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ old('description', $role->description) }}</textarea>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div class="row">
                @foreach($permissions as $permission)
                <div class="col-md-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="permissions[]" 
                               value="{{ $permission->id }}" 
                               id="permission_{{ $permission->id }}"
                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <div class="d-flex">
            <button type="submit" class="btn btn-primary me-2">Update Role</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection


## 9. Set Up Routes

```php
Route::middleware(['auth'])->group(function () {

    // Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Roles and Permissions  
    Route::resource('roles', RoleController::class)->middleware('can:manage_roles');   // can:manage_roles [ name of your defiened permission ] 
    
    // Users
    Route::resource('users', UserController::class)->middleware('can:manage_users');
    
    // Posts
    Route::resource('posts', PostController::class)->middleware('can:manage_posts');
    
    // Dashboard (example of protected route)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('can:view_dashboard');
});
```
[
    
***************************************Note:: [ not included in steps]

Blade directives:
@can('edit_posts')
<!-- Edit button -->
@endcan

Controller authorization:

public function edit(Post $post)
{
$this->authorize('edit_posts');

}

// Assign role to user
$user->assignRole('admin');

// Assign multiple roles
$user->assignRole(['writer', 'editor']);

// Assign direct permissions
$user->givePermissionTo('edit_posts');


2. Create a test user and assign roles: // for defining first user role 
```bash
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->assignRole('admin');

    *******************************************************************Note Over
]


```php
protected $routeMiddleware = [
    // ...
    'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
];



# Complete Laravel Roles & Permissions Implementation

I'll provide the remaining components for a full CRUD system with user management, role assignment, and post management with permission checks.

## 1. Complete Role CRUD (Add Delete Functionality)

### Update RoleController
```php
// app/Http/Controllers/RoleController.php

// Add these methods to the existing controller

public function destroy(Role $role)
{
    // Prevent deletion of admin role
    if ($role->name === 'admin') {
        return redirect()->route('roles.index')
            ->with('error', 'Cannot delete admin role');
    }

    $role->delete();
    return redirect()->route('roles.index')
        ->with('success', 'Role deleted successfully');
}

public function show(Role $role)
{
    return view('roles.show', compact('role'));
}
```

### Add Role Views

```php
<!-- resources/views/roles/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Role Details</h2>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $role->name }}</h5>
            <h6 class="card-subtitle mb-2 text-muted">Permissions:</h6>
            <div class="mb-3">
                @foreach($role->permissions as $permission)
                    <span class="badge bg-primary">{{ $permission->name }}</span>
                @endforeach
            </div>
            
            <div class="d-flex">
                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-warning me-2">Edit</a>
                <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to delete this role?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <a href="{{ route('roles.index') }}" class="btn btn-secondary mt-3">Back to Roles</a>
</div>
@endsection
```

## 2. User CRUD with Role Assignment

### Create UserController
```bash
php artisan make:controller UserController --resource --model=User
```

```php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage_users');
    }

    public function index()
    {
        $users = User::with('roles')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'array',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete yourself');
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
```

### User Views

```bash
mkdir -p resources/views/users
touch resources/views/users/{index,create,show,edit}.blade.php
```

```php
<!-- resources/views/users/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Users</h2>
    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Create New User</a>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
```

```php
<!-- resources/views/users/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New User</h2>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <div class="mb-3">
            <label for="password-confirm" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password-confirm" name="password_confirmation" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Roles</label>
            <div class="row">
                @foreach($roles as $role)
                <div class="col-md-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="roles[]" 
                               value="{{ $role->id }}" 
                               id="role_{{ $role->id }}">
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Create User</button>
    </form>
</div>
@endsection
```

```php
<!-- resources/views/users/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>User Details</h2>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $user->name }}</h5>
            <h6 class="card-subtitle mb-2 text-muted">{{ $user->email }}</h6>
            
            <h6 class="mt-3">Roles:</h6>
            <div class="mb-3">
                @foreach($user->roles as $role)
                    <span class="badge bg-primary">{{ $role->name }}</span>
                @endforeach
            </div>
            
            <div class="d-flex">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning me-2">Edit</a>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to delete this user?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <a href="{{ route('users.index') }}" class="btn btn-secondary mt-3">Back to Users</a>
</div>
@endsection
```

```php
<!-- resources/views/users/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit User</h2>
    <form method="POST" action="{{ route('users.update', $user->id) }}">
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
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        
        <div class="mb-3">
            <label for="password-confirm" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="password-confirm" name="password_confirmation">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Roles</label>
            <div class="row">
                @foreach($roles as $role)
                <div class="col-md-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="roles[]" 
                               value="{{ $role->id }}" 
                               id="role_{{ $role->id }}"
                               {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                        <label class="form-check-label" for="role_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Update User</button>
    </form>
</div>
@endsection
```

## 3. Post CRUD with Permission Checks

### Create Post Model and Migration
```bash
php artisan make:model Post -m
```

```php
// database/migrations/xxxx_create_posts_table.php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}
```

```bash
php artisan migrate
```

### Create PostController
```bash
php artisan make:controller PostController --resource --model=Post
```

```php
// app/Http/Controllers/PostController.php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage_posts')->except(['index', 'show']);
    }

    public function index()
    {
        $posts = Post::with('user')->latest()->get();
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = auth()->user()->posts()->create($request->all());

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully');
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($request->all());

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated successfully');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully');
    }
}
```

### Create Post Policy
```bash
php artisan make:policy PostPolicy --model=Post
```

```php
// app/Policies/PostPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Post;

class PostPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Post $post)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->can('manage_posts');
    }

    public function update(User $user, Post $post)
    {
        return $user->can('edit_posts') && ($user->id === $post->user_id || $user->hasRole('admin'));
    }

    public function delete(User $user, Post $post)
    {
        return $user->can('delete_posts') && ($user->id === $post->user_id || $user->hasRole('admin'));
    }
}
```

### Register Policy
```php
// app/Providers/AuthServiceProvider.php
protected $policies = [
    Post::class => PostPolicy::class,
];
```

### Post Views
```bash
mkdir -p resources/views/posts
touch resources/views/posts/{index,create,show,edit}.blade.php
```

```php
<!-- resources/views/posts/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Posts</h2>
    
    @can('manage_posts')
    <a href="{{ route('posts.create') }}" class="btn btn-primary mb-3">Create New Post</a>
    @endcan
    
    <div class="row">
        @foreach($posts as $post)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $post->title }}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        By {{ $post->user->name }} on {{ $post->created_at->format('M d, Y') }}
                    </h6>
                    <p class="card-text">{{ Str::limit($post->content, 100) }}</p>
                    <a href="{{ route('posts.show', $post->id) }}" class="btn btn-sm btn-info">View</a>
                    
                    @can('update', $post)
                    <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    @endcan
                    
                    @can('delete', $post)
                    <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this post?')">
                            Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
```

```php
<!-- resources/views/posts/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create New Post</h2>
    <form method="POST" action="{{ route('posts.store') }}">
        @csrf
        
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Create Post</button>
    </form>
</div>
@endsection
```

```php
<!-- resources/views/posts/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">{{ $post->title }}</h2>
            <h6 class="card-subtitle mb-3 text-muted">
                By {{ $post->user->name }} on {{ $post->created_at->format('M d, Y') }}
            </h6>
            <div class="card-text mb-4">{!! nl2br(e($post->content)) !!}</div>
            
            <div class="d-flex">
                <a href="{{ route('posts.index') }}" class="btn btn-secondary me-2">Back to Posts</a>
                
                @can('update', $post)
                <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-warning me-2">Edit</a>
                @endcan
                
                @can('delete', $post)
                <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('Are you sure you want to delete this post?')">
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
```

```php
<!-- resources/views/posts/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Post</h2>
    <form method="POST" action="{{ route('posts.update', $post->id) }}">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $post->title }}" required>
        </div>
        
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" id="content" name="content" rows="5" required>{{ $post->content }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Post</button>
    </form>
</div>
@endsection
```

## 4. Update Routes

```php
// routes/web.php
use App\Http\Controllers\{
    RoleController,
    UserController,
    PostController
};

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Roles and Permissions
    Route::resource('roles', RoleController::class)->middleware('can:manage_roles');
    
    // Users
    Route::resource('users', UserController::class)->middleware('can:manage_users');
    
    // Posts
    Route::resource('posts', PostController::class);
    
    // Dashboard (example of protected route)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('can:view_dashboard');
});
```

## 5. Navigation Menu with Permission Checks

```php
<!-- resources/views/layouts/app.blade.php -->
<!-- Add this to your navigation -->
@auth
<ul class="navbar-nav me-auto">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('home') }}">Home</a>
    </li>
    
    @can('view_dashboard')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
    </li>
    @endcan
    
    <li class="nav-item">
        <a class="nav-link" href="{{ route('posts.index') }}">Posts</a>
    </li>
    
    @can('manage_users')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('users.index') }}">Users</a>
    </li>
    @endcan
    
    @can('manage_roles')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('roles.index') }}">Roles</a>
    </li>
    @endcan
</ul>
@endauth
```