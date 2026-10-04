<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Cour;
use App\Models\Specialite;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Support\Str;

name('filiere.cours.index');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $filiere;
    public $hasFiliere = false;

    // Formulaire Cours (sans code ni hour_number)
    public $name = '';
    public $specialite_id = null;
    public $ue_id = null;
    public $responsable_id = null;
    public $credit_cc = 0;
    public $credit_exam = 0;

    // Modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;

    public $selectedCours = null;

    // Filtres
    public $filterSpecialite = 'all';
    public $filterUe = 'all';

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

    public function getCoursProperty()
    {
        if (!$this->hasFiliere || !$this->filiere) {
            return collect()->paginate(15);
        }

        $query = Cour::with(['specialite', 'ue', 'responsable'])
            ->where('filiere_id', $this->filiere->id)
            ->where('status', '!=', 'failed')
            ->orderBy('name');

        if ($this->filterSpecialite !== 'all') {
            $query->where('specialite_id', $this->filterSpecialite);
        }

        if ($this->filterUe !== 'all') {
            $query->where('ue_id', $this->filterUe);
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

    public function getUesProperty()
    {
        if (!$this->filiere) return collect();

        return Ue::where('filiere_id', $this->filiere->id)
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();
    }

    public function getEnseignantsProperty()
    {
        if (!$this->filiere) return collect();

        return User::where('filiere_id', $this->filiere->id)
            ->where('role', 'enseignant')
            ->where('status', 'Success')
            ->orderBy('name')
            ->get();
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'COURS-' . strtoupper(Str::random(6));
        } while (Cour::where('code', $code)->where('filiere_id', $this->filiere->id)->exists());

        return $code;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->selectedCours = Cour::findOrFail($id);
        $this->name = $this->selectedCours->name;
        $this->specialite_id = $this->selectedCours->specialite_id;
        $this->ue_id = $this->selectedCours->ue_id;
        $this->responsable_id = $this->selectedCours->responsable_id;
        $this->credit_cc = $this->selectedCours->credit_cc ?? 0;
        $this->credit_exam = $this->selectedCours->credit_exam ?? 0;
        $this->formErrors = [];
        $this->showEditModal = true;
    }

    public function openDetailsModal($id)
    {
        $this->selectedCours = Cour::with(['specialite', 'ue', 'responsable', 'filiere'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->selectedCours = Cour::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailsModal = false;
        $this->selectedCours = null;
        $this->resetForm();
    }

    public function save()
    {
        $this->formErrors = [];

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'specialite_id' => 'nullable|exists:specialites,id',
                'ue_id' => 'nullable|exists:ues,id',
                'responsable_id' => 'nullable|exists:users,id',
                'credit_cc' => 'nullable|integer|min:0',
                'credit_exam' => 'nullable|integer|min:0',
            ]);

            $code = $this->generateUniqueCode();

            Cour::create([
                'name' => $this->name,
                'code' => $code,
                'specialite_id' => $this->specialite_id ?: null,
                'ue_id' => $this->ue_id ?: null,
                'responsable_id' => $this->responsable_id ?: null,
                'filiere_id' => $this->filiere->id,
                'credit_cc' => $this->credit_cc,
                'credit_exam' => $this->credit_exam,
                'status' => 'Success',
            ]);

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification("Cours créé avec succès ! Code généré : {$code}");
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
                'specialite_id' => 'nullable|exists:specialites,id',
                'ue_id' => 'nullable|exists:ues,id',
                'responsable_id' => 'nullable|exists:users,id',
                'credit_cc' => 'nullable|integer|min:0',
                'credit_exam' => 'nullable|integer|min:0',
            ]);

            $this->selectedCours->update([
                'name' => $this->name,
                'specialite_id' => $this->specialite_id ?: null,
                'ue_id' => $this->ue_id ?: null,
                'responsable_id' => $this->responsable_id ?: null,
                'credit_cc' => $this->credit_cc,
                'credit_exam' => $this->credit_exam,
            ]);

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Cours modifié avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la mise à jour';
        }
    }

    public function delete()
    {
        try {
            $this->selectedCours->update(['status' => 'failed']);
            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Cours supprimé avec succès !');
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur lors de la suppression';
        }
    }

    public function toggleStatus($id)
    {
        try {
            $cours = Cour::findOrFail($id);
            $newStatus = $cours->status === 'Success' ? 'pending' : 'Success';
            $cours->update(['status' => $newStatus]);
            $this->showSuccessNotification('Statut mis à jour !');
        } catch (\Exception $e) {
            $this->showErrorNotification('Erreur lors du changement de statut');
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->specialite_id = null;
        $this->ue_id = null;
        $this->responsable_id = null;
        $this->credit_cc = 0;
        $this->credit_exam = 0;
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
                <div class="min-h-[60vh] flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl p-10 max-w-lg text-center">
                        <div class="mx-auto w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aucune filière attribuée</h2>
                        <p class="text-gray-600 mb-6">Vous devez avoir une filière attribuée pour gérer les cours.</p>
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
                        <h1 class="text-3xl font-bold text-gray-800">Gestion des Cours</h1>
                        <p class="text-gray-500">{{ $filiere->name }}</p>
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
                            Nouveau Cours
                        </button>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-blue-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Total Cours</p>
                                <p class="text-3xl font-bold">{{ $this->cours->total() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Cours Actifs</p>
                                <p class="text-3xl font-bold">{{ $this->cours->where('status', 'Success')->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-purple-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Enseignants</p>
                                <p class="text-3xl font-bold">{{ $this->enseignants->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-orange-500 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100">UE</p>
                                <p class="text-3xl font-bold">{{ $this->ues->count() }}</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="bg-white rounded-xl shadow p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par spécialité</label>
                            <select wire:model.live="filterSpecialite" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all">Toutes les spécialités</option>
                                @foreach($this->specialites as $spec)
                                    <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filtrer par UE</label>
                            <select wire:model.live="filterUe" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all">Toutes les UE</option>
                                @foreach($this->ues as $ue)
                                    <option value="{{ $ue->id }}">{{ $ue->code }} - {{ $ue->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Liste des Cours -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Liste des Cours</h2>
                    </div>

                    @if($this->cours->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cours</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Responsable</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">UE</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Crédits</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->cours as $cours)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $cours->code }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div>
                                                    <p class="font-medium text-gray-800">{{ $cours->name }}</p>
                                                    @if($cours->specialite)
                                                        <p class="text-xs text-indigo-600">{{ $cours->specialite->name }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($cours->responsable)
                                                    <div class="flex items-center">
                                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-2">
                                                            <span class="text-purple-600 font-semibold text-sm">{{ strtoupper(substr($cours->responsable->name, 0, 1)) }}</span>
                                                        </div>
                                                        <span class="text-sm text-gray-700">{{ $cours->responsable->name }} {{ $cours->responsable->lastname }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-sm">Non assigné</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($cours->ue)
                                                    <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-medium">{{ $cours->ue->code }}</span>
                                                @else
                                                    <span class="text-gray-400 text-sm">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex space-x-2">
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs" title="CC">CC: {{ $cours->credit_cc ?? 0 }}</span>
                                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs" title="Exam">EX: {{ $cours->credit_exam ?? 0 }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($cours->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Actif</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($cours->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button wire:click="openDetailsModal({{ $cours->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Détails">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openEditModal({{ $cours->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="toggleStatus({{ $cours->id }})" class="p-2 {{ $cours->status === 'Success' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition" title="{{ $cours->status === 'Success' ? 'Désactiver' : 'Activer' }}">
                                                        @if($cours->status === 'Success')
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                            </svg>
                                                        @else
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                    <button wire:click="openDeleteModal({{ $cours->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
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

                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $this->cours->links() }}
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Aucun cours</h3>
                            <p class="text-gray-500 mb-6">Commencez par créer votre premier cours.</p>
                            <button wire:click="openCreateModal" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Créer un cours
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Modal Créer -->
                @if($showCreateModal)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nouveau Cours</h2>
                            
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
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom du cours <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ex: Algorithmique">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Responsable (Enseignant)</label>
                                        <select wire:model="responsable_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Sélectionner un enseignant --</option>
                                            @foreach($this->enseignants as $enseignant)
                                                <option value="{{ $enseignant->id }}">{{ $enseignant->name }} {{ $enseignant->lastname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Spécialité</label>
                                        <select wire:model="specialite_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Générale --</option>
                                            @foreach($this->specialites as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Unité d'Enseignement (UE)</label>
                                        <select wire:model="ue_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Aucune UE --</option>
                                            @foreach($this->ues as $ue)
                                                <option value="{{ $ue->id }}">{{ $ue->code }} - {{ $ue->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Crédit CC</label>
                                        <input type="number" wire:model="credit_cc" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Crédit Examen</label>
                                        <input type="number" wire:model="credit_exam" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0">
                                    </div>
                                </div>
                                
                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        Créer le cours
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Modifier -->
                @if($showEditModal && $selectedCours)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier le Cours</h2>
                            
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
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom du cours <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Responsable (Enseignant)</label>
                                        <select wire:model="responsable_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Sélectionner un enseignant --</option>
                                            @foreach($this->enseignants as $enseignant)
                                                <option value="{{ $enseignant->id }}">{{ $enseignant->name }} {{ $enseignant->lastname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Spécialité</label>
                                        <select wire:model="specialite_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Générale --</option>
                                            @foreach($this->specialites as $spec)
                                                <option value="{{ $spec->id }}">{{ $spec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Unité d'Enseignement (UE)</label>
                                        <select wire:model="ue_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Aucune UE --</option>
                                            @foreach($this->ues as $ue)
                                                <option value="{{ $ue->id }}">{{ $ue->code }} - {{ $ue->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Crédit CC</label>
                                        <input type="number" wire:model="credit_cc" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Crédit Examen</label>
                                        <input type="number" wire:model="credit_exam" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0">
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
                @if($showDetailsModal && $selectedCours)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-3xl font-bold text-blue-600">{{ strtoupper(substr($selectedCours->name, 0, 2)) }}</span>
                                </div>
                                <h2 class="text-2xl font-semibold text-gray-800">{{ $selectedCours->name }}</h2>
                                <p class="text-gray-500 font-mono">{{ $selectedCours->code }}</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Filière</span>
                                    <span class="font-medium">{{ $selectedCours->filiere->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Spécialité</span>
                                    <span class="font-medium">{{ $selectedCours->specialite->name ?? 'Générale' }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Unité d'Enseignement</span>
                                    @if($selectedCours->ue)
                                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm">{{ $selectedCours->ue->code }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Responsable</span>
                                    @if($selectedCours->responsable)
                                        <span class="font-medium">{{ $selectedCours->responsable->name }} {{ $selectedCours->responsable->lastname }}</span>
                                    @else
                                        <span class="text-gray-400">Non assigné</span>
                                    @endif
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Crédits</span>
                                    <div class="flex space-x-2">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">CC: {{ $selectedCours->credit_cc ?? 0 }}</span>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Exam: {{ $selectedCours->credit_exam ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-gray-500">Statut</span>
                                    @if($selectedCours->status === 'Success')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Actif</span>
                                    @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">{{ ucfirst($selectedCours->status) }}</span>
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
                @if($showDeleteModal && $selectedCours)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">Supprimer le cours</h3>
                                <p class="text-gray-600 mb-2">Voulez-vous vraiment supprimer le cours</p>
                                <p class="font-semibold text-gray-800 mb-6">{{ $selectedCours->name }} ({{ $selectedCours->code }}) ?</p>
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