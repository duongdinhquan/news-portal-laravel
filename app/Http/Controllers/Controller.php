<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // Laravel's Gate / Policy authorization helpers are available through the framework's controller middleware.
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
}
