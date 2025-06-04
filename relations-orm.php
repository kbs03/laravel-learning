# Setting Up Relationships in Laravel from Scratch

Here's a step-by-step guide to implementing relationships in Laravel:

## 1. Set Up a New Laravel Project
```bash
composer create-project laravel/laravel relationship-demo
cd relationship-demo
```

## 2. Configure Database
Edit `.env` file:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=relationship_demo
DB_USERNAME=root
DB_PASSWORD=
```

## 3. Create Models and Migrations

### One-to-One Relationship (User ↔ Phone)

1. Create User model (already exists by default)
2. Create Phone model:
```bash
php artisan make:model Phone -m
```

3. Edit the phone migration:
```php
// database/migrations/..._create_phones_table.php
public function up()
{
Schema::create('phones', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->string('number');
$table->timestamps();
});
}
```

4. Define the relationship in models:
```php
// app/Models/User.php
public function phone()
{
return $this->hasOne(Phone::class);
}

// app/Models/Phone.php
public function user()
{
return $this->belongsTo(User::class);
}
```

### One-to-Many Relationship (Post ↔ Comments)

1. Create Post and Comment models:
```bash
php artisan make:model Post -m
php artisan make:model Comment -m
```

2. Edit the migrations:
```php
// posts table migration
Schema::create('posts', function (Blueprint $table) {
$table->id();
$table->string('title');
$table->text('body');
$table->timestamps();
});

// comments table migration
Schema::create('comments', function (Blueprint $table) {
$table->id();
$table->foreignId('post_id')->constrained()->onDelete('cascade');
$table->text('body');
$table->timestamps();
});
```

3. Define relationships:
```php
// app/Models/Post.php
public function comments()
{
return $this->hasMany(Comment::class);
}

// app/Models/Comment.php
public function post()
{
return $this->belongsTo(Post::class);
}
```

### Many-to-Many Relationship (User ↔ Roles)

1. Create Role model and pivot table:
```bash
php artisan make:model Role -m
```

2. Edit the migrations:
```php
// roles table migration
Schema::create('roles', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->timestamps();
});

// role_user pivot table migration
Schema::create('role_user', function (Blueprint $table) {
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->foreignId('role_id')->constrained()->onDelete('cascade');
$table->primary(['user_id', 'role_id']);
});
```

3. Define relationships:
```php
// app/Models/User.php
public function roles()
{
return $this->belongsToMany(Role::class);
}

// app/Models/Role.php
public function users()
{
return $this->belongsToMany(User::class);
}
```

## 4. Run Migrations
```bash
php artisan migrate
```

## 5. Seed Some Data
Create a seeder:
```bash
php artisan make:seeder DatabaseSeeder
```

Edit the seeder:
```php
public function run()
{
// One-to-One
$user = User::factory()->create();
$user->phone()->create(['number' => '1234567890']);

// One-to-Many
$post = Post::factory()->create();
$post->comments()->createMany([
['body' => 'First comment'],
['body' => 'Second comment'],
]);

// Many-to-Many
$roles = Role::factory()->count(3)->create();
$user->roles()->attach($roles->pluck('id'));
}
```

Run the seeder:
```bash
php artisan db:seed
```

## 6. Test the Relationships

Create a route in `routes/web.php`:
```php
Route::get('/test-relationships', function() {
// One-to-One
$user = User::first();
echo "User's phone: " . $user->phone->number . "<br>";

// One-to-Many
$post = Post::first();
echo "Post comments:<br>";
foreach ($post->comments as $comment) {
echo "- " . $comment->body . "<br>";
}

// Many-to-Many
echo "User roles:<br>";
foreach ($user->roles as $role) {
echo "- " . $role->name . "<br>";
}

return '';
});
```

Now visit `/test-relationships` in your browser to see the relationships in action.

## Additional Relationship Types

### Has-Many-Through
```php
// Country has many Users, User has many Posts
// Want to get all Posts for a Country

// In Country model
public function posts()
{
return $this->hasManyThrough(Post::class, User::class);
}
```

### Polymorphic Relationships
```php
// Comments can belong to Posts or Videos

// In Comment model
public function commentable()
{
return $this->morphTo();
}

// In Post and Video models
public function comments()
{
return $this->morphMany(Comment::class, 'commentable');
}
```

This covers the basic relationship setup in Laravel. You can now expand on these examples for your specific application
needs.






*** tips ***

find($id) takes an id and returns a single model. If no matching model exist, it returns null.

findOrFail($id) takes an id and returns a single model. If no matching model exist, it throws an error1.

first() returns the first record found in the database. If no matching model exist, it returns null.

firstOrFail() returns the first record found in the database. If no matching model exist, it throws an error1.

get() returns a collection of models matching the query.

pluck($column) returns a collection of just the values in the given column. In previous versions of Laravel this method
was called lists.

toArray() converts the model/collection into a simple PHP array.