# Using Scheduler in Laravel: A Complete Guide

The Laravel scheduler allows you to schedule periodic tasks (like cron jobs) directly within your Laravel application.
It provides a clean, expressive way to define your scheduled tasks in code rather than managing system cron entries.

## Why Use Laravel Scheduler?

1. **Code-based configuration** - Define schedules in PHP code rather than system cron files
2. **Clean syntax** - Fluent, expressive methods for defining schedules
3. **Centralized management** - All tasks are defined in one place
4. **Error handling** - Built-in mechanisms for handling task failures
5. **Testing** - Easier to test scheduled tasks
6. **Overlapping prevention** - Prevent multiple instances of long-running tasks

## Steps from Project Creation

### 1. Create a New Laravel Project
```bash
composer create-project laravel/laravel scheduler-demo
cd scheduler-demo
```

### 2. Set Up the Scheduler Cron Entry (One-Time Setup) [ if you are working on server otherwise skip step 2]

Add this to your server's crontab (run `crontab -e`):

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This cron entry will call Laravel's scheduler every minute, and Laravel will determine which tasks need to run based on
your schedule definitions.




step 3::generate scheduler

php artisan make:command TestCron --command=test:cron [ it will create in app/commands/




<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestCron extends Command
{
   /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
  
    /**
     * Execute the console command.
     */
    public function handle()
    {
        
  
        Log::info('Custom task executed!');

    }
}


### 4 register in  app/Console/Kernel.php


protected function schedule(Schedule $schedule): void
{
    $schedule->command('test:cron')->everyMinute();
    
}


[

    protected function schedule(Schedule $schedule)
{
// Run an artisan command daily at midnight
$schedule->command('inspire')->daily();

// Run a closure every hour
$schedule->call(function () {
\Log::info('Running hourly task...');
})->hourly();

// Run a custom command every 5 minutes
$schedule->command('custom:task')->everyFiveMinutes();

// Send emails every day at 8:00 AM
$schedule->job(new SendEmailsJob)->dailyAt('8:00');

// Run a shell command weekly on Monday at 1:00 AM
$schedule->exec('node /home/forge/script.js')->weeklyOn(1, '1:00');
}
]

### 5. Test Your Scheduled Tasks

Test your schedule without waiting:
```bash
php artisan schedule:work # Runs scheduler every minute (for local development)
php artisan schedule:run # Runs due tasks immediately (for testing)
```

### 6. Monitor Scheduled Tasks

View output from scheduled tasks in Laravel's log file:
```bash
tail -f storage/logs/laravel.log
```

## Common Schedule Frequencies

Here are some common schedule methods:

- `->everyMinute()`
- `->everyFiveMinutes()`
- `->hourly()`
- `->daily()`
- `->dailyAt('13:00')`
- `->twiceDaily(1, 13)`
- `->weekly()`
- `->monthly()`
- `->quarterly()`
- `->yearly()`
- `->weekdays()`
- `->weekends()`
- `->between('8:00', '17:00')`

## Advanced Features

1. **Task Hooks**:
```php
$schedule->command('emails:send')
->daily()
->before(function () {
// Task is about to start
})
->after(function () {
// Task is complete
});
```

2. **Prevent Overlapping**:
```php
$schedule->command('report:generate')->everyMinute()->withoutOverlapping();
```

3. **Run in Background**:
```php
$schedule->command('report:generate')->daily()->runInBackground();
```

4. **Maintenance Mode**:
```php
$schedule->command('report:generate')->evenInMaintenanceMode();
```

5. **Email Output**:
```php
$schedule->command('report:generate')
->daily()
->sendOutputTo($filePath)
->emailOutputTo('admin@example.com');
```

The Laravel scheduler provides a powerful yet simple way to manage periodic tasks in your application, keeping all your
task scheduling logic in version control and making it easy to maintain.