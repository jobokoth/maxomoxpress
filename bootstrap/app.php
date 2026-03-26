<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.school'    => \App\Http\Middleware\SetTenantSchool::class,
            'school.access'    => \App\Http\Middleware\EnsureUserBelongsToSchool::class,
            'onboarded'        => \App\Http\Middleware\EnsureSchoolIsOnboarded::class,
            'not.onboarded'    => \App\Http\Middleware\RedirectIfNotOnboarded::class,
            'parent.portal'    => \App\Http\Middleware\EnsureParentPortalAccess::class,
            'student.portal'   => \App\Http\Middleware\EnsureStudentPortalAccess::class,
            'platform.admin'   => \App\Http\Middleware\EnsurePlatformAdmin::class,
            'role'             => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'       => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
