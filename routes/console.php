<?php

use App\Console\Commands\SendKeepAliveToPrinter;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SendKeepAliveToPrinter::class)->everyMinute();
