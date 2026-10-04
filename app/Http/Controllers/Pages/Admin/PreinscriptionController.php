<?php

namespace App\Http\Controllers\Pages\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;
use App\Models\Preinscription;
use Mauricius\LaravelHtmx\Http\HtmxRequest;
use Mauricius\LaravelHtmx\Http\HtmxResponse;


class PreinscriptionController extends Controller
{

    #[Route(fullUri:'admin/preinscription',name:'admin.preinscription')]
    public function index()
    {

        $preinscriptions = Preinscription::with('filiere.specialites')->with('region')->with('departement')->with('arrondissement')->get();
// dd($preinscriptions);

        return view('pages.admin.preinscription.index',compact('preinscriptions'));
    }
    #[Route(fullUri:'admin/preinscription/validated',name:'validatedPreinscription')]
    public function validatedPreinscription(HtmxRequest $request)
    {
        $data = Preinscription::where('id',$request->id)->first();

        if(isset($data))
        {
            $data->update([
                'status'=>'success'
            ]);
        }

    //    dd($request->id);

        return with(new HtmxResponse())
        ->addFragment('pages.admin.preinscription.components.fragment','successTable',compact('data'))
        // ->addFragmentRaw('<tr id="pending-'.$data->id.'" hx-swap-oob="delete"></tr>')
        ->addTrigger('closeActivatedModal');
        // ->addTrigger( 'closeActivatedModal');


    }
}
