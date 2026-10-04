<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;
use Mauricius\LaravelHtmx\Http\HtmxRequest;
use Mauricius\LaravelHtmx\Http\HtmxResponse;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Arrondissement;
use App\Models\Region;
use App\Models\Cycle;
use App\Models\Departement;

class test extends Controller
{

    #[Route(uri:'test', name : 'preinscription.index')]
    public function index()
    {
        $filieres = Filiere::where('status','success')->get();
        $specialites = Specialite::where('status','success')->get();
        $cycles = Cycle::where('status','success')->get();
        $arrondissements = Arrondissement::all();
        $regions = Region::all();
        $departements = Departement::all();
        return view('pages.preinscription.index',['filieres'=>$filieres,'specialites'=>$specialites,'arrondissements'=>$arrondissements,'regions'=>$regions,'departments'=>$departments,'cycles'=>$cycles]);
    }



    #[Route(uri : 'preinscription/store', name :'preinscription.store', method : 'post')]
    public function store(HtmxRequest $request)
    {




    }

    #[Route(uri:'fetch-item/{data}', name:'fetchItem')]
    public function fetchItem(HtmxRequest $request)
    {
        Log::info($request->formation_type);
    }
}
