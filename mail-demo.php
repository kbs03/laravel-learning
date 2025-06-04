step 1 :: composer create-project laravel/laravel mail-demo


# **Mailtrap Integration in Laravel (Frontend + Backend)**

This guide covers **how to integrate Mailtrap in Laravel**, including **frontend form setup**, backend email handling,
and testing.

---

## **Step 1: Set Up Mailtrap Account & Get SMTP Credentials**

1. **Sign Up** at [Mailtrap.io](https://mailtrap.io/)
2. Go to ** email tesing=>inboxes =>My Inbox => credential there
3. Under **"SMTP Settings"**, note:
- **Host**: `sandbox.smtp.mailtrap.io`
- **Port**: `2525` (or `587` for TLS)
- **Username**: Your Mailtrap username
- **Password**: Your Mailtrap password

---

## **Step 2: Configure Laravel for Mailtrap**
### **1. Update `.env` File**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="Your App Name"
```

### **2. Verify Mail Configuration**
Run:
```bash
php artisan config:clear
```

---

## **Step 3: Create a Contact Form (Frontend)**
### **1. Create a Blade View (`resources/views/contact.blade.php`)**
```html
<!DOCTYPE html>
<html>

<head>
    <title>Contact Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Contact Us</h1>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('contact.submit') }}">
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
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</body>

</html>
```


## **Step 4: Create Controller & Send Email**
### **1. Generate Controller**
```bash
php artisan make:controller ContactController
```


### **2. Add Routes (`routes/web.php`)**
```php
use App\Http\Controllers\ContactController;

Route::get('/contact', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contact', [ContactController::class, 'submitForm'])->name('contact.submit');
```


### **2. Update `ContactController.php`**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function showForm()
    {
        return view('contact');
    }

    public function submitForm(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message,
        ];

        // Send email
        Mail::to('test@example.com')->send(new ContactFormMail($data));

        return redirect()->back()->with('success', 'Your message has been sent!');
    }
}
```

### **3. Create a Mailable Class**
```bash
php artisan make:mail ContactFormMail
```

### **4. Update `ContactFormMail.php`**
```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('New Contact Form Submission')
                    ->view('emails.contact')
                    ->with(['data' => $this->data]);
    }
}
```

### **5. Create Email Template (`resources/views/emails/contact.blade.php`)**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submission</title>
</head>
<body>
    <h2>New Contact Form Submission</h2>
    <p><strong>Name:</strong> {{ $data['name'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>
    <p><strong>Message:</strong> {{ $data['message'] }}</p>
</body>
</html>
```

---

## **Step 5: Test the Integration**
1. Visit `/contact` in your browser  
2. Fill out the form and submit  
3. Check your **Mailtrap inbox** to see the captured email  

---

## **Step 6: Optional - Switch to Real SMTP in Production**
When deploying to production, update `.env` with a real SMTP service (e.g., **Mailgun, SendGrid, or AWS SES**).

```env
MAIL_MAILER=smtp
MAIL_HOST=real-smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_real_username
MAIL_PASSWORD=your_real_password
MAIL_ENCRYPTION=tls
```

---

## **Conclusion**
✅ **Frontend**: Created a contact form in Blade  
✅ **Backend**: Set up Laravel to send emails via Mailtrap  
✅ **Testing**: Verified emails appear in Mailtrap inbox  

Now you can safely test emails in development before switching to a real SMTP in production! 🚀