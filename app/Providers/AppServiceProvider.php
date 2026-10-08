<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Setting;
use App\View\Composers\AdminNotificationComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Admin-edited translations override the lang files
        $this->app->extend('translation.loader', fn ($loader) => new \App\Translation\DatabaseOverrideLoader($loader));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $mailer = Setting::get('mail_mailer');
                if ($mailer) {
                    $encryption = Setting::get('mail_encryption');
                    config([
                        'mail.default' => $mailer,
                        'mail.mailers.smtp.host' => Setting::get('mail_host', config('mail.mailers.smtp.host')),
                        'mail.mailers.smtp.port' => Setting::get('mail_port', config('mail.mailers.smtp.port')),
                        'mail.mailers.smtp.username' => Setting::get('mail_username', config('mail.mailers.smtp.username')),
                        'mail.mailers.smtp.password' => Setting::get('mail_password', config('mail.mailers.smtp.password')),
                        'mail.mailers.smtp.encryption' => ($encryption === 'null' || strtolower($encryption) === 'none') ? null : $encryption,
                        'mail.from.address' => Setting::get('mail_from_address', config('mail.from.address')),
                        'mail.from.name' => Setting::get('mail_from_name', config('mail.from.name')),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Silence provider errors before database migrations run
        }

        $this->registerGujaratiFont();

        View::composer('layouts.admin', AdminNotificationComposer::class);
    }

    /**
     * Register the Gujarati fonts used by receipt PDFs with dompdf.
     * Registration is persisted to dompdf's font cache (storage/fonts), so
     * this only runs the (heavier) registration once and self-heals if that
     * cache is ever cleared. The marker is versioned so adding a new font
     * here re-runs registration exactly once, even on installs that already
     * registered an earlier version.
     */
    protected function registerGujaratiFont(): void
    {
        $marker = storage_path('fonts/.gujarati_registered_v2');

        if (file_exists($marker)) {
            return;
        }

        try {
            if (!is_dir(storage_path('fonts'))) {
                mkdir(storage_path('fonts'), 0755, true);
            }

            $fontMetrics = app('dompdf')->getFontMetrics();
            $fontMetrics->registerFont(
                ['family' => 'NotoSansGujarati', 'weight' => 'normal', 'style' => ''],
                resource_path('fonts/gujarati/NotoSansGujarati-Regular.ttf')
            );
            $fontMetrics->registerFont(
                ['family' => 'NotoSansGujarati', 'weight' => 'bold', 'style' => ''],
                resource_path('fonts/gujarati/NotoSansGujarati-Bold.ttf')
            );
            $fontMetrics->registerFont(
                ['family' => 'HindVadodara', 'weight' => 'normal', 'style' => ''],
                resource_path('fonts/gujarati/HindVadodara-Regular.ttf')
            );
            $fontMetrics->registerFont(
                ['family' => 'HindVadodara', 'weight' => 'bold', 'style' => ''],
                resource_path('fonts/gujarati/HindVadodara-Bold.ttf')
            );

            file_put_contents($marker, now()->toDateTimeString());
        } catch (\Throwable $e) {
            // dompdf not ready yet (e.g. during install); it will retry on the next request
        }
    }
}
