<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Specialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

name('filiere.personnel');
middleware(['auth', 'verified']);

new class extends Component {
    public $personnel;
    public $specialites;
    public $filiere;
    public $hasFiliere = false;
    
    // Champs du formulaire
    public $name = '';
    public $lastname = '';
    public $email = '';
    public $contact = '';
    public $whatsapp = '';
    public $role = 'enseignant';
    public $specialite_id = null;
    public $poste = '';
    
    // Modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;
    
    public $selectedUser = null;
    public $generatedPassword = '';
    public $newUserPassword = ''; // Pour afficher le mot de passe du nouvel utilisateur
    
    // Notifications
    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';
    public array $formErrors = [];

    public function mount()
    {
        $user = auth()->user();
        
        if ($user->filiere_id && $user->filiere) {
            $this->hasFiliere = true;
            $this->filiere = $user->filiere;
            $this->loadData();
        }
    }

    public function loadData()
    {
        if ($this->filiere) {
            // Charger le personnel de la filière (enseignants uniquement selon votre demande)
            $this->personnel = User::with(['specialite'])
                ->where('filiere_id', $this->filiere->id)
                ->where('role', 'enseignant')
                ->where('status', '!=', 'failed')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Charger les spécialités de la filière
            $this->specialites = Specialite::where('filiere_id', $this->filiere->id)
                ->where('status', 'Success')
                ->get();
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->name = $this->selectedUser->name;
        $this->lastname = $this->selectedUser->lastname;
        $this->email = $this->selectedUser->email;
        $this->contact = $this->selectedUser->contact;
        $this->whatsapp = $this->selectedUser->whatsapp;
        $this->role = $this->selectedUser->role;
        $this->specialite_id = $this->selectedUser->specialite_id;
        $this->poste = $this->selectedUser->poste;
        $this->formErrors = [];
        $this->showEditModal = true;
    }

    public function openDetailsModal($id)
    {
        $this->selectedUser = User::with(['specialite', 'filiere'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailsModal = false;
        $this->selectedUser = null;
        $this->generatedPassword = '';
        $this->newUserPassword = '';
        $this->resetForm();
    }

    public function save()
    {
        $this->formErrors = [];
        
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'contact' => 'nullable|string|max:20',
                'whatsapp' => 'nullable|string|max:20',
                'specialite_id' => 'nullable|exists:specialites,id',
                'poste' => 'nullable|string|max:255',
            ]);

            // Générer un mot de passe aléatoire
            $password = Str::random(10);
            
            // Générer un matricule unique
            $matricule = 'ENS-' . strtoupper(Str::random(6));

            $user = User::create([
                'name' => $this->name,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'contact' => $this->contact,
                'whatsapp' => $this->whatsapp,
                'role' => 'enseignant',
                'specialite_id' => $this->specialite_id ?: null,
                'filiere_id' => $this->filiere->id,
                'poste' => $this->poste,
                'matricule' => $matricule,
                'password' => Hash::make($password),
                'status' => 'Success',
            ]);

            // Stocker le mot de passe généré pour l'affichage
            $this->newUserPassword = $password;
            $this->selectedUser = $user;
            
            $this->showCreateModal = false;
            $this->loadData();
            $this->resetForm();
            $this->showSuccessNotification('Enseignant ajouté avec succès ! Mot de passe : ' . $password);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la création : ' . $e->getMessage();
        }
    }

    public function update()
    {
        $this->formErrors = [];
        
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'lastname' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email,' . $this->selectedUser->id,
                'contact' => 'nullable|string|max:20',
                'whatsapp' => 'nullable|string|max:20',
                'specialite_id' => 'nullable|exists:specialites,id',
                'poste' => 'nullable|string|max:255',
            ]);

            $this->selectedUser->update([
                'name' => $this->name,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'contact' => $this->contact,
                'whatsapp' => $this->whatsapp,
                'specialite_id' => $this->specialite_id ?: null,
                'poste' => $this->poste,
            ]);

            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Enseignant mis à jour avec succès !');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la mise à jour';
        }
    }

    public function delete()
    {
        try {
            $this->selectedUser->update(['status' => 'failed']);
            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Enseignant supprimé avec succès !');
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la suppression';
        }
    }

    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);
            $password = Str::random(10);
            $user->update(['password' => Hash::make($password)]);
            
            $this->newUserPassword = $password;
            $this->selectedUser = $user;
            $this->showSuccessNotification('Nouveau mot de passe pour ' . $user->name . ' : ' . $password);
        } catch (\Exception $e) {
            $this->showErrorNotification('Erreur lors de la réinitialisation du mot de passe');
        }
    }

    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            $newStatus = $user->status === 'Success' ? 'pending' : 'Success';
            $user->update(['status' => $newStatus]);
            $this->loadData();
            $this->showSuccessNotification('Statut mis à jour avec succès !');
        } catch (\Exception $e) {
            $this->showErrorNotification('Erreur lors du changement de statut');
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->lastname = '';
        $this->email = '';
        $this->contact = '';
        $this->whatsapp = '';
        $this->role = 'enseignant';
        $this->specialite_id = null;
        $this->poste = '';
        $this->formErrors = [];
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'success';
        $this->showNotification = true;
    }

    private function showErrorNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'error';
        $this->showNotification = true;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            @if(!$hasFiliere)
                <!-- Message si aucune filière -->
                <div class="min-h-[60vh] flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                        <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune filière attribuée</h2>
                        <p class="text-gray-600 mb-6">Vous devez avoir une filière attribuée pour gérer le personnel.</p>
                        <a href="/filiere" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Retour
                        </a>
                    </div>
                </div>
            @else
                <!-- Header -->
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Gestion du Personnel</h1>
                        <p class="text-gray-500">{{ $filiere->name }} - Enseignants</p>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <a href="/filiere" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                            <div>
                                
                                
                            </div>
                        </div>
                        <button wire:click="openCreateModal" class="mt-4 md:mt-0 px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition duration-200 flex items-center shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Ajouter un enseignant
                        </button>
                    </div>
                </div>

                <!-- Alerte mot de passe généré -->
                @if($newUserPassword)
                    <div class="mb-6 p-4 bg-green-100 border border-green-400 rounded-xl">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-semibold text-green-800">Mot de passe généré</h4>
                                <p class="text-green-700 mt-1">
                                    Utilisateur : <strong>{{ $selectedUser?->email }}</strong><br>
                                    Mot de passe : <strong class="font-mono bg-green-200 px-2 py-1 rounded">{{ $newUserPassword }}</strong>
                                </p>
                                <p class="text-sm text-green-600 mt-2">⚠️ Notez ce mot de passe, il ne sera plus affiché.</p>
                            </div>
                            <button wire:click="$set('newUserPassword', '')" class="text-green-600 hover:text-green-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Statistiques rapides -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-blue-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100">Total Enseignants</p>
                                <p class="text-3xl font-bold">{{ $personnel->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Spécialités couvertes</p>
                                <p class="text-3xl font-bold">{{ $personnel->whereNotNull('specialite_id')->unique('specialite_id')->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste du Personnel -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Liste des Enseignants</h2>
                    </div>
                    
                    @if($personnel->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Enseignant</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Matricule</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Spécialité</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($personnel as $member)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                        @if($member->photo)
                                                            <img src="{{ asset('storage/' . $member->photo) }}" class="w-10 h-10 rounded-full object-cover">
                                                        @else
                                                            <span class="text-indigo-600 font-semibold">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800">{{ $member->name }} {{ $member->lastname }}</p>
                                                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $member->matricule ?? '-' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($member->specialite)
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $member->specialite->name }}</span>
                                                @else
                                                    <span class="text-gray-400 text-sm">Non affecté</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-sm text-gray-600">{{ $member->contact ?? '-' }}</p>
                                                @if($member->whatsapp)
                                                    <p class="text-xs text-green-600">WhatsApp: {{ $member->whatsapp }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($member->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Actif</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($member->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button wire:click="openDetailsModal({{ $member->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Détails">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openEditModal({{ $member->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="resetPassword({{ $member->id }})" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition" title="Réinitialiser mot de passe">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="toggleStatus({{ $member->id }})" class="p-2 {{ $member->status === 'Success' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition" title="{{ $member->status === 'Success' ? 'Désactiver' : 'Activer' }}">
                                                        @if($member->status === 'Success')
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                    <button wire:click="openDeleteModal({{ $member->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun enseignant</h3>
                            <p class="text-gray-500 mb-6">Commencez par ajouter des enseignants à votre filière.</p>
                            <button wire:click="openCreateModal" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Ajouter un enseignant
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Modal Créer -->
                @if($showCreateModal)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter un Enseignant</h2>
                            
                            @if(!empty($formErrors))
                                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                    <strong>Erreurs :</strong>
                                    <ul class="list-disc ml-5 mt-2">
                                        @foreach($formErrors as $field => $errors)
                                            @foreach((array)$errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form wire:submit="save">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Nom">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Prénom</label>
                                        <input type="text" wire:model="lastname" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Prénom">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                                        <input type="email" wire:model="email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="email@exemple.com">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Téléphone</label>
                                        <input type="text" wire:model="contact" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="6 99 00 00 00">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">WhatsApp</label>
                                        <input type="text" wire:model="whatsapp" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="6 99 00 00 00">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Spécialité <span class="text-gray-400">(optionnel)</span></label>
                                        <select wire:model="specialite_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Aucune spécialité --</option>
                                            @foreach($specialites as $specialite)
                                                <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Poste</label>
                                        <input type="text" wire:model="poste" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ex: Professeur, Vacataire...">
                                    </div>
                                </div>
                                
                                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-blue-700">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Un mot de passe sera généré automatiquement et affiché après la création.
                                    </p>
                                </div>
                                
                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Créer l'enseignant
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Modifier -->
                @if($showEditModal && $selectedUser)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier l'Enseignant</h2>
                            
                            @if(!empty($formErrors))
                                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                    <strong>Erreurs :</strong>
                                    <ul class="list-disc ml-5 mt-2">
                                        @foreach($formErrors as $field => $errors)
                                            @foreach((array)$errors as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <form wire:submit="update">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Prénom</label>
                                        <input type="text" wire:model="lastname" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                                        <input type="email" wire:model="email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Téléphone</label>
                                        <input type="text" wire:model="contact" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">WhatsApp</label>
                                        <input type="text" wire:model="whatsapp" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Spécialité</label>
                                        <select wire:model="specialite_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Aucune spécialité --</option>
                                            @foreach($specialites as $specialite)
                                                <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Poste</label>
                                        <input type="text" wire:model="poste" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                </div>
                                
                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Mettre à jour
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Détails -->
                @if($showDetailsModal && $selectedUser)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    @if($selectedUser->photo)
                                        <img src="{{ asset('storage/' . $selectedUser->photo) }}" class="w-20 h-20 rounded-full object-cover">
                                    @else
                                        <span class="text-3xl font-bold text-indigo-600">{{ strtoupper(substr($selectedUser->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <h2 class="text-2xl font-semibold text-gray-800">{{ $selectedUser->name }} {{ $selectedUser->lastname }}</h2>
                                <p class="text-gray-500">{{ ucfirst($selectedUser->role) }}</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Matricule</span>
                                    <span class="font-mono font-medium">{{ $selectedUser->matricule ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Email</span>
                                    <span class="font-medium">{{ $selectedUser->email }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Téléphone</span>
                                    <span class="font-medium">{{ $selectedUser->contact ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">WhatsApp</span>
                                    <span class="font-medium">{{ $selectedUser->whatsapp ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Filière</span>
                                    <span class="font-medium">{{ $selectedUser->filiere->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Spécialité</span>
                                    <span class="font-medium">{{ $selectedUser->specialite->name ?? 'Non affecté' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Poste</span>
                                    <span class="font-medium">{{ $selectedUser->poste ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-gray-500">Statut</span>
                                    @if($selectedUser->status === 'Success')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Actif</span>
                                    @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">{{ ucfirst($selectedUser->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-end">
                                <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                    Fermer
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Modal Supprimer -->
                @if($showDeleteModal && $selectedUser)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">Supprimer l'enseignant</h3>
                                <p class="text-gray-600 mb-6">Voulez-vous vraiment supprimer <strong>{{ $selectedUser->name }} {{ $selectedUser->lastname }}</strong> ? Cette action est irréversible.</p>
                                <div class="flex justify-center space-x-4">
                                    <button wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button wire:click="delete" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notification -->
                @if($showNotification)
                    <div class="fixed top-6 right-6 z-50 max-w-md w-full" 
                         x-data="{ show: true }" 
                         x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 5000)"
                         x-show="show"
                         x-transition>
                        <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-lg border-l-4">
                            <div class="flex items-start">
                                @if($notificationType === 'success')
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                                <span class="flex-1">{{ $notificationMessage }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    @endvolt
</x-layouts.app>