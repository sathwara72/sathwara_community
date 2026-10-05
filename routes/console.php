<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Ends expired business memberships (free renewal: renews automatically; paid: closes and emails the owner)
Schedule::command('business:deactivate-expired')->hourly();
