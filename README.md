# Water Management Backend (Laravel API)

A beginner-friendly Laravel REST API for **efficient water management in a ward**. It stores locations, plots, monthly meter readings (billing) and payments, and exposes them as JSON endpoints that you can test with Postman.

This README walks through everything from creating the project, to understanding MVC, to testing each endpoint.

---

## Table of Contents

1. [Tech Stack](#1-tech-stack)
2. [Requirements](#2-requirements)
3. [Understanding MVC (for total beginners)](#3-understanding-mvc-for-total-beginners)
4. [Create the Laravel Project](#4-create-the-laravel-project)
5. [Connect to MySQL](#5-connect-to-mysql)
6. [Enable API Support](#6-enable-api-support)
7. [Database Design](#7-database-design)
8. [Create Models, Migrations, Controllers and Seeders](#8-create-models-migrations-controllers-and-seeders)
9. [Write the Migrations](#9-write-the-migrations)
10. [Write the Models](#10-write-the-models)
11. [Write the Seeders](#11-write-the-seeders)
12. [Run Migrations and Seeders](#12-run-migrations-and-seeders)
13. [Write the Controllers](#13-write-the-controllers)
14. [Define the API Routes](#14-define-the-api-routes)
15. [Views in Laravel (Blade)](#15-views-in-laravel-blade)
16. [Start the Server](#16-start-the-server)
17. [Testing the APIs with Postman](#17-testing-the-apis-with-postman)
18. [Troubleshooting](#18-troubleshooting)
19. [Artisan Command Cheat Sheet](#19-artisan-command-cheat-sheet)
20. [Cloning and Running This Project](#20-cloning-and-running-this-project)

---

## 1. Tech Stack

- PHP 8.3+
- Laravel (latest)
- MySQL (via XAMPP / phpMyAdmin)
- Composer
- Postman (for testing the API)

## 2. Requirements

Install these first:

| Tool | Why you need it | Check it works |
|------|-----------------|----------------|
| PHP 8.3 or higher | Runs Laravel | `php -v` |
| Composer | Installs PHP packages | `composer -V` |
| XAMPP (MySQL) | Database server + phpMyAdmin | Start MySQL in the XAMPP Control Panel |
| Git | Version control | `git --version` |
| Postman | Test API requests | Open the app |

---

## 3. Understanding MVC (for total beginners)

**MVC** stands for **Model, View, Controller**. It is a way of organising code so that each part has one job.

### The restaurant analogy

| Restaurant | MVC | Job |
|------------|-----|-----|
| The menu and the waiter's order pad | **Route** | Decides which kitchen station gets the request |
| The waiter / head chef | **Controller** | Takes the request, decides what to do, and returns the result |
| The pantry / fridge | **Model** | Talks to the database. Reads, saves, updates and deletes data |
| The plate the food is served on | **View** | What the customer actually sees |

### What each part does in Laravel

**Model** (`app/Models`)
- One model = one database table.
- `Location` model → `locations` table.
- It also describes relationships, e.g. "a Location has many Plots".

**View** (`resources/views`)
- What the user sees.
- In a normal website, a view is an HTML page written with **Blade** (`.blade.php`).
- In an **API** like this project, the "view" is **JSON** (data, not a web page). The controller returns JSON directly.

**Controller** (`app/Http/Controllers`)
- Receives the request, validates the data, uses the model to talk to the database, and sends back a response.

**Route** (`routes/api.php`)
- A list that says: "when someone visits `/api/locations` with GET, run the `index` method of `LocationController`".

### What happens when you call an API

```
Postman  --->  Route  --->  Controller  --->  Model  --->  Database
(request)    routes/api.php               (asks for data)   (MySQL)
   ^                                                            |
   |                                                            |
   +------------------  JSON response  <-----------------------+
```

Example: `GET http://127.0.0.1:8000/api/locations`

1. Postman sends the request.
2. `routes/api.php` sees `/locations` and sends it to `LocationController@index`.
3. The controller asks the `Location` model for all locations.
4. The model queries the `locations` table in MySQL.
5. The controller returns the result as JSON, and Postman displays it.

### Other terms you will meet

| Term | Meaning |
|------|---------|
| **Migration** | A file that creates or changes a database table. Think of it as "version control for your database". |
| **Seeder** | A file that inserts sample data into your tables so you do not have to type it by hand. |
| **Factory** | A file that generates fake data (optional). |
| **Artisan** | Laravel's command line tool. Every `php artisan ...` command comes from it. |
| **Eloquent** | Laravel's way of working with the database through models, so you rarely write raw SQL. |
| **Fillable** | A list of columns that are allowed to be saved from a request. |
| **Validation** | Checking the incoming data is correct before saving it. |

---

## 4. Create the Laravel Project

```bash
composer create-project --prefer-dist laravel/laravel water-management-backend
cd water-management-backend
```

- `composer create-project` downloads a fresh Laravel app.
- `--prefer-dist` downloads packaged releases (faster and lighter than cloning from source).
- `water-management-backend` is the folder name.

Open the folder in VS Code:

```bash
code .
```

### The folders you will use most

```
water-management-backend/
├── app/
│   ├── Http/Controllers/     # Controllers (C)
│   └── Models/               # Models (M)
├── database/
│   ├── migrations/           # Table definitions
│   └── seeders/              # Sample data
├── resources/views/          # Blade views (V)
├── routes/
│   ├── api.php               # API routes
│   └── web.php               # Web page routes
└── .env                      # Your private settings (never commit this)
```

---

## 5. Connect to MySQL

1. Start **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`) and create an empty database called `water_management`.
3. Open the `.env` file in your project and update the database section:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=water_management
DB_USERNAME=root
DB_PASSWORD=
```

> `.env` holds secrets and local settings. It is already in `.gitignore`, so it will not be pushed to GitHub. Other people copy `.env.example` instead.

---

## 6. Enable API Support

Newer Laravel versions do not include API routes by default. Install them:

```bash
php artisan install:api
```

This creates `routes/api.php` and installs **Laravel Sanctum** (for API authentication later). If it asks to run migrations, answer **yes** (your database is already configured).

All routes in `routes/api.php` automatically get the `/api` prefix, so a route called `locations` is reached at `/api/locations`.

---

## 7. Database Design

| Table | Columns | Relationship |
|-------|---------|--------------|
| `locations` | id, name, description | One location has many plots |
| `plots` | id, location_id, plot_number, owner_name | Belongs to a location; has many billings |
| `billings` | id, plot_id, record_date, meter_reading | Belongs to a plot; has many payments |
| `payments` | id, billing_id, amount, payment_date, method | Belongs to a billing |

Every table also gets `created_at` and `updated_at` automatically from Laravel.

```
Location 1 ──── * Plot 1 ──── * Billing 1 ──── * Payment
```

> Laravel's naming convention: model names are **singular** (`Location`) and table names are **plural** (`locations`). That is why the model is `Billing` and the table is `billings`.

---

## 8. Create Models, Migrations, Controllers and Seeders

Create the models **in this order** (parents first). Migrations run in the order they were created, and a table with a foreign key must be created after the table it points to.

### One command, three files

```bash
php artisan make:model Location -mcs
```

The flags mean:

| Flag | Creates |
|------|---------|
| `-m` | a **m**igration (`database/migrations/..._create_locations_table.php`) |
| `-c` | a **c**ontroller (`app/Http/Controllers/LocationController.php`) |
| `-s` | a **s**eeder (`database/seeders/LocationSeeder.php`) |
| `-f` | a **f**actory (fake data generator) |
| `-a` | **a**ll of the above |
| `--api` | makes the controller an API resource controller (no create/edit form methods) |

### What we will run for this project

For an API we want controllers inside an `Api` folder with the standard CRUD methods. So create the model, migration and seeder with `-ms`, then create the controller separately:

```bash
# 1. Location
php artisan make:model Location -ms
php artisan make:controller Api/LocationController --api --model=Location

# 2. Plot
php artisan make:model Plot -ms
php artisan make:controller Api/PlotController --api --model=Plot

# 3. Billing
php artisan make:model Billing -ms
php artisan make:controller Api/BillingController --api --model=Billing

# 4. Payment
php artisan make:model Payment -ms
php artisan make:controller Api/PaymentController --api --model=Payment
```

(`make:model Location -mcs` also works. It just creates a plain controller instead of an API resource one.)

You can see all options for any command with:

```bash
php artisan make:model --help
```

---

## 9. Write the Migrations

Open each file in `database/migrations/` (the names start with a timestamp). Replace the whole file with the code below.

### `..._create_locations_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
```

### `..._create_plots_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('plot_number')->unique();
            $table->string('owner_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};
```

### `..._create_billings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->cascadeOnDelete();
            $table->date('record_date');
            $table->decimal('meter_reading', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
```

### `..._create_payments_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('method')->default('cash');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

**How to read this:**
- `up()` runs when you migrate (creates the table). `down()` runs when you roll back (drops it).
- `foreignId('location_id')->constrained()` creates a column linked to `locations.id`.
- `cascadeOnDelete()` means: if a location is deleted, its plots are deleted too.

---

## 10. Write the Models

Replace each file in `app/Models/`.

### `app/Models/Location.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'description'];

    public function plots(): HasMany
    {
        return $this->hasMany(Plot::class);
    }
}
```

### `app/Models/Plot.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plot extends Model
{
    protected $fillable = ['location_id', 'plot_number', 'owner_name'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }
}
```

### `app/Models/Billing.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    protected $fillable = ['plot_id', 'record_date', 'meter_reading'];

    protected function casts(): array
    {
        return [
            'record_date' => 'date:Y-m-d',
            'meter_reading' => 'decimal:2',
        ];
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
```

### `app/Models/Payment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['billing_id', 'amount', 'payment_date', 'method'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
```

**How to read this:**
- `$fillable` lists the columns that can be saved through `Model::create([...])`. Anything not listed is ignored (this protects you from users sending extra fields).
- `hasMany` = "this model owns many of those". `belongsTo` = "this model belongs to one of those".

---

## 11. Write the Seeders

Seeders fill your tables with sample data. This data matches the Billing List from the earlier PHP learning project: 6 plots, one reading per plot on the 1st of each month from July to October 2026.

### `database/seeders/LocationSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::create(['name' => 'Zone A', 'description' => 'Northern part of the ward']);
        Location::create(['name' => 'Zone B', 'description' => 'Southern part of the ward']);
    }
}
```

### `database/seeders/PlotSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Plot;
use Illuminate\Database\Seeder;

class PlotSeeder extends Seeder
{
    public function run(): void
    {
        $plots = [
            ['location_id' => 1, 'plot_number' => 'A-01', 'owner_name' => 'Owner One'],
            ['location_id' => 1, 'plot_number' => 'A-02', 'owner_name' => 'Owner Two'],
            ['location_id' => 1, 'plot_number' => 'A-03', 'owner_name' => 'Owner Three'],
            ['location_id' => 2, 'plot_number' => 'B-01', 'owner_name' => 'Owner Four'],
            ['location_id' => 2, 'plot_number' => 'B-02', 'owner_name' => 'Owner Five'],
            ['location_id' => 2, 'plot_number' => 'B-03', 'owner_name' => 'Owner Six'],
        ];

        foreach ($plots as $plot) {
            Plot::create($plot);
        }
    }
}
```

### `database/seeders/BillingSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Billing;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // date => meter readings for plots 1 to 6
        $readings = [
            '2026-07-01' => [0, 0, 0, 0, 0, 0],
            '2026-08-01' => [12, 8, 20, 25, 10, 0],
            '2026-09-01' => [27, 18, 34, 55, 16, 21],
            '2026-10-01' => [41, 27, 50, 80, 23, 32],
        ];

        foreach ($readings as $date => $values) {
            foreach ($values as $index => $reading) {
                Billing::create([
                    'plot_id' => $index + 1,
                    'record_date' => $date,
                    'meter_reading' => $reading,
                ]);
            }
        }
    }
}
```

### `database/seeders/PaymentSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        Payment::create([
            'billing_id' => 7,
            'amount' => 600.00,
            'payment_date' => '2026-08-05',
            'method' => 'mpesa',
        ]);

        Payment::create([
            'billing_id' => 8,
            'amount' => 400.00,
            'payment_date' => '2026-08-06',
            'method' => 'cash',
        ]);
    }
}
```

### `database/seeders/DatabaseSeeder.php`

This is the "main" seeder. It calls the others **in order** (parents before children, because of the foreign keys).

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            PlotSeeder::class,
            BillingSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
```

---

## 12. Run Migrations and Seeders

Create all the tables:

```bash
php artisan migrate
```

Fill them with sample data:

```bash
php artisan db:seed
```

Or do both in one go:

```bash
php artisan migrate --seed
```

Wipe everything and start again (useful while learning, **deletes all data**):

```bash
php artisan migrate:fresh --seed
```

Other useful migration commands:

| Command | What it does |
|---------|--------------|
| `php artisan migrate:status` | Shows which migrations have run |
| `php artisan migrate:rollback` | Undoes the last batch of migrations |
| `php artisan migrate:fresh` | Drops all tables and re-runs every migration |
| `php artisan db:seed --class=LocationSeeder` | Runs one specific seeder |

Open phpMyAdmin and check that the four tables exist and contain data. You should see 24 rows in `billings`.

---

## 13. Write the Controllers

Each controller has five methods (this is called a **resource controller**):

| Method | Purpose | HTTP verb + URL |
|--------|---------|-----------------|
| `index` | List all | `GET /api/locations` |
| `store` | Create one | `POST /api/locations` |
| `show` | Show one | `GET /api/locations/{id}` |
| `update` | Update one | `PUT /api/locations/{id}` |
| `destroy` | Delete one | `DELETE /api/locations/{id}` |

Replace each file in `app/Http/Controllers/Api/`.

### `LocationController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return Location::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $location = Location::create($data);

        return response()->json($location, 201);
    }

    public function show(Location $location)
    {
        return $location->load('plots');
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $location->update($data);

        return $location;
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return response()->noContent();
    }
}
```

### `PlotController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    public function index()
    {
        return Plot::with('location')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'plot_number' => 'required|string|max:50|unique:plots,plot_number',
            'owner_name' => 'required|string|max:255',
        ]);

        $plot = Plot::create($data);

        return response()->json($plot, 201);
    }

    public function show(Plot $plot)
    {
        return $plot->load(['location', 'billings']);
    }

    public function update(Request $request, Plot $plot)
    {
        $data = $request->validate([
            'location_id' => 'sometimes|required|exists:locations,id',
            'plot_number' => 'sometimes|required|string|max:50|unique:plots,plot_number,' . $plot->id,
            'owner_name' => 'sometimes|required|string|max:255',
        ]);

        $plot->update($data);

        return $plot;
    }

    public function destroy(Plot $plot)
    {
        $plot->delete();

        return response()->noContent();
    }
}
```

### `BillingController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        return Billing::with('plot')->orderBy('record_date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plot_id' => 'required|exists:plots,id',
            'record_date' => 'required|date',
            'meter_reading' => 'required|numeric|min:0',
        ]);

        $billing = Billing::create($data);

        return response()->json($billing, 201);
    }

    public function show(Billing $billing)
    {
        return $billing->load(['plot', 'payments']);
    }

    public function update(Request $request, Billing $billing)
    {
        $data = $request->validate([
            'plot_id' => 'sometimes|required|exists:plots,id',
            'record_date' => 'sometimes|required|date',
            'meter_reading' => 'sometimes|required|numeric|min:0',
        ]);

        $billing->update($data);

        return $billing;
    }

    public function destroy(Billing $billing)
    {
        $billing->delete();

        return response()->noContent();
    }
}
```

### `PaymentController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::with('billing')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'billing_id' => 'required|exists:billings,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'method' => 'nullable|string|max:50',
        ]);

        $payment = Payment::create($data);

        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        return $payment->load('billing');
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'billing_id' => 'sometimes|required|exists:billings,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_date' => 'sometimes|required|date',
            'method' => 'nullable|string|max:50',
        ]);

        $payment->update($data);

        return $payment;
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->noContent();
    }
}
```

**How to read this:**
- `$request->validate([...])` checks the incoming data. If it is wrong, Laravel automatically returns a `422` error with the reasons.
- `exists:locations,id` means the value must exist in the `id` column of the `locations` table.
- `show(Location $location)` is **route model binding**: Laravel sees `{location}` in the URL, looks up that row, and hands you the model. If it does not exist, Laravel returns `404`.
- `with('plot')` and `load('plots')` fetch related records in the same response.
- `201` = created, `204` = deleted (no content).

---

## 14. Define the API Routes

Open `routes/api.php` and add the resource routes. A single `apiResource` line creates all five routes for a controller.

```php
<?php

use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PlotController;
use Illuminate\Support\Facades\Route;

Route::apiResource('locations', LocationController::class);
Route::apiResource('plots', PlotController::class);
Route::apiResource('billings', BillingController::class);
Route::apiResource('payments', PaymentController::class);
```

> `install:api` may have added a `/user` route that uses `auth:sanctum`. You can leave it. The routes above are public, which is fine for learning. Add authentication before using this in production.

See every route Laravel knows about:

```bash
php artisan route:list --path=api
```

You should see 20 routes (5 for each of the 4 resources).

---

## 15. Views in Laravel (Blade)

This project is an API, so it returns JSON and does not need views. But views are the **V** in MVC, so here is how they work if you want an HTML page that shows data (like the Billing List table from the PHP learning project).

Create a view:

```bash
php artisan make:view billings.index
```

This creates `resources/views/billings/index.blade.php`. Replace it with:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Billing List</title>
</head>
<body style="display:grid;place-items:center">
    <h1>Billing List</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Record Date</th>
            <th>Plot ID</th>
            <th>Meter Reading</th>
        </tr>
        @foreach ($billings as $billing)
            <tr>
                <td>{{ $billing->id }}</td>
                <td>{{ $billing->record_date->format('Y-m-d') }}</td>
                <td>{{ $billing->plot_id }}</td>
                <td>{{ $billing->meter_reading }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
```

Add a web route in `routes/web.php`:

```php
use App\Models\Billing;

Route::get('/billings', function () {
    return view('billings.index', ['billings' => Billing::all()]);
});
```

Visit `http://127.0.0.1:8000/billings` to see the same table you built in plain PHP.

- `{{ ... }}` prints a value **and escapes it automatically** (the same job `htmlspecialchars()` did in the PHP version).
- `@foreach ... @endforeach` is the Blade version of the `while ... endwhile` loop.

---

## 16. Start the Server

```bash
php artisan serve
```

The app runs at `http://127.0.0.1:8000`. Your API base URL is:

```
http://127.0.0.1:8000/api
```

> If you still have the older plain PHP project running on port 8000, stop it first, or start this one on another port: `php artisan serve --port=8001`.

Quick check in your browser: open `http://127.0.0.1:8000/api/locations`. You should see JSON.

---

## 17. Testing the APIs with Postman

### Step 1: Install and open Postman

Download it from https://www.postman.com/downloads/, install it, and sign in (or continue without an account).

### Step 2: Create a collection

1. Click **Collections** in the left sidebar, then **+** (New Collection).
2. Name it `Water Management API`.

### Step 3: Set up an environment (so you do not repeat the URL)

1. Click **Environments** then **+**.
2. Name it `Local`.
3. Add a variable: `base_url` = `http://127.0.0.1:8000/api`
4. Click **Save**, then select `Local` in the environment dropdown at the top right.

Now you can write `{{base_url}}/locations` in any request.

### Step 4: Add the required headers (important)

For every request, set these headers (or set them once on the collection under **Headers** so all requests inherit them):

| Key | Value |
|-----|-------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` |

`Accept: application/json` tells Laravel to reply with JSON errors. Without it, validation failures may redirect and return an HTML page instead of a readable error.

### Step 5: Create and send requests

For each request: click **Add request** in your collection, choose the method, enter the URL, and for POST/PUT go to the **Body** tab, choose **raw**, choose **JSON**, then paste the body. Click **Send**.

Test in this order because the tables depend on each other: **Locations, Plots, Billings, Payments**.

#### Locations

| Action | Method | URL | Body | Expected status |
|--------|--------|-----|------|-----------------|
| List all | GET | `{{base_url}}/locations` | none | 200 |
| Get one | GET | `{{base_url}}/locations/1` | none | 200 |
| Create | POST | `{{base_url}}/locations` | see below | 201 |
| Update | PUT | `{{base_url}}/locations/3` | see below | 200 |
| Delete | DELETE | `{{base_url}}/locations/3` | none | 204 |

Create body:

```json
{
    "name": "Zone C",
    "description": "Eastern part of the ward"
}
```

Update body:

```json
{
    "name": "Zone C (Updated)"
}
```

#### Plots

| Action | Method | URL | Expected status |
|--------|--------|-----|-----------------|
| List all | GET | `{{base_url}}/plots` | 200 |
| Get one | GET | `{{base_url}}/plots/1` | 200 |
| Create | POST | `{{base_url}}/plots` | 201 |
| Update | PUT | `{{base_url}}/plots/7` | 200 |
| Delete | DELETE | `{{base_url}}/plots/7` | 204 |

Create body (use a `location_id` that exists):

```json
{
    "location_id": 1,
    "plot_number": "A-04",
    "owner_name": "Owner Seven"
}
```

Update body:

```json
{
    "owner_name": "New Owner Name"
}
```

#### Billings

| Action | Method | URL | Expected status |
|--------|--------|-----|-----------------|
| List all | GET | `{{base_url}}/billings` | 200 |
| Get one | GET | `{{base_url}}/billings/1` | 200 |
| Create | POST | `{{base_url}}/billings` | 201 |
| Update | PUT | `{{base_url}}/billings/25` | 200 |
| Delete | DELETE | `{{base_url}}/billings/25` | 204 |

Create body (use a `plot_id` that exists):

```json
{
    "plot_id": 1,
    "record_date": "2026-11-01",
    "meter_reading": 55.5
}
```

Update body:

```json
{
    "meter_reading": 60
}
```

#### Payments

| Action | Method | URL | Expected status |
|--------|--------|-----|-----------------|
| List all | GET | `{{base_url}}/payments` | 200 |
| Get one | GET | `{{base_url}}/payments/1` | 200 |
| Create | POST | `{{base_url}}/payments` | 201 |
| Update | PUT | `{{base_url}}/payments/3` | 200 |
| Delete | DELETE | `{{base_url}}/payments/3` | 204 |

Create body (use a `billing_id` that exists):

```json
{
    "billing_id": 9,
    "amount": 1000,
    "payment_date": "2026-08-10",
    "method": "mpesa"
}
```

Update body:

```json
{
    "amount": 1200
}
```

### Step 6: Test the error cases too

Good testers try to break things. Try these:

| Test | What to do | Expected result |
|------|------------|-----------------|
| Missing required field | POST `/locations` with `{}` | `422` with a message saying `name` is required |
| Record not found | GET `/locations/999` | `404` |
| Invalid foreign key | POST `/plots` with `"location_id": 999` | `422` (location does not exist) |
| Duplicate value | POST `/plots` with a `plot_number` that already exists | `422` (already taken) |

### Common status codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 201 | Created |
| 204 | Deleted, nothing to return |
| 404 | Not found |
| 405 | Wrong method (e.g. GET on a POST-only route) |
| 422 | Validation failed (read the response body) |
| 500 | Server error (check `storage/logs/laravel.log`) |

---

## 18. Troubleshooting

| Problem | Likely cause and fix |
|---------|----------------------|
| `Could not send request` in Postman | The server is not running. Run `php artisan serve`. |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL is not running. Start it in XAMPP. |
| `Unknown database 'water_management'` | Create the database in phpMyAdmin first. |
| `404` on every `/api/...` route | You forgot `php artisan install:api`, or the URL is missing `/api`. Run `php artisan route:list --path=api`. |
| Response is an HTML page, not JSON | Add the header `Accept: application/json`. |
| `Class "App\Models\..." not found` | Check the `namespace` and `use` lines at the top of the file. |
| `Add [x] to fillable property` error | Add the column to `$fillable` in the model. |
| Foreign key constraint error while migrating | Migrations ran in the wrong order. Delete the migration files, and recreate the models parents-first. |
| Changed `.env` but nothing happens | Run `php artisan config:clear`. |
| Anything strange | Read `storage/logs/laravel.log`. The newest error is at the bottom. |

---

## 19. Artisan Command Cheat Sheet

| Command | What it does |
|---------|--------------|
| `php artisan serve` | Starts the development server |
| `php artisan install:api` | Adds API routes and Sanctum |
| `php artisan make:model Name -mcs` | Model + migration + controller + seeder |
| `php artisan make:model Name -a` | Model + everything (factory, seeder, controller, requests, policy) |
| `php artisan make:migration create_x_table` | A new migration |
| `php artisan make:controller Api/NameController --api --model=Name` | API resource controller |
| `php artisan make:seeder NameSeeder` | A new seeder |
| `php artisan make:factory NameFactory` | A new factory |
| `php artisan make:view folder.name` | A new Blade view |
| `php artisan make:resource NameResource` | Shapes the JSON output of a model |
| `php artisan migrate` | Runs new migrations |
| `php artisan migrate:fresh --seed` | Rebuilds the whole database with sample data |
| `php artisan db:seed` | Runs the seeders |
| `php artisan route:list` | Lists all routes |
| `php artisan config:clear` | Clears cached config after `.env` changes |
| `php artisan tinker` | An interactive shell to test models, e.g. `App\Models\Plot::count()` |

---

## 20. Cloning and Running This Project

If you (or someone else) are setting this project up from GitHub:

```bash
git clone https://github.com/marz-w/water-management-backend.git
cd water-management-backend

composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database details (see [section 5](#5-connect-to-mysql)), create the `water_management` database in phpMyAdmin, then:

```bash
php artisan migrate --seed
php artisan serve
```

On Windows PowerShell, use `copy .env.example .env` instead of `cp`.

---

## Author

**Martha** ([@marz-w](https://github.com/marz-w))

Built as part of learning server-side programming with PHP and Laravel.