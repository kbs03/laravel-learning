steps to import and export csv file in your project




## 1. Set Up Laravel Project

```bash
composer create-project laravel/laravel csv-project
cd csv-project
```

## 2. Configure Database

Edit `.env` file with your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

## 3. Create Model and Migration

```bash
php artisan make:model CsvData -m

## create migratation or add table into database

## 6. Create Views

Create `resources/views/csv/index.blade.php`:
```html
<!DOCTYPE html>
<html>

<head>
    <title>CSV Import/Export</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>CSV Import/Export</h2>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <div class="card mt-4">
            <div class="card-header">
                Upload CSV
            </div>
            <div class="card-body">
                <form action="{{ route('csv.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <input type="file" class="form-control" name="csv_file" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload CSV</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                Export Data
            </div>
            <div class="card-body">
                <a href="{{ route('csv.download') }}" class="btn btn-success">Download CSV</a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                Data Preview
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Column 1</th>
                            <th>Column 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                        <tr>
                            <td>{{ $row->column1 }}</td>
                            <td>{{ $row->column2 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>


## 5. Create Routes

Edit `routes/web.php`:
```php
use App\Http\Controllers\CsvController;

Route::get('/csv', [CsvController::class, 'index']);
Route::post('/csv/upload', [CsvController::class, 'upload'])->name('csv.upload');
Route::get('/csv/download', [CsvController::class, 'download'])->name('csv.download');
```




Edit `app/Http/Controllers/CsvController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\CsvData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CsvController extends Controller
{
    
    public function index()
    {
        return view('index');
    }

    public function upload(Request $request)
    {
        // validate with mimes - csv 
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);
    
         
        $file = $request->file('csv_file'); // request file 
        $csvData = file_get_contents($file);  // get file copntent 
        $rows = array_map('str_getcsv', explode("\n", $csvData)); // str_getcsv => converts string to array and then explod 
        $header = array_shift($rows);  // extract first row
    
        foreach ($rows as $row) {
            if (empty($row) || count($row) != count($header)) { // row count and header count should not same 
                continue;
            }
    
            $row = array_combine($header, $row);  // combine header and rww
     
            $data = [];
            foreach ($row as $key => $value) {   // take row as key and value 
                $value = trim($value);  // tram the output 
 
                if (in_array($key, ['init_amt', 'refund_amt_', 'transfer_amt', 'return_amt'])) { 
                    $data[$key] = ($value === '') ? null : (is_numeric($value) ? (float)$value : null);
                }
  
                elseif (in_array($key, ['check_in_date', 'check_out_date', 'booked_on', 'last_updated'])) {
                    try {
                        $data[$key] = ($value === '') ? null : Carbon::createFromFormat('d-m-Y H:i', $value);
                    } catch (\Exception $e) {
                        $data[$key] = null;
                    }
                }
           
                else {
                    $data[$key] = ($value === '') ? null : $value;
                }
            }
            
    
            CsvData::create($data);
        }
    
        return redirect()->back()->with('success', 'CSV file imported successfully');
    }

    
    
    public function download()
{
        // Get all data from the CsvData model
   $data = CsvData::all()->toArray();
    
   // If no data, return with message
   if (empty($data)) {
       return back()->with('error', 'No data available to export');
   }
   
       // Get headers from first row
   $headers = array_keys($data[0]);
   
     // Create output buffer
   $output = fopen('php://output', 'w');
   
     // Set headers for download
   header('Content-Type: text/csv');
   header('Content-Disposition: attachment; filename="csv_data_export_' . date('Y-m-d') . '.csv"');
   
     // Write headers
   fputcsv($output, $headers);
   
   // Write data rows
   foreach ($data as $row) {
       fputcsv($output, $row);
   }
   
   fclose($output);
   exit;

}
    

  
}
namespace App\Http\Controllers;

use App\Models\CsvData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CsvController extends Controller
{
    public function index()
    {
        $data = CsvData::all();
        return view('csv.index', compact('data'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $csvData = file_get_contents($file);
        $rows = array_map('str_getcsv', explode("\n", $csvData));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (count($row) != count($header)) continue;
            
            $row = array_combine($header, $row);
            CsvData::create($row);
        }

        return redirect()->back()->with('success', 'CSV file imported successfully');
    }

    public function download()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="csv_data.csv"',
        ];

        $data = CsvData::all();
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write headers
            fputcsv($file, ['column1', 'column2']); // adjust based on your columns
            
            // Write data
            foreach ($data as $row) {
                fputcsv($file, [$row->column1, $row->column2]); // adjust based on your columns
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}