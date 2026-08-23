<?php

namespace App\Providers;

use App\Support\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerAuditListeners();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Register audit log listeners for authentication events.
     */
    protected function registerAuditListeners(): void
    {
        Event::listen(Login::class, function (Login $event) {
            AuditLogger::log(
                action: 'auth.login',
                description: "User {$event->user->name} ({$event->user->credential}) logged in.",
                subject: $event->user,
                userId: $event->user->id
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLogger::log(
                    action: 'auth.logout',
                    description: "User {$event->user->name} ({$event->user->credential}) logged out.",
                    subject: $event->user,
                    userId: $event->user->id
                );
            }
        });

        Event::listen(OtherDeviceLogout::class, function (OtherDeviceLogout $event) {
            if ($event->user) {
                AuditLogger::log(
                    action: 'auth.session_terminated',
                    description: "User {$event->user->name} session terminated from another device or timeout.",
                    subject: $event->user,
                    userId: $event->user->id
                );
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            $cred = $event->credentials['credential'] ?? 'unknown';
            AuditLogger::log(
                action: 'auth.failed_login',
                description: "Failed login attempt for credential: {$cred}.",
                subject: $event->user,
                userId: $event->user?->id
            );
        });
    }
}
