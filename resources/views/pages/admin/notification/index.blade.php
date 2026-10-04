<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifMail;

name('admin.notifications');
middleware(['auth', 'verified']);

new class extends Component {
    // Propriétés publiques avec valeurs par défaut explicites
    public $notifications = [];
    public bool $showAddModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public bool $showActivateModal = false;
    public bool $showDeactivateModal = false;
    public bool $showDetailsModal = false;
    public $notificationElement = null;
    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = '';
    public array $formErrors = [];

    // Propriétés du formulaire
    public string $name = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $phone = '';
    public string $status = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->notifications = Notification::all();
            logger('Données chargées', [
                'notifications_count' => $this->notifications->count(),
            ]);
        } catch (\Exception $e) {
            logger('Erreur loadData: ' . $e->getMessage());
            $this->notifications = collect();
        }
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
        $this->formErrors = [];
        logger('Modal ajouter ouvert');
    }

    public function openEditModal($id)
    {
        try {
            $this->notificationElement = Notification::findOrFail($id);
            $this->name = $this->notificationElement->name ?? '';
            $this->email = $this->notificationElement->email ?? '';
            $this->whatsapp = $this->notificationElement->whatsapp ?? '';
            $this->phone = $this->notificationElement->phone ?? '';
            $this->status = $this->notificationElement->status ?? '';
            $this->showEditModal = true;
            $this->formErrors = [];
            logger('Modal édition ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openEditModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal d\'édition');
        }
    }

    public function openActivateModal($id)
    {
        try {
            $this->notificationElement = Notification::findOrFail($id);
            $this->showActivateModal = true;
            logger('Modal activation ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openActivateModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function openDeactivateModal($id)
    {
        try {
            $this->notificationElement = Notification::findOrFail($id);
            $this->showDeactivateModal = true;
            logger('Modal désactivation ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDeactivateModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function openDeleteModal($id)
    {
        try {
            $this->notificationElement = Notification::findOrFail($id);
            $this->showDeleteModal = true;
            logger('Modal suppression ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDeleteModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function openDetailsModal($id)
    {
        try {
            $this->notificationElement = Notification::findOrFail($id);
            $this->showDetailsModal = true;
            logger('Modal détails ouvert pour ID: ' . $id);
        } catch (\Exception $e) {
            logger('Erreur openDetailsModal: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'ouverture du modal');
        }
    }

    public function save()
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'whatsapp' => 'nullable|string|max:20',
                'phone' => 'required|string|max:20',
                'status' => 'nullable|in:Success,pending',
            ];

            $validated = $this->validate($rules);

            Notification::create([
                'name' => $this->name,
                'email' => $this->email,
                'whatsapp' => $this->whatsapp,
                'phone' => $this->phone,
                'status' => $this->status ?: 'pending',
            ]);

            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showSuccessNotification('Notification ajoutée avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur save: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la sauvegarde');
        }
    }

    public function update()
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'whatsapp' => 'nullable|string|max:20',
                'phone' => 'required|string|max:20',
                'status' => 'nullable|in:Success,pending',
            ];

            $validated = $this->validate($rules);

            $this->notificationElement->update([
                'name' => $this->name,
                'email' => $this->email,
                'whatsapp' => $this->whatsapp,
                'phone' => $this->phone,
                'status' => $this->status ?: 'pending',
            ]);

            $this->resetForm();
            $this->showEditModal = false;
            $this->notificationElement = null;
            $this->loadData();
            $this->showSuccessNotification('Notification mise à jour avec succès !');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->formErrors = $e->errors();
        } catch (\Exception $e) {
            logger('Erreur update: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la mise à jour');
        }
    }

    public function activate($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['status' => 'Success']);

            // Envoi de l'email de confirmation (style préinscription)
            $subject = "Activation de vos notifications";
            $content = sprintf(
                'Bonjour %s,<br><br>Vos notifications ont été activées avec succès le %s. Vous recevrez désormais nos mises à jour par email.<br><br>Merci de votre inscription !<br><br>Cordialement,<br>L\'équipe de support',
                htmlspecialchars($notification->name),
                date('d/m/Y H:i')
            );

            try {
                Mail::to($notification->email)->send(new NotifMail($subject, $content));
            } catch (\Exception $e) {
                logger('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            }

            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Notification activée avec succès ! Un email a été envoyé.');
        } catch (\Exception $e) {
            logger('Erreur activate: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de l\'activation');
        }
    }

    public function deactivate($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['status' => 'pending']);
            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Notification désactivée avec succès !');
        } catch (\Exception $e) {
            logger('Erreur deactivate: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la désactivation');
        }
    }

    public function delete($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();
            $this->closeModal();
            $this->loadData();
            $this->showSuccessNotification('Notification supprimée avec succès !');
        } catch (\Exception $e) {
            logger('Erreur delete: ' . $e->getMessage());
            $this->addError('general', 'Erreur lors de la suppression');
        }
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->showDetailsModal = false;
        $this->notificationElement = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->whatsapp = '';
        $this->phone = '';
        $this->status = '';
        $this->formErrors = [];
    }

    private function showSuccessNotification($message)
    {
        $this->notificationMessage = $message;
        $this->notificationType = 'success';
        $this->showNotification = true;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Notifications</h1>
                    <p class="text-gray-500">Ajoutez, modifiez, activez, désactivez ou supprimez</p>
                </div>
            </div>

            <!-- Bouton Ajouter -->
            <div class="mb-8 text-center">
                <button wire:click="openAddModal" type="button"
                    class="bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un personnel
                </button>
            </div>

            <!-- Erreurs générales -->
            @error('general')
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg border-l-4 border-red-500">
                    {{ $message }}
                </div>
            @enderror

            <!-- Liste des Notifications -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Notifications</h2>

                @if(count($notifications) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Email</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Téléphone</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notifications as $notification)
                                    <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <td class="p-4">{{ $notification->name ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $notification->email ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $notification->phone ?? 'Non défini' }}</td>
                                        <td class="p-4">
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                                {{ $notification->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $notification->status === 'Success' ? 'Actif' : 'En attente' }}
                                            </span>
                                        </td>
                                        <td class="p-4 flex space-x-2">
                                            <button wire:click="openDetailsModal({{ $notification->id }})" type="button" class="bouton-icone text-sky-600 hover:bg-sky-50" title="Détails" aria-label="Détails"><x-icone-action nom="voir" /></button>
                                            @if ($notification->status === 'pending')
                                                <button wire:click="openActivateModal({{ $notification->id }})" type="button" class="bouton-icone text-emerald-600 hover:bg-emerald-50" title="Activer" aria-label="Activer"><x-icone-action nom="activer" /></button>
                                            @else
                                                <button wire:click="openDeactivateModal({{ $notification->id }})" type="button" class="bouton-icone text-amber-600 hover:bg-amber-50" title="Désactiver" aria-label="Désactiver"><x-icone-action nom="desactiver" /></button>
                                            @endif
                                            <button wire:click="openEditModal({{ $notification->id }})" type="button" class="bouton-icone text-indigo-600 hover:bg-indigo-50" title="Modifier" aria-label="Modifier"><x-icone-action nom="modifier" /></button>
                                            <button wire:click="openDeleteModal({{ $notification->id }})" type="button" class="bouton-icone text-red-600 hover:bg-red-50" title="Supprimer" aria-label="Supprimer"><x-icone-action nom="supprimer" /></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-gray-500">Aucune notification trouvée.</p>
                @endif
            </div>

            <!-- Modal Ajouter -->
            @if ($showAddModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Ajouter une Notification</h2>

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

                        <form wire:submit="save">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" wire:model="email" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">WhatsApp (optionnel)</label>
                                    <input type="text" wire:model="whatsapp"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                    <input type="text" wire:model="phone" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                </div>
                                {{-- <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model="status"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Sélectionner un statut</option>
                                        <option value="pending">En attente</option>
                                    </select>
                                </div> --}}
                            </div>

                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700">
                                    Enregistrer
                                </button>
                                <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Modifier -->
            @if ($showEditModal && $notificationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Modifier la Notification</h2>

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

                        <form wire:submit="update">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" wire:model="name" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                                        value="{{ $name }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" wire:model="email" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                                        value="{{ $email }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">WhatsApp (optionnel)</label>
                                    <input type="text" wire:model="whatsapp"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                                        value="{{ $whatsapp }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                    <input type="text" wire:model="phone" required
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                                        value="{{ $phone }}">
                                </div>
                                {{-- <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model="status"
                                        class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Sélectionner un statut</option>
                                        <option value="Success" {{ $status === 'Success' ? 'selected' : '' }}>Actif</option>
                                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>En attente</option>
                                    </select>
                                </div> --}}
                            </div>

                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700">
                                    Mettre à jour
                                </button>
                                <button type="button" wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Activer -->
            @if ($showActivateModal && $notificationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer la notification</h3>
                        <p class="mb-6 text-gray-600">Voulez-vous activer la notification pour {{ $notificationElement->name }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button type="button" wire:click="activate({{ $notificationElement->id }})"
                                class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700">
                                Oui
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Désactiver -->
            @if ($showDeactivateModal && $notificationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver la notification</h3>
                        <p class="mb-6 text-gray-600">Voulez-vous désactiver la notification pour {{ $notificationElement->name }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button type="button" wire:click="deactivate({{ $notificationElement->id }})"
                                class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700">
                                Oui
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Détails -->
            @if ($showDetailsModal && $notificationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Détails de la Notification</h2>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <strong class="text-gray-700">Nom :</strong> {{ $notificationElement->name ?? 'Non défini' }}
                            </div>
                            <div>
                                <strong class="text-gray-700">Email :</strong> {{ $notificationElement->email ?? 'Non défini' }}
                            </div>
                            <div>
                                <strong class="text-gray-700">WhatsApp :</strong> {{ $notificationElement->whatsapp ?? 'Non défini' }}
                            </div>
                            <div>
                                <strong class="text-gray-700">Téléphone :</strong> {{ $notificationElement->phone ?? 'Non défini' }}
                            </div>
                            <div>
                                <strong class="text-gray-700">Statut :</strong>
                                <span class="{{ $notificationElement->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} px-3 py-1 text-xs font-medium rounded-full">
                                    {{ $notificationElement->status === 'Success' ? 'Actif' : 'En attente' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer -->
            @if ($showDeleteModal && $notificationElement)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer la notification</h3>
                        <p class="mb-6 text-gray-600">Voulez-vous supprimer la notification pour {{ $notificationElement->name }} ? Cette action est irréversible.</p>
                        <div class="flex justify-end space-x-4">
                            <button type="button" wire:click="delete({{ $notificationElement->id }})"
                                class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700">
                                Oui
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full"
                    x-data="{ show: true }"
                    x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 3000)"
                    x-show="show">
                    <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-md border-l-4">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif

        </div>
    @endvolt
</x-layouts.app>
