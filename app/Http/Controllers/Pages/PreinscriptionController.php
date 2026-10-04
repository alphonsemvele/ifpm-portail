<?php
namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Mail\notifMail;
use App\Models\Arrondissement;
use App\Models\Cycle;
use App\Models\Department;
use App\Models\Filiere;
use App\Models\Preinscription;
use App\Models\Region;
use App\Models\Specialite;
use Illuminate\Support\Facades\Mail;
use Mauricius\LaravelHtmx\Http\HtmxRequest;
use Mauricius\LaravelHtmx\Http\HtmxResponse;
use PDF;
use Spatie\RouteDiscovery\Attributes\Route;
use Illuminate\Support\Facades\Storage;

class PreinscriptionController extends Controller
{
    #[Route(fullUri: 'preinscription', name: 'preinscription.index')]
    public function index()
    {
        $filieres = Filiere::where('status', 'success')->get();
        $specialites = Specialite::where('status', 'success')->get();
        $arrondissements = Arrondissement::all();
        $regions = Region::all();
        return view('pages.preinscription.index', ['filieres' => $filieres, 'specialites' => $specialites, 'arrondissements' => $arrondissements, 'regions' => $regions]);
    }

    #[Route(fullUri: 'preinscription/store', name: 'preinscription.store', method: 'post')]
    public function store(HtmxRequest $request)
    {
        try {
            // Valider la requête
            $validated = $request->validate([
                'formation_name' => 'required|string|in:ISM,IFPM',
                'cycle_id' => 'nullable|integer|exists:cycles,id',
                'filiere_id' => 'required|integer|exists:filieres,id',
                'specialite_id' => 'required|integer|exists:specialites,id',
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'birth' => 'required|date',
                'region_id' => 'required|integer|exists:regions,id',
                'departement_id' => 'nullable|integer|exists:departments,id',
                'arrondissement_id' => 'nullable|integer|exists:arrondissements,id',
                'contact' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'father' => 'required|string|max:255',
                'mother' => 'required|string|max:255',
                'birth_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
                'diploma' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
            ]);

            // Stocker les fichiers téléchargés
            $birthCertificatePath = $request->file('birth_certificate')->store('documents', 'public');
            $diplomaPath = $request->file('diploma')->store('documents', 'public');

            // Préparer les données pour l'insertion
            $data = $validated;
            $data['birth_certificate'] = $birthCertificatePath;
            $data['diploma'] = $diplomaPath;

            // Créer l'enregistrement de préinscription
            $insert = Preinscription::create($data);

            // Générer le code de référence
            $prefix = $data['formation_name'] === 'ISM' ? 'ISM-' : 'IFPM-';
            $ref = $prefix . str_pad($insert->id, 6, '0', STR_PAD_LEFT); // ex. : ISM-000001 ou IFPM-000001
            $insert->update(['ref' => $ref]);

            // Récupérer la filière pour le contenu de l'email
            $filiere = Filiere::where('id', $data['filiere_id'])->first();

            if ($insert) {
                $subject = "PREINSCRIPTION " . $data['formation_name'];
                $downloadUrl = route("preinscriptionDocument", ["id" => $insert->id]);
                $content = sprintf(
                    'Mr/Mme %s,<br><br>Nous vous remercions pour votre préinscription à la filière %s (Référence: %s). Votre demande a bien été reçue, et les documents (acte de naissance et diplôme) ont été enregistrés avec succès.<br><br>Veuillez télécharger votre fiche en cliquant sur <a href="%s" class="mt-5 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">Télécharger ma fiche</a>.',
                    htmlspecialchars($data['name']),
                    htmlspecialchars($filiere->name),
                    htmlspecialchars($ref),
                    $downloadUrl
                );

                // Envoyer la notification par email
                Mail::to($data['email'])->send(new notifMail($subject, $content));

                // Retourner une réponse de succès avec redirection HTMX
                return response('<div class="flex justify-center mt-2 p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    Préinscription envoyée avec succès ! Référence: ' . htmlspecialchars($ref) . '. Les documents ont été reçus. ISM vous contactera pour la suite de la procédure.
                </div>')
                    ->header('HX-Redirect', route('preinscriptionDocument', ['id' => $insert->id]));
            } else {
                return response('<div class="flex justify-center mt-2 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    Une erreur est survenue, veuillez réessayer.
                </div>');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response('<div class="flex justify-center mt-2 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                Erreur de validation : ' . implode(', ', $e->errors()[array_key_first($e->errors())]) . '
            </div>');
        } catch (\Exception $e) {
            return response('<div class="flex justify-center mt-2 p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                Une erreur inattendue est survenue : ' . $e->getMessage() . '
            </div>');
        }
    }

    #[Route(fullUri: 'fetch-item/{type}', name: 'fetchItem')]
    public function fetchItem(HtmxRequest $request, $type)
    {
        if ($type == 'formation') {
            $cycles = Cycle::where('status', 'success')->where('institution', $request->formation_name)->get();
            if ($cycles->isNotEmpty()) {
                $filieres = Filiere::where('cycle_id', $cycles[0]->id)->get();
                $specialites = $filieres->isNotEmpty() ? Specialite::where('filiere_id', $filieres[0]->id)->get() : collect([]);
                return with(new HtmxResponse)
                    ->addFragment('pages.fragment.preinscription', 'cycle', compact('cycles'));
                    // ->addFragment('pages.fragment.preinscription', 'filiere', compact('filieres'))
                    // ->addFragment('pages.fragment.preinscription', 'specialite', compact('specialites'));
            }
            $filieres = Filiere::where('institution', 'IFPM')->get();
            $specialites = $filieres->isNotEmpty() ? Specialite::where('filiere_id', $filieres[0]->id)->get() : collect([]);
            return with(new HtmxResponse)
                ->addFragment('pages.fragment.preinscription', 'cycle', ['cycles' => collect([])])
                ->addFragment('pages.fragment.preinscription', 'filiere', compact('filieres'))
                ->addFragment('pages.fragment.preinscription', 'specialite', compact('specialites'));
        }

        if ($type == 'cycle') {
            $filieres = Filiere::where('cycle_id', $request->cycle_id)->get();
            $specialites = $filieres->isNotEmpty() ? Specialite::where('filiere_id', $filieres[0]->id)->get() : collect([]);
            return with(new HtmxResponse)
                ->addFragment('pages.fragment.preinscription', 'filiere', compact('filieres'))
                ->addFragment('pages.fragment.preinscription', 'specialite', compact('specialites'));
        }

        if ($type == "filiere") {
            $specialites = Specialite::where('filiere_id', $request->filiere_id)->get();
            return with(new HtmxResponse)
                ->addFragment('pages.fragment.preinscription', 'specialite', compact('specialites'));
        }

        if ($type == 'region') {
            $departments = Department::where('region_id', $request->region_id)->get();
            $arrondissements = $departments->isNotEmpty() ? Arrondissement::where('department_id', $departments[0]->id)->get() : collect([]);
            return with(new HtmxResponse)
                ->addFragment('pages.fragment.preinscription', 'departement', compact('departments'))
                ->addFragment('pages.fragment.preinscription', 'arrondissement', compact('arrondissements'));
        }

        if ($type == 'departement') {
            $arrondissements = Arrondissement::where('department_id', $request->departement_id)->get();
            return with(new HtmxResponse)
                ->addFragment('pages.fragment.preinscription', 'arrondissement', compact('arrondissements'));
        }
    }

    #[Route(fullUri: 'preinscriptionDocument/{ref}', name: 'preinscriptionDocument')]
    public function getPreinscriptionDocument($ref)
    {
        $preinscription = Preinscription::where('ref', $ref)->first();
        return view('pages.preinscription.document', ['preinscription' => $preinscription]);
    }

    #[Route(fullUri: 'generate-pdf/{ref}', name: 'generate.pdf')]
    public function generatePDF($ref)
    {
        // Récupérer les données de préinscription
        $student = Preinscription::where('ref', $ref)->firstOrFail();

        // Chemin absolu pour le logo
        $logoPath = public_path('images/logo.png');
        $logo_ifpm = public_path('images/logo_ifpm.png');

        // Générer le PDF
        $pdf = PDF::loadView('pages.preinscription.fiche_inscription', compact('student', 'logoPath','logo_ifpm'));

        // Télécharger le PDF
        return $pdf->download('fiche_inscription_' . $student->name . '.pdf');
    }
}
