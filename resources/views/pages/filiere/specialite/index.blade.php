<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Specialite;
use App\Models\User;

name('specialites.liste');
middleware(['auth', 'verified']);

new class extends Component {
    use WithPagination;

    public $filiere;
    public $hasFiliere = false;

    // Formulaire
    public $name = '';
    public $responsable_id = null;

    // Modals
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;

    public $selectedSpecialite = null;

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

    public function getSpecialitesProperty()
    {
        if (!$this->hasFiliere || !$this->filiere) {
            return collect()->paginate(15); // paginateur vide si pas de filière
        }

        return Specialite::query()
            ->with(['responsable'])
            ->withCount(['etudiants' => fn($q) => $q->where('role', 'student')])
            ->where('filiere_id', $this->filiere->id)
            ->where('status', '!=', 'failed')
            ->orderBy('name')
            ->paginate(15);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($id)
    {
        $this->selectedSpecialite = Specialite::findOrFail($id);
        $this->name = $this->selectedSpecialite->name;
        $this->responsable_id = $this->selectedSpecialite->responsable_id;
        $this->formErrors = [];
        $this->showEditModal = true;
    }

    public function openDetailsModal($id)
    {
        $this->selectedSpecialite = Specialite::with(['responsable', 'filiere'])->findOrFail($id);
        $this->showDetailsModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->selectedSpecialite = Specialite::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = $this->showEditModal = $this->showDeleteModal = $this->showDetailsModal = false;
        $this->selectedSpecialite = null;
        $this->resetForm();
    }

    public function save()
    {
        $this->formErrors = [];

        $this->validate([
            'name'           => 'required|string|max:255|unique:specialites,name,NULL,id,filiere_id,' . $this->filiere->id,
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        try {
            $specialite = Specialite::create([
                'name'           => $this->name,
                'filiere_id'     => $this->filiere->id,
                'responsable_id' => $this->responsable_id ?: null,
                'status'         => 'Success',
            ]);

            if ($this->responsable_id) {
                $responsable = User::find($this->responsable_id);
                if ($responsable && $responsable->aLeRole('enseignant')) {
                    $responsable->update(['specialite_id' => $specialite->id]);
                }
            }

            $this->closeModal();
            $this->resetPage(); // recharge la pagination
            $this->showSuccessNotification('Spécialité créée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur création : ' . $e->getMessage();
        }
    }

    public function update()
    {
        $this->formErrors = [];

        $this->validate([
            'name'           => 'required|string|max:255|unique:specialites,name,' . $this->selectedSpecialite->id . ',id,filiere_id,' . $this->filiere->id,
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        try {
            $oldResponsableId = $this->selectedSpecialite->responsable_id;

            $this->selectedSpecialite->update([
                'name'           => $this->name,
                'responsable_id' => $this->responsable_id ?: null,
            ]);

            if ($this->responsable_id) {
                $responsable = User::find($this->responsable_id);
                if ($responsable && $responsable->aLeRole('enseignant')) {
                    $responsable->update(['specialite_id' => $this->selectedSpecialite->id]);
                }
            }

            if ($oldResponsableId && $oldResponsableId != $this->responsable_id) {
                $old = User::find($oldResponsableId);
                if ($old) {
                    $old->update(['specialite_id' => null]);
                }
            }

            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Spécialité modifiée avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur mise à jour';
        }
    }

    public function delete()
    {
        try {
            if ($this->selectedSpecialite->responsable_id) {
                $responsable = User::find($this->selectedSpecialite->responsable_id);
                if ($responsable) {
                    $responsable->update(['specialite_id' => null]);
                }
            }

            $this->selectedSpecialite->update(['status' => 'failed']);
            $this->closeModal();
            $this->resetPage();
            $this->showSuccessNotification('Spécialité supprimée avec succès !');
        } catch (\Exception $e) {
            $this->formErrors['general'] = 'Erreur suppression';
        }
    }

    private function resetForm()
    {
        $this->name = '';
        $this->responsable_id = null;
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

    public function getEnseignantsProperty()
    {
        if (!$this->filiere) {
            return collect();
        }

        return User::where('filiere_id', $this->filiere->id)
            ->where('role', 'enseignant')
            ->where('status', '!=', 'failed')
            ->orderBy('name')
            ->get();
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
                        <p class="text-gray-600 mb-6">Vous devez avoir une filière attribuée pour gérer les spécialités.</p>
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
                        <h1 class="text-3xl font-bold text-gray-800">Gestion des Spécialités</h1>
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
                            Ajouter une spécialité
                        </button>
                    </div>
                </div>

                <!-- Liste -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Liste des Spécialités</h2>
                    </div>

                    @if($this->specialites->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Spécialité</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Responsable</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Apprenants</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">UE</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->specialites as $spec)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $spec->name }}</td>
                                            <td class="px-6 py-4 text-gray-700">
                                                {{ $spec->responsable ? $spec->responsable->name . ' ' . $spec->responsable->lastname : '—' }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700">{{ $spec->etudiants_count }}</td>
                                            <td class="px-6 py-4 text-gray-700">{{ $spec->ues()->count() }}</td>
                                            <td class="px-6 py-4">
                                                @if($spec->status === 'Success')
                                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Active</span>
                                                @else
                                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($spec->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <button wire:click="openDetailsModal({{ $spec->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Détails">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openEditModal({{ $spec->id }})" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="openDeleteModal({{ $spec->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
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
                            {{ $this->specialites->links() }}
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Aucune spécialité</h3>
                            <p class="text-gray-500 mb-6">Commencez par en ajouter une à votre filière.</p>
                            <button wire:click="openCreateModal" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Ajouter une spécialité
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Modal Créer / Modifier (identique pour les deux) -->
                @if($showCreateModal || $showEditModal)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                                {{ $showCreateModal ? 'Ajouter une Spécialité' : 'Modifier la Spécialité' }}
                            </h2>

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

                            <form wire:submit="{{ $showCreateModal ? 'save' : 'update' }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="ex: Génie Logiciel">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Responsable (optionnel)</label>
                                        <select wire:model="responsable_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                            <option value="">-- Aucun responsable --</option>
                                            @foreach($this->enseignants as $ens)
                                                <option value="{{ $ens->id }}">{{ $ens->name }} {{ $ens->lastname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-8 flex justify-end space-x-4">
                                    <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Annuler
                                    </button>
                                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                        {{ $showCreateModal ? 'Créer' : 'Mettre à jour' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Modal Détails -->
                @if($showDetailsModal && $selectedSpecialite)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span class="text-3xl font-bold text-indigo-600">{{ strtoupper(substr($selectedSpecialite->name, 0, 1)) }}</span>
                                </div>
                                <h2 class="text-2xl font-semibold text-gray-800">{{ $selectedSpecialite->name }}</h2>
                                <p class="text-gray-500">Spécialité</p>
                            </div>

                            <div class="space-y-4">
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Nom</span>
                                    <span class="font-medium">{{ $selectedSpecialite->name }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Responsable</span>
                                    <span class="font-medium">
                                        {{ $selectedSpecialite->responsable ? $selectedSpecialite->responsable->name . ' ' . $selectedSpecialite->responsable->lastname : 'Non assigné' }}
                                    </span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Nombre d'apprenants</span>
                                    <span class="font-medium">{{ $selectedSpecialite->etudiants_count }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Nombre d'UE</span>
                                    <span class="font-medium">{{ $selectedSpecialite->ues()->count() }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-500">Filière</span>
                                    <span class="font-medium">{{ $selectedSpecialite->filiere->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between py-3">
                                    <span class="text-gray-500">Statut</span>
                                    @if($selectedSpecialite->status === 'Success')
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Active</span>
                                    @else
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">{{ ucfirst($selectedSpecialite->status) }}</span>
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
                @if($showDeleteModal && $selectedSpecialite)
                    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                        <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">Supprimer la spécialité</h3>
                                <p class="text-gray-600 mb-6">
                                    Voulez-vous vraiment supprimer <strong>{{ $selectedSpecialite->name }}</strong> ?
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