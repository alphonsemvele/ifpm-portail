<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Cycle;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Specialite;
use Illuminate\Support\Str;

name('admin.etudiants');
middleware(['auth', 'verified']);

new class extends Component {

    public $users      = [];
    public $cycles     = [];
    public $departements = [];
    public $filieres   = [];
    public $specialites = [];

    public bool $showAddModal        = false;
    public bool $showEditModal       = false;
    public bool $showActivateModal   = false;
    public bool $showDeactivateModal = false;
    public bool $showDetailsModal    = false;
    public bool $showDeleteModal     = false;

    public $userElement = null;

    public bool   $showNotification    = false;
    public string $notificationMessage = '';
    public string $notificationType    = '';
    public array  $formErrors          = [];

    // Champs formulaire
    public string $name       = '';
    public string $lastname   = '';
    public string $email      = '';
    public string $contact    = '';
    public string $whatsapp   = '';
    public string $father_name    = '';
    public string $father_contact = '';
    public string $mother_name    = '';
    public string $mother_contact = '';

    // Cascade académique
    public $cycle_id       = null;
    public $departement_id = null;
    public $filiere_id     = null;
    public $specialite_id  = null;

    // ─── Mount ─────────────────────────────────────────────────────────────────

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->users  = User::with(['filiere', 'specialite', 'cycle'])
                ->where('role', 'student')
                ->where('status', '!=', 'failed')
                ->get();

            $this->cycles       = Cycle::where('status', 'Success')->get();
            $this->departements = collect();
            $this->filieres     = collect();
            $this->specialites  = collect();
        } catch (\Exception $e) {
            logger('Erreur loadData apprenants: ' . $e->getMessage());
            $this->users = collect();
        }
    }

    // ─── Cascades ──────────────────────────────────────────────────────────────

    public function updatedCycleId($value)
    {
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->departements   = collect();
        $this->filieres       = collect();
        $this->specialites    = collect();

        if ($value) {
            $this->departements = Departement::where('cycle_id', $value)->get();
        }
    }

    public function updatedDepartementId($value)
    {
        $this->filiere_id    = null;
        $this->specialite_id = null;
        $this->filieres      = collect();
        $this->specialites   = collect();

        if ($value) {
            // Pas de filtre status pour ne pas bloquer l'affichage
            $this->filieres = Filiere::where('departement_id', $value)->get();
        }
    }

    public function updatedFiliereId($value)
    {
        $this->specialite_id = null;
        $this->specialites   = collect();

        if ($value) {
            $this->specialites = Specialite::where('filiere_id', $value)->where('status', 'Success')->get();
        }
    }

    // ─── Modals ────────────────────────────────────────────────────────────────

    public function functionShowAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function functionShowEditModal($id)
    {
        try {
            $this->userElement = User::with(['filiere', 'specialite', 'cycle'])->findOrFail($id);

            $this->name           = $this->userElement->name ?? '';
            $this->lastname       = $this->userElement->lastname ?? '';
            $this->email          = $this->userElement->email ?? '';
            $this->contact        = $this->userElement->contact ?? '';
            $this->whatsapp       = $this->userElement->whatsapp ?? '';
            $this->father_name    = $this->userElement->father_name ?? '';
            $this->father_contact = $this->userElement->father_contact ?? '';
            $this->mother_name    = $this->userElement->mother_name ?? '';
            $this->mother_contact = $this->userElement->mother_contact ?? '';
            $this->cycle_id       = $this->userElement->cycle_id;
            $this->departement_id = $this->userElement->departement_id;
            $this->filiere_id     = $this->userElement->filiere_id;
            $this->specialite_id  = $this->userElement->specialite_id;

            // Charger toutes les listes pour les selects (sans filtre status sur departements/filieres)
            $this->departements = $this->cycle_id
                ? Departement::where('cycle_id', $this->cycle_id)->get()
                : collect();

            $this->filieres = $this->departement_id
                ? Filiere::where('departement_id', $this->departement_id)->get()
                : collect();

            $this->specialites = $this->filiere_id
                ? Specialite::where('filiere_id', $this->filiere_id)->where('status', 'Success')->get()
                : collect();

            $this->showEditModal = true;
            $this->formErrors    = [];
        } catch (\Exception $e) {
            logger('Erreur openEditModal apprenant: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function functionShowActivateModal($id)
    {
        $this->userElement      = User::findOrFail($id);
        $this->showActivateModal = true;
    }

    public function functionShowDeactivateModal($id)
    {
        $this->userElement        = User::findOrFail($id);
        $this->showDeactivateModal = true;
    }

    public function functionShowDetailsModal($id)
    {
        $this->userElement      = User::with(['filiere', 'specialite', 'cycle'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function functionShowDeleteModal($id)
    {
        $this->userElement    = User::findOrFail($id);
        $this->showDeleteModal = true;
    }

    // ─── CRUD ──────────────────────────────────────────────────────────────────

    public function save()
    {
        try {
            $this->validate([
                'name'           => 'required|string|max:255',
                'lastname'       => 'required|string|max:255',
                'email'          => 'required|email|unique:users,email',
                'contact'        => 'required|string|max:20',
                'whatsapp'       => 'nullable|string|max:20',
                'cycle_id'       => 'required|exists:cycles,id',
                'departement_id' => 'required|exists:departements,id',
                'filiere_id'     => 'required|exists:filieres,id',
                'specialite_id'  => 'nullable|exists:specialites,id',
                'father_name'    => 'nullable|string|max:255',
                'father_contact' => 'nullable|string|max:20',
                'mother_name'    => 'nullable|string|max:255',
                'mother_contact' => 'nullable|string|max:20',
            ]);

            $matricule = 'ETU-' . strtoupper(Str::random(6));

            User::create([
                'name'           => $this->name,
                'lastname'       => $this->lastname,
                'email'          => $this->email,
                'contact'        => $this->contact,
                'whatsapp'       => $this->whatsapp,
                'matricule'      => $matricule,
                'cycle_id'       => $this->cycle_id,
                'departement_id' => $this->departement_id,
                'filiere_id'     => $this->filiere_id,
                'specialite_id'  => $this->specialite_id ?: null,
                'father_name'    => $this->father_name,
                'father_contact' => $this->father_contact,
                'mother_name'    => $this->mother_name,
                'mother_contact' => $this->mother_contact,
                'role'           => 'student',
                'status'         => 'pending',
                'password'       => bcrypt('password'),
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showSuccessNotification('Apprenant ajouté avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur save apprenant: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la sauvegarde');
        }
    }

    public function update()
    {
        try {
            $this->validate([
                'name'           => 'required|string|max:255',
                'lastname'       => 'required|string|max:255',
                'email'          => 'required|email|unique:users,email,' . $this->userElement->id,
                'contact'        => 'required|string|max:20',
                'whatsapp'       => 'nullable|string|max:20',
                'cycle_id'       => 'required|exists:cycles,id',
                'departement_id' => 'required|exists:departements,id',
                'filiere_id'     => 'required|exists:filieres,id',
                'specialite_id'  => 'nullable|exists:specialites,id',
                'father_name'    => 'nullable|string|max:255',
                'father_contact' => 'nullable|string|max:20',
                'mother_name'    => 'nullable|string|max:255',
                'mother_contact' => 'nullable|string|max:20',
            ]);

            $this->userElement->update([
                'name'           => $this->name,
                'lastname'       => $this->lastname,
                'email'          => $this->email,
                'contact'        => $this->contact,
                'whatsapp'       => $this->whatsapp,
                'cycle_id'       => $this->cycle_id,
                'departement_id' => $this->departement_id,
                'filiere_id'     => $this->filiere_id,
                'specialite_id'  => $this->specialite_id ?: null,
                'father_name'    => $this->father_name,
                'father_contact' => $this->father_contact,
                'mother_name'    => $this->mother_name,
                'mother_contact' => $this->mother_contact,
            ]);

            $this->resetForm();
            $this->showEditModal = false;
            $this->userElement   = null;
            $this->loadData();
            $this->showSuccessNotification('Apprenant mis à jour avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur update apprenant: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la mise à jour');
        }
    }

    public function activateUser($id)
    {
        try {
            User::findOrFail($id)->update(['status' => 'Success']);
            $this->showActivateModal = false;
            $this->loadData();
            $this->showSuccessNotification('Apprenant activé avec succès !');
        } catch (\Exception $e) {
            $this->addError('general', 'Erreur lors de l\'activation');
        }
    }

    public function deactivateUser($id)
    {
        try {
            User::findOrFail($id)->update(['status' => 'pending']);
            $this->showDeactivateModal = false;
            $this->loadData();
            $this->showSuccessNotification('Apprenant désactivé avec succès !');
        } catch (\Exception $e) {
            $this->addError('general', 'Erreur lors de la désactivation');
        }
    }

    public function deleteUser($id)
    {
        try {
            User::findOrFail($id)->update(['status' => 'failed']);
            $this->showDeleteModal = false;
            $this->loadData();
            $this->showSuccessNotification('Apprenant supprimé avec succès !');
        } catch (\Exception $e) {
            $this->addError('general', 'Erreur lors de la suppression');
        }
    }

    public function closeModal()
    {
        $this->showAddModal        = false;
        $this->showEditModal       = false;
        $this->showActivateModal   = false;
        $this->showDeactivateModal = false;
        $this->showDetailsModal    = false;
        $this->showDeleteModal     = false;
        $this->userElement         = null;
        $this->resetForm();
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function resetForm()
    {
        $this->name           = '';
        $this->lastname       = '';
        $this->email          = '';
        $this->contact        = '';
        $this->whatsapp       = '';
        $this->father_name    = '';
        $this->father_contact = '';
        $this->mother_name    = '';
        $this->mother_contact = '';
        $this->cycle_id       = null;
        $this->departement_id = null;
        $this->filiere_id     = null;
        $this->specialite_id  = null;
        $this->departements   = collect();
        $this->filieres       = collect();
        $this->specialites    = collect();
        $this->formErrors     = [];
    }

    private function showSuccessNotification(string $message)
    {
        $this->notificationMessage = $message;
        $this->notificationType    = 'success';
        $this->showNotification    = true;
    }
};
?>

<x-layouts.app header="true">
    @volt
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Gestion des Apprenants</h1>
                <p class="text-gray-500">Gérez les apprenants, leurs filières et spécialités</p>
            </div>
            <button wire:click="functionShowAddModal"
                class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg">
                + Ajouter un apprenant
            </button>
        </div>

        <!-- Erreurs générales -->
        @error('general')
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">{{ $message }}</div>
        @enderror

        <!-- ─── Liste ─── -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Apprenants</h2>

            @if(count($users) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Matricule</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Cycle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr class="bg-gray-50 hover:bg-gray-100 transition duration-200">
                                    <td class="p-4">{{ $user->name }} {{ $user->lastname }}</td>
                                    <td class="p-4">
                                        <span class="font-mono text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded">
                                            {{ $user->matricule ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="p-4">{{ $user->cycle->name ?? '—' }}</td>
                                    <td class="p-4">{{ $user->filiere->name ?? '—' }}</td>
                                    <td class="p-4">{{ $user->specialite->name ?? '—' }}</td>
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                            {{ $user->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $user->status === 'Success' ? 'Actif' : 'En attente' }}
                                        </span>
                                    </td>
                                    <td class="p-4 flex flex-wrap gap-2">
                                        <button wire:click="functionShowDetailsModal({{ $user->id }})" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                        @if ($user->status === 'pending')
                                            <button wire:click="functionShowActivateModal({{ $user->id }})" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                        @else
                                            <button wire:click="functionShowDeactivateModal({{ $user->id }})" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                        @endif
                                        <button wire:click="functionShowEditModal({{ $user->id }})" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                        <button wire:click="functionShowDeleteModal({{ $user->id }})" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500">Aucun apprenant trouvé.</p>
            @endif
        </div>

        <!-- ─── Modal Ajouter ─── -->
        @if ($showAddModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Ajouter un apprenant
                    </h2>

                    @if (!empty($formErrors))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                            <strong>Erreurs :</strong>
                            <ul class="list-disc ml-5 mt-2">
                                @foreach ($formErrors as $errors)
                                    @foreach ((array)$errors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit="save">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" wire:model="name" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input type="text" wire:model="lastname" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="email" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input type="text" wire:model="contact" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                <input type="text" wire:model="whatsapp"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cycle</label>
                                <select wire:model.live="cycle_id" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                    <option value="">— Choisir un cycle —</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Département</label>
                                <select wire:model.live="departement_id" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500
                                           {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    @disabled(!$cycle_id)>
                                    <option value="">— Choisir un département —</option>
                                    @foreach ($departements as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filière</label>
                                <select wire:model.live="filiere_id" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500
                                           {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    @disabled(!$departement_id)>
                                    <option value="">— Choisir une filière —</option>
                                    @foreach ($filieres as $filiere)
                                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Spécialité</label>
                                <select wire:model="specialite_id"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500
                                           {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    @disabled(!$filiere_id)>
                                    <option value="">— Optionnel —</option>
                                    @foreach ($specialites as $specialite)
                                        <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom du père</label>
                                <input type="text" wire:model="father_name"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact du père</label>
                                <input type="text" wire:model="father_contact"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom de la mère</label>
                                <input type="text" wire:model="mother_name"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact de la mère</label>
                                <input type="text" wire:model="mother_contact"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <button type="submit"
                                class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition shadow-md">
                                Enregistrer
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition shadow-md">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ─── Modal Modifier ─── -->
        @if ($showEditModal && $userElement)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl flex flex-col">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Modifier l'apprenant
                    </h2>

                    @if (!empty($formErrors))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                            <strong>Erreurs :</strong>
                            <ul class="list-disc ml-5 mt-2">
                                @foreach ($formErrors as $errors)
                                    @foreach ((array)$errors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit="update" class="flex flex-col flex-1 min-h-0">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" wire:model="name" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                <input type="text" wire:model="lastname" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="email" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input type="text" wire:model="contact" required
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">WhatsApp</label>
                                <input type="text" wire:model="whatsapp"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cycle</label>
                                <select wire:model.live="cycle_id" required
                                    wire:key="edit-cycle"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                    <option value="">— Choisir un cycle —</option>
                                    @foreach ($cycles as $cycle)
                                        <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Département</label>
                                <select wire:model.live="departement_id" required
                                    wire:key="edit-dept-{{ $cycle_id }}"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500
                                           {{ !$cycle_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    @disabled(!$cycle_id)>
                                    <option value="">— Choisir un département —</option>
                                    @foreach ($departements as $dep)
                                        <option value="{{ $dep->id }}">{{ $dep->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Filière</label>
                                <select wire:model.live="filiere_id" required
                                    wire:key="edit-filiere-{{ $departement_id }}"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500
                                           {{ !$departement_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    @disabled(!$departement_id)>
                                    <option value="">— Choisir une filière —</option>
                                    @foreach ($filieres as $filiere)
                                        <option value="{{ $filiere->id }}">{{ $filiere->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Spécialité</label>
                                <select wire:model="specialite_id"
                                    wire:key="edit-specialite-{{ $filiere_id }}"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500
                                           {{ !$filiere_id ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    @disabled(!$filiere_id)>
                                    <option value="">— Optionnel —</option>
                                    @foreach ($specialites as $specialite)
                                        <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Infos parents --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom du père</label>
                                <input type="text" wire:model="father_name"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact du père</label>
                                <input type="text" wire:model="father_contact"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom de la mère</label>
                                <input type="text" wire:model="mother_name"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Contact de la mère</label>
                                <input type="text" wire:model="mother_contact"
                                    class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                            </div>

                        </div>{{-- end grid --}}

                        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end space-x-4 sticky bottom-0 bg-white">
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-200 text-gray-700 py-3 px-6 rounded-xl hover:bg-gray-300 transition shadow-sm">
                                Annuler
                            </button>
                            <button type="submit"
                                class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition shadow-md">
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- ─── Modal Activer ─── -->
        @if ($showActivateModal && $userElement)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer l'apprenant</h3>
                    <p class="mb-6 text-gray-600">Voulez-vous activer <strong>{{ $userElement->name }} {{ $userElement->lastname }}</strong> ?</p>
                    <div class="flex justify-end space-x-4">
                        <button wire:click="activateUser({{ $userElement->id }})"
                            class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700">Oui</button>
                        <button wire:click="closeModal"
                            class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">Annuler</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ─── Modal Désactiver ─── -->
        @if ($showDeactivateModal && $userElement)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver l'apprenant</h3>
                    <p class="mb-6 text-gray-600">Voulez-vous désactiver <strong>{{ $userElement->name }} {{ $userElement->lastname }}</strong> ?</p>
                    <div class="flex justify-end space-x-4">
                        <button wire:click="deactivateUser({{ $userElement->id }})"
                            class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700">Oui</button>
                        <button wire:click="closeModal"
                            class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">Annuler</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ─── Modal Supprimer ─── -->
        @if ($showDeleteModal && $userElement)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer l'apprenant</h3>
                    <p class="mb-6 text-gray-600">Voulez-vous supprimer <strong>{{ $userElement->name }} {{ $userElement->lastname }}</strong> ? Cette action est irréversible.</p>
                    <div class="flex justify-end space-x-4">
                        <button wire:click="deleteUser({{ $userElement->id }})"
                            class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700">Oui</button>
                        <button wire:click="closeModal"
                            class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">Annuler</button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ─── Modal Détails ─── -->
        @if ($showDetailsModal && $userElement)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Détails de l'apprenant</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <p class="text-gray-500">Nom complet</p>
                        <p class="font-medium">{{ $userElement->name }} {{ $userElement->lastname }}</p>
                        <p class="text-gray-500">Matricule</p>
                        <p class="font-mono font-medium">{{ $userElement->matricule ?? '—' }}</p>
                        <p class="text-gray-500">Email</p>
                        <p class="font-medium">{{ $userElement->email ?? '—' }}</p>
                        <p class="text-gray-500">Téléphone</p>
                        <p class="font-medium">{{ $userElement->contact ?? '—' }}</p>
                        <p class="text-gray-500">WhatsApp</p>
                        <p class="font-medium">{{ $userElement->whatsapp ?? '—' }}</p>
                        <p class="text-gray-500">Cycle</p>
                        <p class="font-medium">{{ $userElement->cycle->name ?? '—' }}</p>
                        <p class="text-gray-500">Filière</p>
                        <p class="font-medium">{{ $userElement->filiere->name ?? '—' }}</p>
                        <p class="text-gray-500">Spécialité</p>
                        <p class="font-medium">{{ $userElement->specialite->name ?? '—' }}</p>
                        <p class="text-gray-500">Nom du père</p>
                        <p class="font-medium">{{ $userElement->father_name ?? '—' }}</p>
                        <p class="text-gray-500">Contact père</p>
                        <p class="font-medium">{{ $userElement->father_contact ?? '—' }}</p>
                        <p class="text-gray-500">Nom de la mère</p>
                        <p class="font-medium">{{ $userElement->mother_name ?? '—' }}</p>
                        <p class="text-gray-500">Contact mère</p>
                        <p class="font-medium">{{ $userElement->mother_contact ?? '—' }}</p>
                        <p class="text-gray-500">Statut</p>
                        <p>
                            <span class="inline-block px-2 py-1 text-xs rounded-full
                                {{ $userElement->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $userElement->status === 'Success' ? 'Actif' : 'En attente' }}
                            </span>
                        </p>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeModal"
                            class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ─── Toast notification ─── -->
        @if ($showNotification)
            <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
                x-data="{ show: true }"
                x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 3500)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="flex items-center gap-3 p-4 rounded-xl shadow-lg border-l-4
                    {{ $notificationType === 'success'
                        ? 'bg-green-50 text-green-700 border-green-500'
                        : 'bg-red-50 text-red-700 border-red-500' }}">
                    @if ($notificationType === 'success')
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                    <span class="text-sm font-medium">{{ $notificationMessage }}</span>
                </div>
            </div>
        @endif

    </div>
    @endvolt
</x-layouts.app>