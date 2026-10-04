<?php

namespace App\Http\Controllers\Pages\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;
use App\Http\Middleware\RoleBasedRedirect;

class IndexController extends Controller
{
    // #[Middleware([RoleBasedRedirect::class])]
    #[Route(fullUri:'dashboard',middleware : ['auth','verified',RoleBasedRedirect::class])]
    public function dashboard()
    {
        return view('pages.dashboard.index');
    }
}
