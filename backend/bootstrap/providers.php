<?php

declare(strict_types=1);

use App\Modules\Auth\AuthServiceProvider;
use App\Modules\Rbac\RbacServiceProvider;
use App\Modules\Organization\OrganizationServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    AuthServiceProvider::class,
    RbacServiceProvider::class,
    OrganizationServiceProvider::class,
];
