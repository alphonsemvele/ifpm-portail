<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Preinscription;
use App\Models\Specialite;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifMail;

name('admin.preinscription');
middleware(['auth', 'verified']);

new class extends Component {
    public $preinscriptions;
    public $showViewModal = false;
    public $showActivatedModal = false;
    public $showDeletedModal = false;
    public $showPaymentModal = false; // Nouveau modal pour le paiement
    public $preinscriptionElement = null;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        $this->preinscriptions = Preinscription::with('filiere.specialites', 'region', 'departement', 'arrondissement')->get();
    }

    public function functionShowViewModal($id)
    {
        $this->resetModals();
        $this->showViewModal = true;
        $this->preinscriptionElement = Preinscription::with('filiere.specialites', 'region', 'departement', 'arrondissement')->findOrFail($id);
    }

    public function functionShowActivatedViewModal($id)
    {
        $this->resetModals();
        $this->showActivatedModal = true;
        $this->preinscriptionElement = Preinscription::with('filiere.specialites', 'region', 'departement', 'arrondissement')->findOrFail($id);
    }

    public function functionShowDeletedViewModal($id)
    {
        $this->resetModals();
        $this->showDeletedModal = true;
        $this->preinscriptionElement = Preinscription::with('filiere.specialites', 'region', 'departement', 'arrondissement')->findOrFail($id);
    }

    public function functionShowPaymentModal($id)
    {
        $this->resetModals();
        $this->showPaymentModal = true;
        $this->preinscriptionElement = Preinscription::with('filiere.specialites', 'region', 'departement', 'arrondissement')->findOrFail($id);
    }

    public function functionShowFicheModal($id)
    {
        $this->resetModals();
        $this->preinscriptionElement = Preinscription::with('filiere.specialites', 'region', 'departement', 'arrondissement')->findOrFail($id);
        // Ajoutez ici la logique pour afficher la fiche (par exemple, redirection ou modal)
    }

    private function resetModals()
    {
        $this->showViewModal = false;
        $this->showActivatedModal = false;
        $this->showDeletedModal = false;
        $this->showPaymentModal = false;
        $this->preinscriptionElement = null;
    }

    public function closeViewModal()
    {
        $this->resetModals();
    }

    public function closeActivatedModal()
    {
        $this->resetModals();
    }

    public function closeDeletedModal()
    {
        $this->resetModals();
    }

    public function closePaymentModal()
    {
        $this->resetModals();
    }

    public function ActivatedPreinscription($id)
    {
        try {
            $preinscription = Preinscription::findOrFail($id);
            $preinscription->update(['status' => 'Success', 'isValidated' => true]);

            // Récupérer le prix de la spécialité
            $specialite = Specialite::find($preinscription->specialite_id);
            $price = $specialite ? $specialite->price : 0;

            // Lien de téléchargement basé sur la référence
            $downloadLink = route('generate.pdf', ['ref' => $preinscription->ref]);

            // Contenu de l'email
            $subject = "Validation de votre préinscription";
            $content = sprintf(
                'Bonjour %s %s,<br><br>Votre préinscription a été validée avec succès le %s. Vous pouvez maintenant procéder au paiement de %s FCFA pour la spécialité choisie.<br><br>Téléchargez votre fiche d\'inscription ici : <a href="%s">Lien de téléchargement</a><br><br>Cordialement,<br>L\'équipe de support',
                htmlspecialchars($preinscription->name),
                htmlspecialchars($preinscription->last_name),
                now()->format('d/m/Y H:i'),
                number_format($price, 0, ',', ' '),
                htmlspecialchars($downloadLink)
            );

            try {
                Mail::to($preinscription->email)->send(new NotifMail($subject, $content));
            } catch (\Exception $e) {
                logger('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            }

            $this->resetModals();
            $this->loadData();

            $this->showNotification = true;
            $this->notificationMessage = 'Préinscription validée avec succès ! Un email a été envoyé.';
            $this->notificationType = 'success';

            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            $this->showNotification = true;
            $this->notificationMessage = 'Erreur lors de la validation de la préinscription : ' . $e->getMessage();
            $this->notificationType = 'error';
        }
    }

    public function DeletedPreinscription($id)
    {
        try {
            $preinscription = Preinscription::findOrFail($id);
            $preinscription->update(['status' => 'failed']);

            $this->resetModals();
            $this->loadData();

            $this->showNotification = true;
            $this->notificationMessage = 'Préinscription supprimée avec succès !';
            $this->notificationType = 'success';

            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            $this->showNotification = true;
            $this->notificationMessage = 'Erreur lors de la suppression de la préinscription : ' . $e->getMessage();
            $this->notificationType = 'error';
        }
    }

    public function updatePaymentStatus($id)
    {
        try {
            $preinscription = Preinscription::findOrFail($id);
            $preinscription->update(['payment_status' => 'Success']);

            $this->resetModals();
            $this->loadData();

            $this->showNotification = true;
            $this->notificationMessage = 'Paiement marqué comme effectué avec succès !';
            $this->notificationType = 'success';

            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            $this->showNotification = true;
            $this->notificationMessage = 'Erreur lors de la mise à jour du paiement : ' . $e->getMessage();
            $this->notificationType = 'error';
        }
    }

    public function cancelPaymentStatus($id)
    {
        try {
            $preinscription = Preinscription::findOrFail($id);
            $preinscription->update(['payment_status' => 'pending']);

            $this->resetModals();
            $this->loadData();

            $this->showNotification = true;
            $this->notificationMessage = 'Paiement annulé avec succès !';
            $this->notificationType = 'success';

            $this->dispatch('auto-hide-notification');
        } catch (\Exception $e) {
            $this->showNotification = true;
            $this->notificationMessage = 'Erreur lors de l\'annulation du paiement : ' . $e->getMessage();
            $this->notificationType = 'error';
        }
    }

    public function hideNotification()
    {
        $this->showNotification = false;
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Notification -->
            <x-admin.notification :showNotification="$showNotification" :notificationType="$notificationType" :notificationMessage="$notificationMessage" />

            <!-- Liste des préinscriptions en attente -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Préinscriptions en attente</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Prénom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Prix (FCFA)</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Contact</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingTable">
                            @foreach ($preinscriptions->where('status', 'pending') as $data)
                                <tr class="bg-gray-50 rounded-lg" id="pending-{{ $data->id }}">
                                    <td class="p-4">{{ $data->name }}</td>
                                    <td class="p-4">{{ $data->last_name }}</td>
                                    <td class="p-4">{{ $data->filiere->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $data->specialite->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ number_format($data->price ?? 0, 0, ',', ' ') }}</td>
                                    <td class="p-4">{{ $data->contact }}</td>
                                    <td class="p-4">
                                        <div class="bg-white shadow-sm rounded-md p-2 flex justify-center space-x-1">
                                            <button wire:click.prevent="functionShowViewModal({{ $data->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Voir
                                            </button>
                                            <button wire:click.prevent="functionShowActivatedViewModal({{ $data->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Valider
                                            </button>
                                            <button wire:click.prevent="functionShowDeletedViewModal({{ $data->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                                wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Supprimer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal de vue -->
            @if($showViewModal && $preinscriptionElement)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-4xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="closeViewModal"
                                class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Fermer le modal</span>
                            </button>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-4">Détails de la préinscription</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <strong>Nom:</strong> {{ $preinscriptionElement->name }}
                                    </div>
                                    <div>
                                        <strong>Prénom:</strong> {{ $preinscriptionElement->last_name }}
                                    </div>
                                    <div>
                                        <strong>Filière:</strong> {{ $preinscriptionElement->filiere->name ?? 'Non défini' }}
                                    </div>
                                    <div>
                                        <strong>Spécialité:</strong> {{ $preinscriptionElement->specialite->name ?? 'Non défini' }}
                                    </div>
                                    <div>
                                        <strong>Prix (FCFA):</strong> {{ number_format($preinscriptionElement->price ?? 0, 0, ',', ' ') }}
                                    </div>
                                    <div>
                                        <strong>Contact:</strong> {{ $preinscriptionElement->contact }}
                                    </div>
                                    <div>
                                        <strong>Email:</strong> {{ $preinscriptionElement->email }}
                                    </div>
                                    <div>
                                        <strong>Date de naissance:</strong> {{ $preinscriptionElement->birth ? \Carbon\Carbon::parse($preinscriptionElement->birth)->format('d/m/Y') : 'Non défini' }}
                                    </div>
                                    <div>
                                        <strong>Référence:</strong> {{ $preinscriptionElement->ref }}
                                    </div>
                                    <div>
                                        <strong>Région:</strong> {{ $preinscriptionElement->region->name ?? 'Non défini' }}
                                    </div>
                                    <div>
                                        <strong>Département:</strong> {{ $preinscriptionElement->departement->name ?? 'Non défini' }}
                                    </div>
                                    <div>
                                        <strong>Arrondissement:</strong> {{ $preinscriptionElement->arrondissement->name ?? 'Non défini' }}
                                    </div>
                                    <div>
                                        <strong>Statut:</strong>
                                        <span class="px-2 py-1 rounded text-sm
                                            {{ $preinscriptionElement->status === 'Success' ? 'bg-green-100 text-green-800' :
                                               ($preinscriptionElement->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $preinscriptionElement->status }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>Statut de paiement:</strong>
                                        <span class="px-2 py-1 rounded text-sm
                                            {{ $preinscriptionElement->payment_status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $preinscriptionElement->payment_status === 'Success' ? 'Payé' : 'Non payé' }}
                                        </span>
                                    </div>
                                    <div class="col-span-2 mt-4">
                                        <strong>Fichiers soumis:</strong>
                                        <div class="flex flex-col gap-2 mt-2">
                                            @if($preinscriptionElement->birth_certificate)
                                                <div class="flex items-center gap-2">
                                                    <span>Acte de naissance:</span>
                                                    <a href="{{ asset('storage/' . $preinscriptionElement->birth_certificate) }}"
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                        </svg>
                                                        Télécharger / Voir
                                                    </a>
                                                </div>
                                            @else
                                                <span>Acte de naissance: Non soumis</span>
                                            @endif
                                            @if($preinscriptionElement->diploma)
                                                <div class="flex items-center gap-2">
                                                    <span>Diplôme:</span>
                                                    <a href="{{ asset('storage/' . $preinscriptionElement->diploma) }}"
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                        </svg>
                                                        Télécharger / Voir
                                                    </a>
                                                </div>
                                            @else
                                                <span>Diplôme: Non soumis</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal de validation -->
            @if($showActivatedModal && $preinscriptionElement)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="closeActivatedModal"
                                class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Fermer le modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center relative">
                                <svg class="mx-auto mb-4 text-green-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500">
                                    Êtes-vous sûr de vouloir valider la candidature de <strong>{{ $preinscriptionElement->name }} {{ $preinscriptionElement->last_name }}</strong> ?
                                </h3>
                                <button wire:click="ActivatedPreinscription({{ $preinscriptionElement->id }})"
                                    type="button" class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2"
                                    wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                    Oui, valider
                                </button>
                                <button type="button" wire:click="closeActivatedModal"
                                    class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                    Annuler
                                </button>
                                <div wire:loading wire:target="ActivatedPreinscription"
                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white bg-opacity-90 p-4 rounded-lg shadow-lg flex flex-col items-center pointer-events-none z-10">
                                    <svg class="animate-spin h-8 w-8 text-green-700 mb-2" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">En cours...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal de suppression -->
            @if($showDeletedModal && $preinscriptionElement)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="closeDeletedModal"
                                class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Fermer le modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center relative">
                                <svg class="mx-auto mb-4 text-red-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500">
                                    Êtes-vous sûr de vouloir supprimer la candidature de <strong>{{ $preinscriptionElement->name }} {{ $preinscriptionElement->last_name }}</strong> ?
                                </h3>
                                <button wire:click="DeletedPreinscription({{ $preinscriptionElement->id }})"
                                    type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2"
                                    wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                    Oui, supprimer
                                </button>
                                <button type="button" wire:click="closeDeletedModal"
                                    class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                    Annuler
                                </button>
                                <div wire:loading wire:target="DeletedPreinscription"
                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white bg-opacity-90 p-4 rounded-lg shadow-lg flex flex-col items-center pointer-events-none z-10">
                                    <svg class="animate-spin h-8 w-8 text-red-700 mb-2" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">En cours...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal de paiement -->
            @if($showPaymentModal && $preinscriptionElement)
                <div class="overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 flex">
                    <div class="relative p-4 w-full max-w-xl max-h-full mx-auto my-auto">
                        <div class="relative bg-white rounded-lg shadow-lg">
                            <button type="button" wire:click="closePaymentModal"
                                class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Fermer le modal</span>
                            </button>
                            <div class="p-4 md:p-5 text-center relative">
                                <svg class="mx-auto mb-4 text-blue-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <h3 class="mb-5 text-lg font-normal text-gray-500">
                                    Confirmer le paiement pour <strong>{{ $preinscriptionElement->name }} {{ $preinscriptionElement->last_name }}</strong> ?
                                </h3>
                                <button wire:click="updatePaymentStatus({{ $preinscriptionElement->id }})"
                                    type="button" class="text-white bg-blue-600 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2"
                                    wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                    Oui, confirmer
                                </button>
                                <button type="button" wire:click="closePaymentModal"
                                    class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100">
                                    Annuler
                                </button>
                                <div wire:loading wire:target="updatePaymentStatus"
                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white bg-opacity-90 p-4 rounded-lg shadow-lg flex flex-col items-center pointer-events-none z-10">
                                    <svg class="animate-spin h-8 w-8 text-blue-700 mb-2" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">En cours...</span>
                                </div>
                                <div wire:loading wire:target="cancelPaymentStatus"
                                    class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white bg-opacity-90 p-4 rounded-lg shadow-lg flex flex-col items-center pointer-events-none z-10">
                                    <svg class="animate-spin h-8 w-8 text-yellow-700 mb-2" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-800">En cours...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des préinscriptions validées -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Préinscriptions validées</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Prénom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Filière</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Prix (FCFA)</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Contact</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut de paiement</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="successTable">
                            @foreach ($preinscriptions->where('status', 'Success') as $data)
                                <tr class="bg-gray-50 rounded-lg">
                                    <td class="p-4">{{ $data->name }}</td>
                                    <td class="p-4">{{ $data->last_name }}</td>
                                    <td class="p-4">{{ $data->filiere->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $data->specialite->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ number_format($data->price ?? 0, 0, ',', ' ') }}</td>
                                    <td class="p-4">{{ $data->contact }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-sm
                                            {{ $data->payment_status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $data->payment_status === 'Success' ? 'Payé' : 'Non payé' }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="bg-white shadow-sm rounded-md p-2 flex justify-center space-x-1">
                                            <button wire:click.prevent="functionShowViewModal({{ $data->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Voir
                                            </button>
                                            @if($data->payment_status === 'pending')
                                                <button wire:click.prevent="functionShowPaymentModal({{ $data->id }})"
                                                    class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.1.9-2 2-2s2 .9 2 2-2 2-2 2-2-.9-2-2zM3 3v18h18V3H3zm15 15H6v-2h12v2zm0-4H6V7h12v7z" />
                                                    </svg>
                                                    Paiement effectué
                                                </button>
                                            @elseif($data->payment_status === 'Success')
                                                <button wire:click.prevent="functionShowPaymentModal({{ $data->id }})"
                                                    class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                                    wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Annuler paiement
                                                </button>
                                            @endif
                                            <a  href= {{ route('generate.pdf', ['ref' => $data->ref]) }}
                                                class="inline-flex items-center px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                                wire:loading.class="opacity-50 cursor-not-allowed" wire:loading.attr="disabled">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v10l-4 4z" />
                                                </svg>
                                                Voir la fiche
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('livewire:init', function () {
                Livewire.on('auto-hide-notification', () => {
                    setTimeout(() => {
                        @this.call('hideNotification');
                    }, 3000);
                });
            });
        </script>
    @endvolt
</x-layouts.app>
