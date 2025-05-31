<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


//Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('12:42');
Schedule::command('backup:monitor')->daily()->at('17:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
