<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Base controller for the whole app.
 *
 * In Laravel 11 the default base controller is empty - the framework no
 * longer includes the AuthorizesRequests trait by default. We pull it in
 * here so that every controller can call $this->authorize($ability, $model)
 * (which is the idiomatic Laravel way to invoke a Policy from a controller).
 *
 * The other two traits (DispatchesJobs, ValidatesRequests) round out the
 * old Laravel 10 base for parity - harmless if unused.
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}
