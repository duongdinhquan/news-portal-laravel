<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('news:about', function () {
    $this->info('News Portal - Laravel MVC / Blade / MySQL / JWT');
})->purpose('Display project information');
