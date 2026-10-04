<?php

namespace App\Http\Controllers\Pages\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;
use App\Http\Middleware\RoleBasedRedirect;


// middleware(['auth','verified', RoleBasedRedirect::class])

class IndexController extends Controller
{
    //
    // #[Route(fullUri : 'admin',name:'admin',middleware : ['auth','verified'])]
    // // #[Middleware(['auth','verified',RoleBasedRedirect::class])]
    // public function index()
    // {
    //     return view('pages.admin.index');
    // }




}
