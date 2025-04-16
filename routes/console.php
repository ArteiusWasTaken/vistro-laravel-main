<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('picking:run')->everyFiveMinutes();
