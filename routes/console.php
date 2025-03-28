<?php


use App\Console\Commands\KeepPrinterAlive;
use Illuminate\Support\Facades\Schedule;

Schedule::command('printer:keep-alive')->everyThirtySeconds();
