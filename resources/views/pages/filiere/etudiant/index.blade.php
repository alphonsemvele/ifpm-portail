<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Specialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

name('specialites.etudiants');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $filiere;
    public $hasFiliere = false;

    // Formulaire création/édition apprenant
    public $name = '';
    public $lastname = '';
    public $email = '';
    public $contact = '';
    public $whatsapp = '';
    public $specialite_id = null;

    // Modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;

    public $selectedStudent = null;
    public $newUserPassword = '';

    // Filtres
    public $selectedSpecialiteFilter = 'all';

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
        }
    }

    public function getEtudiantsProperty()
    {
        if (!$this->hasFiliere || !$this->filiere) {
            return collect()->paginate(15);
        }

        $query = User::with(['specialite'])
            ->where('filiere_id', $this->filiere->id)
            ->where('role', 'student')
            ->where('status', '!=', 'failed')
            ->orderBy('created_at', 'desc');

        if ($this->selectedSpecialiteFilter !== 'all') {
            $query->where('specialite_id', $this->selectedSpecialiteFilter);
        }

        return $query->paginate(15);
    }

    public function getSpecialitesProperty()
    {
        if (!$this->filiere) return collect();

        return Specialite::where('filiere_id', $this->filiere->id)
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->selectedStudent = User::findOrFail($id);
        $this->name = $this->selectedStudent->name;
        $this->lastname = $this->selectedStudent->lastname;
        $this->email = $this->selectedStudent->email;
        $this->contact = $this->selectedStudent->contact;
        $this->whatsapp = $this->selectedStudent->whatsapp;
        $this->specialite_id = $this->selectedStudent->specialite_id;
        $this->formErrors = [];
        $this->showEditModal = true;
    }

    public function openDetailsModal($id)
    {
        $this->selectedStudent = User::with(['specialite', 'filiere'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->selectedStudent = User::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = $this->showEditModal = $this->showDeleteModal = $this->showDetailsModal = false;
        $this->selectedStudent = null;
        $this->newUserPassword = '';
        $this->resetForm();
    }

    private function generateUniqueMatricule(): string
    {
        do {
            $matricule = 'ETU-' . strtoupper(Str::random(6));
        } while (User::where('matricule', $matricule)->exists());

        return $matricule;
    }

    public function save()
    {
        $this->formErrors = [];

        $this->validate([
            'name'          => 'required|string|max:255',
            'lastname'      => 'nullable|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'contact'       => 'nullable|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'specialite_id' => 'nullable|exists:specialites,id',
        ]);

        try {
            $password = Str::random(10);
            $matricule = $this->generateUniqueMatricule();

            $student = User::create([
                'name'          => $this->name,
                'lastname'      => $this->lastname,
                'email'         => $this->email,
                'contact'       => $this->contact,
                'whatsapp'      => $this->whatsapp,
                'role'          => 'student',
                'specialite_id' => $this->specialite_id ?: null,
                'filiere_id'    => $this->filiere->id,
                'matricule'     => $matricule,
                'password'      => Hash::make($password),
                'status'        => 'Success',
            ]);

            $this->newUserPassword = $password;
            $this->selectedStudent = $student;

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification("Apprenant ajouté ! Matricule : {$matricule} – Mot de passe temporaire : {$password}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la création : ' . $e->getMessage();
        }
    }

    public function update()
    {
        $this->formErrors = [];

        $this->validate([
            'name'          => 'required|string|max:255',
            'lastname'      => 'nullable|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $this->selectedStudent->id,
            'contact'       => 'nullable|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'specialite_id' => 'nullable|exists:specialites,id',
        ]);

        try {
            $this->selectedStudent->update([
                'name'          => $this->name,
                'lastname'      => $this->lastname,
                'email'         => $this->email,
                'contact'       => $this->contact,
                'whatsapp'      => $this->whatsapp,
                'specialite_id' => $this->specialite_id ?: null,
            ]);

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Informations de l’apprenant mises à jour avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la mise à jour';
        }
    }

    public function delete()
    {
        try {
            $this->selectedStudent->update(['status' => 'failed']);
            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Apprenant supprimé (désactivé) avec succès !');
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la suppression';
        }
    }

    public function resetPassword($id)
    {
        try {
            $student = User::findOrFail($id);
            $password = Str::random(10);
            $student->update(['password' => Hash::make($password)]);

            $this->newUserPassword = $password;
            $this->selectedStudent = $student;
            $this->showSuccessNotification("Nouveau mot de passe pour {$student->name} : {$password}");
        } catch (\Exception $e) {
            $this->showErrorNotification('Erreur lors de la réinitialisation du mot de passe');
        }
    }

    private function resetForm()
    {
        $this->reset([
            'name', 'lastname', 'email', 'contact', 'whatsapp',
            'specialite_id', 'formErrors'
        ]);
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
                <div class="min-h-[60vh] flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                        <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune filière attribuée</h2>
                        <p class="text-gray-600 mb-6">Vous devez avoir une filière pour gérer les apprenants.</p>
                        <a href="/filiere" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Retour aux filières
                        </a>
                    </div>
                </div>
            @else

                <!-- Header -->
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Gestion des Apprenants</h1>
                        <p class="text-gray-500">{{ $filiere->name }}</p>
                    </div>
                    <div>
                        
                        
                    </div>
                    <button wire:click="openCreateModal" class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition flex items-center shadow-md">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter un apprenant
                    </button>
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
                                    Apprenant : <strong>{{ $selectedStudent?->email }}</strong><br>
                                    Matricule : <strong>{{ $selectedStudent?->matricule }}</strong><br>
                                    Mot de passe : <strong class="font-mono bg-green-200 px-2 py-1 rounded">{{ $newUserPassword }}</strong>
                                </p>
                                <p class="text-sm text-green-600 mt-2">⚠️ Notez ces informations, elles ne seront plus affichées.</p>
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-indigo-50 rounded-xl shadow p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Apprenants inscrits</h3>
                        <p class="text-4xl font-bold text-indigo-600">{{ $this->etudiants->total() }}</p>
                        <p class="text-sm text-gray-500">dans la filière</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">Spécialités</h3>
                        <p class="text-4xl font-bold text-indigo-600">{{ $this->specialites->count() }}</p>
                        <p class="text-sm text-gray-500">représentées</p>
                    </div>
                    <div class="bg-indigo-50 rounded-xl shadow p-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">En attente</h3>
                        <p class="text-4xl font-bold text-orange-600">
                            {{ User::where('filiere_id', $filiere->id)->where('role', 'student')->where('status', 'pending')->count() }}
                        </p>
                        <p class="text-sm text-gray-500">apprenants</p>
                    </div>
                </div>

                <!-- Filtre -->
                <div class="bg-white rounded-xl shadow p-6 mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par spécialité</label>
                            <select wire:model.live="selectedSpecialiteFilter" class="w-full md:w-80 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all">Toutes les spécialités</option>
                                @foreach($this->specialites as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Liste -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Liste des apprenants</h2>
                    </div>

                    @if($this->etudiants->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Apprenant</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Matricule</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Spécialité</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->etudiants as $student)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                                        @if($student->photo)
                                                            <img src="{{ asset('storage/' . $student->photo) }}" class="w-10 h-10 rounded-full object-cover">
                                                        @else
                                                            <span class="text-indigo-600 font-semibold">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800">{{ $student->name }} {{ $student->lastname }}</p>
                                                        <p class="text-sm text-gray-500">{{ $student->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $student->matricule ?? '-' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($student->specialite)
                                                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm">{{ $student->specialite->name }}</span>
                                                @else
                                                    <span class="text-gray-400 text-sm">Non affecté</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-sm text-gray-600">{{ $student->contact ?? '-' }}</p>
                                                @if($student->whatsapp)
                                                    <p class="text-xs text-green-600">WhatsApp: {{ $student->whatsapp }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($student->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Actif</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($student->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button wire:click="openDetailsModal({{ $student->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Détails">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openEditModal({{ $student->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="resetPassword({{ $student->id }})" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition" title="Réinitialiser mot de passe">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openDeleteModal({{ $student->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
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

                        <div class="px-6 py-4 border-t border-gray-200 flex justify-center">
                            {{ $this->etudiants->links() }}
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun apprenant inscrit</h3>
                            <p class="text-gray-500 mb-6">Ajoutez vos premiers apprenants à la filière.</p>
                            <button wire:click="openCreateModal" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Ajouter un apprenant
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Modal Créer -->
                @if($showCreateModal)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter un Apprenant</h2>

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
                                        <input type="email" wire:model="email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="etu@example.com">
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
                                            <option value="">-- Non affecté --</option>
                                            @foreach($this->specialites as $specialite)
                                                <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-6 p-4 bg-blue-50 rounded-lg text-sm text-blue-700">
                                    Un matricule et un mot de passe temporaire seront générés automatiquement et affichés après création.
                                </div>

                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Créer l'apprenant
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Modifier (similaire au créer) -->
                @if($showEditModal && $selectedStudent)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier l’Apprenant</h2>

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
                                            <option value="">-- Non affecté --</option>
                                            @foreach($this->specialites as $specialite)
                                                <option value="{{ $specialite->id }}">{{ $specialite->name }}</option>
                                            @endforeach
                                        </select>
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
                @if($showDetailsModal && $selectedStudent)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    @if($selectedStudent->photo)
                                        <img src="{{ asset('storage/' . $selectedStudent->photo) }}" class="w-20 h-20 rounded-full object-cover">
                                    @else
                                        <span class="text-3xl font-bold text-indigo-600">{{ strtoupper(substr($selectedStudent->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <h2 class="text-2xl font-semibold text-gray-800">{{ $selectedStudent->name }} {{ $selectedStudent->lastname }}</h2>
                                <p class="text-gray-500">Apprenant</p>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Matricule</span>
                                    <span class="font-mono font-medium">{{ $selectedStudent->matricule ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Email</span>
                                    <span class="font-medium">{{ $selectedStudent->email }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Téléphone</span>
                                    <span class="font-medium">{{ $selectedStudent->contact ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">WhatsApp</span>
                                    <span class="font-medium">{{ $selectedStudent->whatsapp ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Filière</span>
                                    <span class="font-medium">{{ $selectedStudent->filiere->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Spécialité</span>
                                    <span class="font-medium">{{ $selectedStudent->specialite->name ?? 'Non affecté' }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-gray-500">Statut</span>
                                    @if($selectedStudent->status === 'Success')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Actif</span>
                                    @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">{{ ucfirst($selectedStudent->status) }}</span>
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
                @if($showDeleteModal && $selectedStudent)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">Supprimer l’apprenant</h3>
                                <p class="text-gray-600 mb-6">
                                    Voulez-vous vraiment supprimer <strong>{{ $selectedStudent->name }} {{ $selectedStudent->lastname }}</strong> ?
                                </p>
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