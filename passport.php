# Laravel Passport Authentication: Step-by-Step Guide

I'll walk you through creating a complete authentication system with login and registration using Laravel Passport.
Here's the step-by-step process:

## Step 1: Set Up Laravel Project

First, create a new Laravel project if you don't have one already:

```bash
composer create-project laravel/laravel passport-auth
cd passport-auth
```

## Step 2: Install Laravel Passport

Install Passport via Composer:

```bash
composer require laravel/passport
```

## Step 3: Configure Database

Set up your database in `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=passport_auth
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and install Passport:

```bash
php artisan migrate
php artisan passport:install
```

## Step 4: Configure User Model and Auth Service Provider

Update `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
```

Update `app/Providers/AuthServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
```

## Step 5: Update Auth Config

In `config/auth.php`, set the API driver to passport:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

## Step 6: Create API Routes

Add these routes to `routes/api.php`:

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('logout', [AuthController::class, 'logout']);
});
```

## Step 7: Create Auth Controller

Create a new controller:

```bash
php artisan make:controller API/AuthController
```

Update `app/Http/Controllers/API/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}




## Web Frontend Project (New Setup)

### Step 1: Create Web Routes

In your web project's `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthWebController;

// Registration Routes
Route::get('/register', [AuthWebController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthWebController::class, 'register']);

// Login Routes
Route::get('/login', [AuthWebController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);

// Dashboard Route
Route::get('/dashboard', [AuthWebController::class, 'dashboard'])->middleware('auth:web');

// Logout Route
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
```

### Step 2: Create AuthWebController

```bash
php artisan make:controller AuthWebController
```

Update `app/Http/Controllers/AuthWebController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = env('API_BASE_URL', 'http://api.yourdomain.com');
    }

    // Show registration form
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $response = Http::post("{$this->apiBaseUrl}/api/register", [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Store token in session
            $request->session()->put('api_token', $data['access_token']);
            
            return redirect()->route('dashboard');
        }

        return back()->withErrors($response->json()['errors'] ?? ['email' => 'Registration failed']);
    }

    // Show login form
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $response = Http::post("{$this->apiBaseUrl}/api/login", [
            'email' => $request->email,
            'password' => $request->password
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Store token in session
            $request->session()->put('api_token', $data['access_token']);
            
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // Show dashboard
    public function dashboard(Request $request)
    {
        $token = $request->session()->get('api_token');
        
        if (!$token) {
            return redirect()->route('login');
        }

        $response = Http::withToken($token)->get("{$this->apiBaseUrl}/api/user");

        if ($response->successful()) {
            $user = $response->json();
            return view('dashboard', ['user' => $user]);
        }

        // If token is invalid, clear session and redirect to login
        $request->session()->forget('api_token');
        return redirect()->route('login');
    }

    // Handle logout
    public function logout(Request $request)
    {
        $token = $request->session()->get('api_token');
        
        if ($token) {
            Http::withToken($token)->post("{$this->apiBaseUrl}/api/logout");
        }

        $request->session()->forget('api_token');
        return redirect()->route('login');
    }
}
```

### Step 3: Create Blade Views

1. Create `resources/views/auth/register.blade.php`:

```php
@extends('layouts.app')

@section('content')
<div class="form-container">
    <h1>Student Registration</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
        @endif
        
        <div class="form-group">
            <label for="name">Username</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter username" required>
            @error('name')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email" required>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password (min 6 chars)" required>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
        </div>
        
        <button type="submit">Register</button>
        
        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Login</a>
        </div>
    </form>
</div>
@endsection
```

2. Create `resources/views/auth/login.blade.php`:

```php
@extends('layouts.app')

@section('content')
<div class="form-container">
    <h1>Student Login</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        @if(session('status'))
        <div class="success-message">
            {{ session('status') }}
        </div>
        @endif
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email" required>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <button type="submit">Login</button>
        
        <div class="register-link">
            Don't have an account? <a href="{{ route('register') }}">Register</a>
        </div>
    </form>
</div>
@endsection
```

3. Create `resources/views/dashboard.blade.php`:

```php
@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <h1>Welcome to Your Dashboard</h1>
    <div class="user-info">
        <p><strong>Name:</strong> {{ $user['name'] }}</p>
        <p><strong>Email:</strong> {{ $user['email'] }}</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" id="logoutBtn">Logout</button>
    </form>
</div>
@endsection
```

### Step 4: Create Layout File

Create `resources/views/layouts/app.blade.php`:

```php
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
        }
        .dashboard-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
            text-align: center;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        input[type=text], 
        input[type=email], 
        input[type=password] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border 0.3s;
        }
        input[type=text]:focus, 
        input[type=email]:focus, 
        input[type=password]:focus {
            border-color: #3498db;
            outline: none;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        #logoutBtn {
            background-color: #e74c3c;
        }
        #logoutBtn:hover {
            background-color: #c0392b;
        }
        .login-link, .register-link {
            text-align: center;
            margin-top: 16px;
        }
        .login-link a, .register-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover, .register-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 4px;
        }
        .success-message {
            color: #2ecc71;
            font-size: 14px;
            margin-top: 4px;
            text-align: center;
            margin-bottom: 16px;
        }
        .user-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            text-align: left;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
```

### Step 5: Configure Environment Variables

In your web project's `.env` file:

```env
API_BASE_URL=http://api.yourdomain.com
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Step 6: Add Session Middleware

Make sure your web routes have the `web` middleware group in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

### Step 7: Create Authentication Middleware (Optional)

If you want to protect routes with middleware:

```bash
php artisan make:middleware AuthenticateAPI
```

Update `app/Http/Middleware/AuthenticateAPI.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthenticateAPI
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('api_token')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
```

Register the middleware in `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    'auth.api' => \App\Http\Middleware\AuthenticateAPI::class,
    // other middleware...
];
```

Now you can protect routes like this:

```php
Route::get('/dashboard', [AuthWebController::class, 'dashboard'])->middleware('auth.api');
```

## How This Works

1. **Frontend (Web Project)**:
   - Handles views and forms
   - Makes HTTP requests to the API backend
   - Manages user session with the API token

2. **Backend (API Project)**:
   - Handles all authentication logic
   - Manages user data
   - Returns JSON responses

3. **Flow**:
   - User submits form in web project
   - Web controller makes API call to backend
   - Backend processes request and returns response
   - Web controller handles response and redirects accordingly

This separation keeps your API clean and allows you to reuse it with multiple frontends (web, mobile, etc.).