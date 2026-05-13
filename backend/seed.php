<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = App\Modules\Organization\Models\Company::firstOrCreate(
    ['name' => 'TurboTech ERP'],
    ['email' => 'admin@turbotech.com', 'currency' => 'USD']
);

App\Models\User::where('email', 'uat@gmail.com')->update(['tenant_id' => $company->id]);
echo "Seeded Company ID: " . $company->id . "\n";
