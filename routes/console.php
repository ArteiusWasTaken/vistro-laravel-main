<?php


use App\Console\Commands\KeepPrinterAlive;
use Illuminate\Support\Facades\Schedule;

Schedule::command(KeepPrinterAlive::class)->everyThirtySeconds();
