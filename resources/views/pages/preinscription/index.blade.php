<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Cycle;
use App\Models\Filiere;
use App\Models\Specialite;
use App\Models\Region;
use App\Models\Department;
use App\Models\Arrondissement;
use App\Models\Preinscription;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifMail;

name('preinscription.index');
// middleware(['auth', 'verified']);

new class extends Component {
    use WithFileUploads;

    // Propriétés du formulaire
    public string $formation_name = '';
    public $cycle_id = null;
    public $filiere_id = null;
    public $specialite_id = null;
    public string $name = '';
    public string $last_name = '';
    public string $birth = '';
    public $region_id = null;
    public $departement_id = null;
    public $arrondissement_id = null;
    public string $contact = '';
    public string $email = '';
    public string $father = '';
    public string $mother = '';
    public $birth_certificate = null;
    public $diploma = null;
    public string $whatsapp_number = ''; // Nouveau champ
    public string $parrain_name = ''; // Nouveau champ
    public string $parrain_contact = ''; // Nouveau champ

    // Propriétés pour les données dynamiques
    public $cycles = [];
    public $filieres = [];
    public $specialites = [];
    public $regions = [];
    public $departements = [];
    public $arrondissements = [];

    // Propriétés pour la gestion des notifications
    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = '';
    public array $formErrors = [];

    // Propriétés pour la section de confirmation et édition
    public bool $showConfirmation = false;
    public array $submittedData = [];
    public string $reference = '';
    public $preinscription; // Pour stocker l'enregistrement
    public bool $isEditing = false; // Pour indiquer si on est en mode modification

    public function mount()
    {
        $this->chargerDonneesInitiales();
    }

    public function chargerDonneesInitiales()
    {
        try {
            $this->cycles = Cycle::where('status', 'Success')->get();
            $this->regions = Region::all();
            logger('Données initiales chargées', [
                'cycles_count' => $this->cycles->count(),
                'regions_count' => $this->regions->count(),
            ]);
        } catch (\Exception $e) {
            logger('Erreur lors du chargement des données initiales: ' . $e->getMessage());
            $this->cycles = collect();
            $this->regions = collect();
        }
    }

    public function updatedFormationName()
    {
        $this->cycle_id = null;
        $this->filiere_id = null;
        $this->specialite_id = null;
        $this->filieres = [];
        $this->specialites = [];

        if ($this->formation_name) {
            $this->filieres = Filiere::where('status', 'Success')
                ->where('institution', $this->formation_name)
                ->get();
        }
        logger('Type de formation mis à jour: ' . $this->formation_name, ['filieres_count' => $this->filieres->count()]);
    }

    public function updatedCycleId()
    {
        $this->filiere_id = null;
        $this->specialite_id = null;
        $this->filieres = [];
        $this->specialites = [];

        if ($this->formation_name === 'ISM' && $this->cycle_id) {
            $this->filieres = Filiere::where('status', 'Success')
                ->where('institution', 'ISM')
                ->where('cycle_id', $this->cycle_id)
                ->get();
        }
        logger('Cycle ID mis à jour: ' . $this->cycle_id, ['filieres_count' => $this->filieres->count()]);
    }

    public function updatedFiliereId()
    {
        $this->specialite_id = null;
        $this->specialites = [];

        if ($this->filiere_id) {
            $this->specialites = Specialite::where('status', 'Success')
                ->where('filiere_id', $this->filiere_id)
                ->get();
        }
        logger('Filière ID mis à jour: ' . $this->filiere_id, ['specialites_count' => $this->specialites->count()]);
    }

    public function updatedRegionId()
    {
        $this->departement_id = null;
        $this->arrondissement_id = null;
        $this->departements = [];
        $this->arrondissements = [];

        if ($this->region_id) {
            $this->departements = Department::where('region_id', $this->region_id)->get();
        }
        logger('Région ID mis à jour: ' . $this->region_id, ['departements_count' => $this->departements->count()]);
    }

    public function updatedDepartementId()
    {
        $this->arrondissement_id = null;
        $this->arrondissements = [];

        if ($this->departement_id) {
            $this->arrondissements = Arrondissement::where('department_id', $this->departement_id)->get();
        }
        logger('Département ID mis à jour: ' . $this->departement_id, ['arrondissements_count' => $this->arrondissements->count()]);
    }

    public function store()
    {
        try {
            $rules = [
                'formation_name' => 'required|string|in:ISM,IFPM',
                'cycle_id' => $this->formation_name === 'ISM' ? 'required|integer|exists:cycles,id' : 'nullable',
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
                'birth_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'diploma' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'whatsapp_number' => 'required|string|max:20', // Validation du nouveau champ
                'parrain_name' => 'required|string|max:255', // Validation du nouveau champ
                'parrain_contact' => 'required|string|max:20', // Validation du nouveau champ
            ];

            $validated = $this->validate($rules);

            // Stocker les fichiers téléchargés
            $birthCertificatePath = $this->birth_certificate->store('documents', 'public');
            $diplomaPath = $this->diploma->store('documents', 'public');

            // Récupérer le prix de la spécialité
            $specialite = Specialite::find($this->specialite_id);
            $price = $specialite ? $specialite->price : 0;

            // Préparer les données pour l'insertion
            $data = $validated;
            $data['birth_certificate'] = $birthCertificatePath;
            $data['diploma'] = $diplomaPath;
            $data['price'] = $price; // Ajout du prix
            $data['ref'] = 'TEMP-' . uniqid();

            // Créer l'enregistrement de préinscription
            $preinscription = Preinscription::create($data);

            // Générer la référence définitive
            $prefix = $data['formation_name'] === 'ISM' ? 'ISM-' : 'IFPM-';
            $ref = $prefix . str_pad($preinscription->id, 6, '0', STR_PAD_LEFT);
            $preinscription->update(['ref' => $ref]);

            // Récupérer les informations pour l'affichage
            $filiere = Filiere::where('id', $data['filiere_id'])->first();
            $specialite = Specialite::where('id', $data['specialite_id'])->first();
            $region = Region::where('id', $data['region_id'])->first();
            $departement = $data['departement_id'] ? Department::where('id', $data['departement_id'])->first() : null;
            $arrondissement = $data['arrondissement_id'] ? Arrondissement::where('id', $data['arrondissement_id'])->first() : null;
            $cycle = $data['cycle_id'] ? Cycle::where('id', $data['cycle_id'])->first() : null;

            // Stocker les données pour la section de confirmation
            $this->submittedData = [
                'formation_name' => $data['formation_name'],
                'cycle' => $cycle ? $cycle->name : null,
                'filiere' => $filiere ? $filiere->name : 'Non défini',
                'specialite' => $specialite ? $specialite->name : 'Non défini',
                'name' => $data['name'],
                'last_name' => $data['last_name'],
                'birth' => \Carbon\Carbon::parse($data['birth'])->format('d/m/Y'),
                'region' => $region ? $region->name : 'Non défini',
                'departement' => $departement ? $departement->name : 'Non défini',
                'arrondissement' => $arrondissement ? $arrondissement->name : 'Non défini',
                'contact' => $data['contact'],
                'email' => $data['email'],
                'father' => $data['father'],
                'mother' => $data['mother'],
                'birth_certificate' => $birthCertificatePath,
                'diploma' => $diplomaPath,
                'whatsapp_number' => $data['whatsapp_number'], // Nouveau champ
                'parrain_name' => $data['parrain_name'], // Nouveau champ
                'parrain_contact' => $data['parrain_contact'], // Nouveau champ
            ];
            $this->reference = $ref;
            $this->preinscription = $preinscription;

            // Envoyer l'email de notification
            try {
                $subject = "Préinscription " . $data['formation_name'];
                $content = sprintf(
                    'Mr/Mme %s,<br><br>Nous vous remercions pour votre préinscription à la filière %s (Référence: %s). Votre dossier est en cours de traitement, et notre institut vous contactera dans les plus brefs délais.<br><br>Merci de votre confiance.',
                    htmlspecialchars($data['name']),
                    htmlspecialchars($filiere->name),
                    htmlspecialchars($ref)
                );

                Mail::to($data['email'])->send(new NotifMail($subject, $content));
            } catch (\Exception $e) {
                logger('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            }

            // Afficher la notification de succès
            $this->afficherNotificationSucces('Préinscription envoyée avec succès ! Votre dossier est en cours de traitement. Référence: ' . $ref);

            // Afficher la section de confirmation
            $this->showConfirmation = true;

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
            logger('Erreur de validation: ' . json_encode($e->errors()));
            $this->afficherNotificationErreur('Veuillez corriger les erreurs dans le formulaire.');
        } catch (\Exception $e) {
            logger('Erreur lors de l\'enregistrement: ' . $e->getMessage());
            $this->afficherNotificationErreur('Une erreur inattendue est survenue : ' . $e->getMessage());
            $this->isEditing = true; // Permet de rester dans le formulaire avec les données
        }
    }

    public function editPreinscription()
    {
        if ($this->preinscription) {
            $this->formation_name = $this->preinscription->formation_name;
            $this->cycle_id = $this->preinscription->cycle_id;
            $this->filiere_id = $this->preinscription->filiere_id;
            $this->specialite_id = $this->preinscription->specialite_id;
            $this->name = $this->preinscription->name;
            $this->last_name = $this->preinscription->last_name;
            $this->birth = $this->preinscription->birth;
            $this->region_id = $this->preinscription->region_id;
            $this->departement_id = $this->preinscription->departement_id;
            $this->arrondissement_id = $this->preinscription->arrondissement_id;
            $this->contact = $this->preinscription->contact;
            $this->email = $this->preinscription->email;
            $this->father = $this->preinscription->father;
            $this->mother = $this->preinscription->mother;
            $this->whatsapp_number = $this->preinscription->whatsapp_number; // Nouveau champ
            $this->parrain_name = $this->preinscription->parrain_name; // Nouveau champ
            $this->parrain_contact = $this->preinscription->parrain_contact; // Nouveau champ
            $this->showConfirmation = false;
            $this->isEditing = true;
            $this->chargerDonneesInitiales(); // Recharger les données dynamiques
        }
    }

    public function update()
    {
        try {
            $rules = [
                'formation_name' => 'required|string|in:ISM,IFPM',
                'cycle_id' => $this->formation_name === 'ISM' ? 'required|integer|exists:cycles,id' : 'nullable',
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
                'birth_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'diploma' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'whatsapp_number' => 'required|string|max:20', // Validation du nouveau champ
                'parrain_name' => 'required|string|max:255', // Validation du nouveau champ
                'parrain_contact' => 'required|string|max:20', // Validation du nouveau champ
            ];

            $validated = $this->validate($rules);

            // Mettre à jour les fichiers si de nouveaux sont uploadés
            if ($this->birth_certificate) {
                $validated['birth_certificate'] = $this->birth_certificate->store('documents', 'public');
            }
            if ($this->diploma) {
                $validated['diploma'] = $this->diploma->store('documents', 'public');
            }

            // Récupérer le prix de la spécialité
            $specialite = Specialite::find($this->specialite_id);
            $price = $specialite ? $specialite->price : 0;

            // Mettre à jour les données avec le prix
            $validated['price'] = $price; // Ajout du prix
            $this->preinscription->update($validated);

            // Recharger les données pour la confirmation
            $filiere = Filiere::find($this->preinscription->filiere_id);
            $specialite = Specialite::find($this->preinscription->specialite_id);
            $region = Region::find($this->preinscription->region_id);
            $departement = $this->preinscription->departement_id ? Department::find($this->preinscription->departement_id) : null;
            $arrondissement = $this->preinscription->arrondissement_id ? Arrondissement::find($this->preinscription->arrondissement_id) : null;
            $cycle = $this->preinscription->cycle_id ? Cycle::find($this->preinscription->cycle_id) : null;

            $this->submittedData = [
                'formation_name' => $this->preinscription->formation_name,
                'cycle' => $cycle ? $cycle->name : null,
                'filiere' => $filiere ? $filiere->name : 'Non défini',
                'specialite' => $specialite ? $specialite->name : 'Non défini',
                'name' => $this->preinscription->name,
                'last_name' => $this->preinscription->last_name,
                'birth' => \Carbon\Carbon::parse($this->preinscription->birth)->format('d/m/Y'),
                'region' => $region ? $region->name : 'Non défini',
                'departement' => $departement ? $departement->name : 'Non défini',
                'arrondissement' => $arrondissement ? $arrondissement->name : 'Non défini',
                'contact' => $this->preinscription->contact,
                'email' => $this->preinscription->email,
                'father' => $this->preinscription->father,
                'mother' => $this->preinscription->mother,
                'birth_certificate' => $this->preinscription->birth_certificate,
                'diploma' => $this->preinscription->diploma,
                'whatsapp_number' => $this->preinscription->whatsapp_number, // Nouveau champ
                'parrain_name' => $this->preinscription->parrain_name, // Nouveau champ
                'parrain_contact' => $this->preinscription->parrain_contact, // Nouveau champ
            ];
            $this->reference = $this->preinscription->ref;
            $this->showConfirmation = true;
            $this->isEditing = false;
            $this->afficherNotificationSucces('Informations mises à jour avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
            logger('Erreur de validation: ' . json_encode($e->errors()));
            $this->afficherNotificationErreur('Veuillez corriger les erreurs dans le formulaire.');
        } catch (\Exception $e) {
            logger('Erreur lors de la mise à jour: ' . $e->getMessage());
            $this->afficherNotificationErreur('Une erreur est survenue lors de la mise à jour.');
        }
    }

    private function afficherNotificationSucces($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'success';
        $this->showNotification = true;
    }

    private function afficherNotificationErreur($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'error';
        $this->showNotification = true;
    }
};
?>

<x-layouts.app :header="false">
    @volt
        <div class="flex flex-col md:flex-row justify-center mx-auto w-full max-w-7xl min-h-screen bg-gray-100 p-6 gap-4" style="align-items: flex-start;">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
            <!-- Colonne gauche : Constitution du dossier -->
            <div class="w-full max-w-xl md:w-1/2 p-6 bg-white shadow-md rounded-lg justify-center">
                <img src="{{ asset('asset_vitrine/assets/img/preinscription/school.jpeg') }}" alt="Image de l'école"
                    class="w-full h-48 object-cover rounded-t-lg mb-6">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Constitution du dossier d'admission</h2>
                <ul class="list-disc pl-6 space-y-2 text-gray-700 px-4">
                    <li>2 photos d’identité 4x4 en couleur</li>
                    <li>Copie conforme de l’acte de naissance</li>
                    <li>Copie du dernier diplôme obtenu (BAC ou licence)</li>
                    <li>Remplir et soumettre le formulaire en ligne</li>
                </ul>
                <div class="mt-6 flex space-x-4">
                    <a href="#" class="text-blue-600 hover:text-blue-800 transition duration-200"><i
                            class="fab fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-blue-600 hover:text-blue-800 transition duration-200"><i
                            class="fab fa-twitter text-lg"></i></a>
                    <a href="#" class="text-blue-600 hover:text-blue-800 transition duration-200"><i
                            class="fab fa-instagram text-lg"></i></a>
                    <a href="#" class="text-blue-600 hover:text-blue-800 transition duration-200"><i
                            class="fab fa-linkedin text-lg"></i></a>
                </div>
            </div>

            <!-- Colonne droite : Formulaire ou section de confirmation -->
            <div class="w-full md:w-1/2 p-6 bg-white shadow-md rounded-lg ml-0 md:ml-6 mt-6 md:mt-0">
                @if($showConfirmation)
                    <!-- Section de confirmation -->
                    <div class="flex justify-center mb-6">
                        <img class="h-20" src="{{ $submittedData['formation_name'] === 'IFPM' ? asset('images/logo_ifpm.png') : asset('images/logo.png') }}" alt="Logo">
                    </div>
                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Confirmation de votre préinscription</h2>
                    <p class="text-center text-gray-600 mb-6">
                        Votre dossier a été soumis avec succès et est en cours de traitement. Notre institut vous contactera dans les plus brefs délais.
                    </p>
                    <div class="text-center mb-6">
                        <strong class="text-lg font-semibold text-blue-600">Référence de votre dossier : {{ $reference }}</strong>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Type de formation :</strong> {{ $submittedData['formation_name'] }}</div>
                        @if($submittedData['cycle'])
                            <div><strong>Cycle :</strong> {{ $submittedData['cycle'] }}</div>
                        @endif
                        <div><strong>Filière :</strong> {{ $submittedData['filiere'] }}</div>
                        <div><strong>Spécialité :</strong> {{ $submittedData['specialite'] }}</div>
                        <div><strong>Nom :</strong> {{ $submittedData['name'] }}</div>
                        <div><strong>Prénom :</strong> {{ $submittedData['last_name'] }}</div>
                        <div><strong>Date de naissance :</strong> {{ $submittedData['birth'] }}</div>
                        <div><strong>Région :</strong> {{ $submittedData['region'] }}</div>
                        <div><strong>Département :</strong> {{ $submittedData['departement'] }}</div>
                        <div><strong>Arrondissement :</strong> {{ $submittedData['arrondissement'] }}</div>
                        <div><strong>Contact :</strong> {{ $submittedData['contact'] }}</div>
                        <div><strong>Email :</strong> {{ $submittedData['email'] }}</div>
                        <div><strong>Nom du père :</strong> {{ $submittedData['father'] }}</div>
                        <div><strong>Nom de la mère :</strong> {{ $submittedData['mother'] }}</div>
                        <div><strong>Numéro WhatsApp :</strong> {{ $submittedData['whatsapp_number'] }}</div> <!-- Nouveau champ -->
                        <div><strong>Personne à contacter (Nom) :</strong> {{ $submittedData['parrain_name'] }}</div> <!-- Nouveau champ -->
                        <div><strong>Personne à contacter (Contact) :</strong> {{ $submittedData['parrain_contact'] }}</div> <!-- Nouveau champ -->
                        <div><strong>Acte de naissance :</strong> <a href="{{ asset('storage/' . $submittedData['birth_certificate']) }}" target="_blank" class="text-blue-600 hover:underline">Voir</a></div>
                        <div><strong>Diplôme :</strong> <a href="{{ asset('storage/' . $submittedData['diploma']) }}" target="_blank" class="text-blue-600 hover:underline">Voir</a></div>
                    </div>
                    <div class="flex justify-center mt-8 space-x-4">
                        <button wire:click="editPreinscription"
                                class="text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            Modifier
                        </button>
                        <button wire:click="$set('showConfirmation', false)"
                                class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            Retour au formulaire
                        </button>
                    </div>
                @else
                    <!-- Formulaire d'inscription -->
                    <div class="flex justify-center mb-4">
                        <img id="logo" class="h-20" src="{{ $formation_name === 'IFPM' ? asset('images/logo_ifpm.png') : asset('images/logo.png') }}" alt="Logo">
                    </div>
                    <h2 class="text-center text-2xl font-bold mb-4 text-gray-800">Faites votre inscription en ligne</h2>

                    <form wire:submit="{{ $isEditing ? 'update' : 'store' }}" class="max-w-4xl mx-auto mt-5 relative" enctype="multipart/form-data">
                        @if (!empty($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                <strong>Erreurs :</strong>
                                <ul class="list-disc ml-5 mt-2">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ((array)$errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-4">
                            <label for="formation_name" class="block mb-2 text-sm font-medium text-gray-900">Type de formation
                                <b class="text-red-500">*</b></label>
                            <select wire:model.live="formation_name" id="formation_name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                required>
                                <option value="">Choisir une formation</option>
                                <option value="ISM" {{ $formation_name === 'ISM' ? 'selected' : '' }}>Formation Académique (ISM)</option>
                                <option value="IFPM" {{ $formation_name === 'IFPM' ? 'selected' : '' }}>Formation Professionnelle (IFPM)</option>
                            </select>
                        </div>

                        @if($formation_name === 'ISM')
                            <div class="mt-4">
                                <label for="cycle_id" class="block mb-2 text-sm font-medium text-gray-900">Cycle <b
                                        class="text-red-500">*</b></label>
                                <select id="cycle_id" wire:model.live="cycle_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required>
                                    <option value="">Choisir un cycle</option>
                                    @if(count($cycles) > 0)
                                        @foreach($cycles as $cycle)
                                            <option value="{{ $cycle->id }}" {{ $cycle_id == $cycle->id ? 'selected' : '' }}>{{ $cycle->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun cycle disponible, veuillez en créer un</option>
                                    @endif
                                </select>
                                @if(count($cycles) == 0 && $formation_name === 'ISM')
                                    <p class="mt-1 text-sm text-red-600">Veuillez créer un cycle pour ISM.</p>
                                @endif
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="filiere_id" class="block mb-2 text-sm font-medium text-gray-900">Filière <b
                                        class="text-red-500">*</b></label>
                                <select id="filiere_id" wire:model.live="filiere_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required>
                                    <option value="">Choisir une filière</option>
                                    @if(count($filieres) > 0)
                                        @foreach($filieres as $filiere)
                                            <option value="{{ $filiere->id }}" {{ $filiere_id == $filiere->id ? 'selected' : '' }}>{{ $filiere->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucune filière disponible</option>
                                    @endif
                                </select>
                                @if(count($filieres) == 0 && $formation_name)
                                    <p class="mt-1 text-sm text-red-600">Aucune filière disponible{{ $formation_name === 'ISM' && $cycle_id ? ' et ce cycle' : '' }}.</p>
                                @endif
                            </div>
                            <div>
                                <label for="specialite_id" class="block mb-2 text-sm font-medium text-gray-900">Spécialité <b
                                        class="text-red-500">*</b></label>
                                <select id="specialite_id" wire:model="specialite_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required>
                                    <option value="">Choisir une spécialité</option>
                                    @if(count($specialites) > 0)
                                        @foreach($specialites as $specialite)
                                            <option value="{{ $specialite->id }}" {{ $specialite_id == $specialite->id ? 'selected' : '' }}>{{ $specialite->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucune spécialité disponible</option>
                                    @endif
                                </select>
                                @if(count($specialites) == 0 && $filiere_id)
                                    <p class="mt-1 text-sm text-red-600">Aucune spécialité disponible</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Votre nom <b
                                        class="text-red-500">*</b></label>
                                <input type="text" id="name" wire:model="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Votre nom" value="{{ $name }}" required />
                            </div>
                            <div>
                                <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900">Votre prénom <b
                                        class="text-red-500">*</b></label>
                                <input type="text" id="last_name" wire:model="last_name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Votre prénom" value="{{ $last_name }}" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="birth" class="block mb-2 text-sm font-medium text-gray-900">Date de naissance <b
                                        class="text-red-500">*</b></label>
                                <input type="date" id="birth" wire:model="birth"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    value="{{ $birth }}" required />
                            </div>
                            <div>
                                <label for="region_id" class="block mb-2 text-sm font-medium text-gray-900">Région d'origine
                                    <b class="text-red-500">*</b></label>
                                <select id="region_id" wire:model.live="region_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required>
                                    <option value="">Choisir une région</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" {{ $region_id == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="departement_id" class="block mb-2 text-sm font-medium text-gray-900">Département
                                    <b class="text-red-500">*</b></label>
                                <select id="departement_id" wire:model.live="departement_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required>
                                    <option value="">Choisir un département</option>
                                    @if(count($departements) > 0)
                                        @foreach($departements as $departement)
                                            <option value="{{ $departement->id }}" {{ $departement_id == $departement->id ? 'selected' : '' }}>{{ $departement->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun département disponible</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="arrondissement_id" class="block mb-2 text-sm font-medium text-gray-900">Arrondissement</label>
                                <select id="arrondissement_id" wire:model="arrondissement_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Choisir un arrondissement (optionnel)</option>
                                    @if(count($arrondissements) > 0)
                                        @foreach($arrondissements as $arrondissement)
                                            <option value="{{ $arrondissement->id }}" {{ $arrondissement_id == $arrondissement->id ? 'selected' : '' }}>{{ $arrondissement->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>Aucun arrondissement disponible</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="contact" class="block mb-2 text-sm font-medium text-gray-900">Contact <b
                                        class="text-red-500">*</b></label>
                                <input type="text" id="contact" wire:model="contact"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Votre contact" value="{{ $contact }}" required />
                            </div>
                            <div>
                                <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email <b
                                        class="text-red-500">*</b></label>
                                <input type="email" id="email" wire:model="email"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Votre email" value="{{ $email }}" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <label for="father" class="block mb-2 text-sm font-medium text-gray-900">Nom du père <b
                                        class="text-red-500">*</b></label>
                                <input type="text" id="father" wire:model="father"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Nom du père" value="{{ $father }}" required />
                            </div>
                            <div>
                                <label for="mother" class="block mb-2 text-sm font-medium text-gray-900">Nom de la mère <b
                                        class="text-red-500">*</b></label>
                                <input type="text" id="mother" wire:model="mother"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Nom de la mère" value="{{ $mother }}" required />
                            </div>
                        </div>

                        <!-- Nouveaux champs -->
                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <label for="whatsapp_number" class="block mb-2 text-sm font-medium text-gray-900">Numéro WhatsApp <b class="text-red-500">*</b></label>
                                <input type="text" id="whatsapp_number" wire:model="whatsapp_number"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                       placeholder="Votre numéro WhatsApp" value="{{ $whatsapp_number }}" required />
                            </div>
                            <div>
                                <label for="parrain_name" class="block mb-2 text-sm font-medium text-gray-900">Personne à contacter (Nom) <b class="text-red-500">*</b></label>
                                <input type="text" id="parrain_name" wire:model="parrain_name"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                       placeholder="Nom de la personne à contacter" value="{{ $parrain_name }}" required />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 mt-4">
                            <div>
                                <label for="parrain_contact" class="block mb-2 text-sm font-medium text-gray-900">Personne à contacter (Contact) <b class="text-red-500">*</b></label>
                                <input type="text" id="parrain_contact" wire:model="parrain_contact"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                       placeholder="Contact de la personne" value="{{ $parrain_contact }}" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <label for="birth_certificate" class="block mb-2 text-sm font-medium text-gray-900">Copie de
                                    l'acte de naissance <b class="text-red-500">*</b></label>
                                <input type="file" id="birth_certificate" wire:model="birth_certificate"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    {{ !$isEditing ? 'required' : '' }} />
                                @if($isEditing && $preinscription && $preinscription->birth_certificate)
                                    <a href="{{ asset('storage/' . $preinscription->birth_certificate) }}" target="_blank" class="text-blue-600 hover:underline mt-2 block">Voir l'acte actuel</a>
                                @endif
                            </div>
                            <div>
                                <label for="diploma" class="block mb-2 text-sm font-medium text-gray-900">Copie du dernier
                                    diplôme <b class="text-red-500">*</b></label>
                                <input type="file" id="diploma" wire:model="diploma"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    {{ !$isEditing ? 'required' : '' }} />
                                @if($isEditing && $preinscription && $preinscription->diploma)
                                    <a href="{{ asset('storage/' . $preinscription->diploma) }}" target="_blank" class="text-blue-600 hover:underline mt-2 block">Voir le diplôme actuel</a>
                                @endif
                            </div>
                        </div>

                        <div class="flex justify-center mt-5 relative">
                            <button type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 focus:outline-none"
                                wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                {{ $isEditing ? 'Mettre à jour' : 'Valider' }}
                            </button>
                            <div wire:loading wire:target="{{ $isEditing ? 'update' : 'store' }}"
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white bg-opacity-90 p-6 rounded-lg shadow-lg flex flex-col items-center pointer-events-none z-10">
                                <svg class="animate-spin h-12 w-12 text-blue-700 mb-4" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-lg font-semibold text-gray-800">En cours...</span>
                            </div>
                        </div>
                    </form>
                @endif

                <!-- Notification -->
                @if ($showNotification)
                    <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
                        x-data="{ show: true }"
                        x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 5000)"
                        x-show="show">
                        <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-md border-l-4">
                            {{ $notificationMessage }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                console.log('Livewire et Alpine.js chargés');
            });
        </script>
    @endvolt
</x-layouts.app>
