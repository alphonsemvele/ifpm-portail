<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

name('specialite.profil');
middleware(['auth', 'verified']);

new class extends Component {
    use WithFileUploads;

    public $user;
    public $filiere;
    public $specialite;
    public $cycle;
    
    // Champs éditables
    public $name;
    public $lastname;
    public $email;
    public $contact;
    public $whatsapp;
    public $photo;
    public $newPhoto;
    
    public bool $showEditModal = false;
    public bool $showNotification = false;
    public string $notificationMessage = '';
    public string $notificationType = 'success';

    public function mount()
    {
        $this->user = auth()->user();
        $this->filiere = $this->user->filiere;
        $this->specialite = $this->user->specialite;
        $this->cycle = $this->user->cycle;
        
        // Initialiser les champs
        $this->name = $this->user->name;
        $this->lastname = $this->user->lastname;
        $this->email = $this->user->email;
        $this->contact = $this->user->contact;
        $this->whatsapp = $this->user->whatsapp;
        $this->photo = $this->user->photo;
    }

    public function openEditModal()
    {
        $this->showEditModal = true;
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->newPhoto = null;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'newPhoto' => 'nullable|image|max:2048',
        ]);

        $this->user->name = $this->name;
        $this->user->lastname = $this->lastname;
        $this->user->contact = $this->contact;
        $this->user->whatsapp = $this->whatsapp;

        if ($this->newPhoto) {
            $path = $this->newPhoto->store('photos', 'public');
            $this->user->photo = $path;
            $this->photo = $path;
        }

        $this->user->save();
        
        $this->showEditModal = false;
        $this->newPhoto = null;
        $this->showSuccessNotification('Profil mis à jour avec succès !');
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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mon Profil</h1>
                    <p class="text-gray-500">Consultez et gérez vos informations personnelles</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/filiere" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div>
                        
                        
                    </div>
                </div>
            </div>

            <!-- Carte Profil Principal -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
                <!-- Banner -->
                <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                
                <!-- Photo et infos -->
                <div class="relative px-6 pb-6">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:space-x-6">
                        <div class="-mt-16 relative">
                            <div class="w-32 h-32 bg-white rounded-full p-1 shadow-lg">
                                @if($photo)
                                    <img src="{{ asset('storage/' . $photo) }}" alt="Photo de profil" class="w-full h-full rounded-full object-cover">
                                @else
                                    <div class="w-full h-full rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-4xl font-bold text-indigo-600">{{ strtoupper(substr($name, 0, 1)) }}{{ strtoupper(substr($lastname ?? '', 0, 1)) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0 sm:pb-2 flex-1">
                            <h2 class="text-2xl font-bold text-gray-800">{{ $name }} {{ $lastname ?? '' }}</h2>
                            <p class="text-gray-500">{{ ucfirst($user->role) }}</p>
                        </div>
                        <button wire:click="openEditModal" class="mt-4 sm:mt-0 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informations détaillées -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Informations Personnelles -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informations Personnelles
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Nom complet</span>
                            <span class="font-medium text-gray-800">{{ $name }} {{ $lastname ?? '' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-gray-800">{{ $email }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Téléphone</span>
                            <span class="font-medium text-gray-800">{{ $contact ?? 'Non renseigné' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">WhatsApp</span>
                            <span class="font-medium text-gray-800">{{ $whatsapp ?? 'Non renseigné' }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-gray-500">Matricule</span>
                            <span class="font-medium text-gray-800">{{ $user->matricule ?? 'Non attribué' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Informations Académiques -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Informations Académiques
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Rôle</span>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">{{ ucfirst($user->role) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Filière</span>
                            <span class="font-medium text-gray-800">{{ $filiere->name ?? 'Non attribuée' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Spécialité</span>
                            <span class="font-medium text-gray-800">{{ $specialite->name ?? 'Non attribuée' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b border-gray-100">
                            <span class="text-gray-500">Cycle</span>
                            <span class="font-medium text-gray-800">{{ $cycle->name ?? 'Non attribué' }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-gray-500">Statut</span>
                            @if($user->status === 'Success')
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Actif</span>
                            @else
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">{{ ucfirst($user->status ?? 'En attente') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informations Familiales (si apprenant) -->
                @if($user->role === 'student')
                <div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Informations Familiales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700">Père</h4>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Nom</span>
                                <span class="font-medium text-gray-800">{{ $user->father_name ?? 'Non renseigné' }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-500">Contact</span>
                                <span class="font-medium text-gray-800">{{ $user->father_contact ?? 'Non renseigné' }}</span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700">Mère</h4>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500">Nom</span>
                                <span class="font-medium text-gray-800">{{ $user->mother_name ?? 'Non renseigné' }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-500">Contact</span>
                                <span class="font-medium text-gray-800">{{ $user->mother_contact ?? 'Non renseigné' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Modal Modifier Profil -->
            @if($showEditModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modifier mon profil</h2>
                        <form wire:submit="updateProfile">
                            <div class="space-y-4">
                                <!-- Photo -->
                                <div class="text-center">
                                    <div class="w-24 h-24 mx-auto mb-4 relative">
                                        @if($newPhoto)
                                            <img src="{{ $newPhoto->temporaryUrl() }}" class="w-full h-full rounded-full object-cover">
                                        @elseif($photo)
                                            <img src="{{ asset('storage/' . $photo) }}" class="w-full h-full rounded-full object-cover">
                                        @else
                                            <div class="w-full h-full rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-2xl font-bold text-indigo-600">{{ strtoupper(substr($name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <label class="cursor-pointer text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                        <span>Changer la photo</span>
                                        <input type="file" wire:model="newPhoto" class="hidden" accept="image/*">
                                    </label>
                                    @error('newPhoto') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <!-- Nom -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Prénom -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Prénom</label>
                                    <input type="text" wire:model="lastname" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    @error('lastname') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email (lecture seule) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                                    <input type="email" value="{{ $email }}" disabled class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                                    <p class="text-xs text-gray-400 mt-1">L'email ne peut pas être modifié</p>
                                </div>

                                <!-- Contact -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Téléphone</label>
                                    <input type="text" wire:model="contact" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ex: 6 99 00 00 00">
                                    @error('contact') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- WhatsApp -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">WhatsApp</label>
                                    <input type="text" wire:model="whatsapp" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Ex: 6 99 00 00 00">
                                    @error('whatsapp') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="button" wire:click="closeModal" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                                    Annuler
                                </button>
                                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200">
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Notification -->
            @if($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" 
                     x-data="{ show: true }" 
                     x-init="setTimeout(() => { show = false; $wire.set('showNotification', false); }, 3000)"
                     x-show="show"
                     x-transition>
                    <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700 border-green-500' : 'bg-red-100 text-red-700 border-red-500' }} p-4 rounded-xl shadow-lg border-l-4">
                        <div class="flex items-center">
                            @if($notificationType === 'success')
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                            {{ $notificationMessage }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endvolt
</x-layouts.app>