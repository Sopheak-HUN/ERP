<?php

declare(strict_types=1);

use App\Modules\Auth\AuthServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    AuthServiceProvider::class,
];
