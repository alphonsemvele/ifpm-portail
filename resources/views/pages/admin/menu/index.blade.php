
<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Menu;
use App\Models\MenuDay;
use App\Models\Commande;
use Carbon\Carbon;

name('admin.menus');
middleware(['auth', 'verified']);

new class extends Component {
    public $menus;
    public $menuDays;
    public $orders;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $menuElement;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';
    public $formErrors = [];

    public $name = '';
    public $status = '';
    public $type = '';
    public $categorie = '';
    public $day = '';
    public $hours = '';
    public $specialday = '';
    public $menu_day_type = '';
    public $price = ''; // Ajout du champ price

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        \Log::info('loadData appelé à ' . now()->toDateTimeString());
        $this->menus = Menu::with('menuDays')->where('status', '!=', 'failed')->get();
        $this->orders = Commande::with(['menuDay', 'user'])->where('status', '!=', 'failed')->get();

        // Récupérer le jour actuel
        $currentDay = Carbon::now()->format('l'); // Exemple : "Sunday"
        $currentDate = Carbon::now()->toDateString(); // Exemple : "2025-08-24"

        // Filtrer les MenuDay en fonction du jour actuel ou du jour spécial
        $this->menuDays = MenuDay::with('menu')
            ->where('status', 'Success')
            ->where(function ($query) use ($currentDay, $currentDate) {
                $query->where(function ($q) use ($currentDay) {
                    $q->where('day', $currentDay)
                      ->where('type', 'allDay'); // Afficher uniquement si allDay et jour correspond
                })->orWhere(function ($q) use ($currentDate) {
                    $q->where('specialday', $currentDate)
                      ->where('type', 'one'); // Afficher uniquement si one et date correspond
                });
            })
            ->get();

        \Log::info('Données chargées : ', [
            'menus' => $this->menus->count(),
            'menuDays' => $this->menuDays->count(),
            'orders' => $this->orders->count(),
            'currentDay' => $currentDay,
            'currentDate' => $currentDate,
        ]);
    }

    public function save()
    {
        \Log::info('Méthode save appelée', [
            'name' => $this->name,
            'status' => $this->status,
            'type' => $this->type,
            'categorie' => $this->categorie,
            'day' => $this->day ?: null,
            'hours' => $this->hours ?: null,
            'specialday' => $this->specialday ?: null,
            'menu_day_type' => $this->menu_day_type,
            'price' => $this->price, // Ajout du log pour price
        ]);

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'status' => 'nullable|in:Success,pending',
                'type' => 'required|in:dessert,repas,complement',
                'categorie' => 'required|in:dejeuner,diner,souper',
                'day' => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'hours' => 'nullable|date_format:H:i',
                'specialday' => 'nullable|date',
                'menu_day_type' => 'nullable|in:one,allDay',
                'price' => 'required|integer|min:0', // Ajout de la validation pour price
            ]);

            \DB::beginTransaction();

            $menu = Menu::create([
                'name' => $this->name,
                'status' => $this->status ?? 'Success',
                'type' => $this->type,
                'categorie' => $this->categorie,
                'price' => $this->price, // Ajout du champ price
            ]);

            if ($this->day || $this->specialday || $this->hours || $this->menu_day_type) {
                MenuDay::create([
                    'menu_id' => $menu->id,
                    'day' => $this->day ?: null,
                    'hours' => $this->hours ?: null,
                    'specialday' => $this->specialday ?: null,
                    'status' => 'Success',
                    'type' => $this->menu_day_type ?: 'one',
                ]);
            }

            \DB::commit();

            \Log::info('Plat créé avec succès');
            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showNotification('Plat ajouté avec succès !', 'success');
            $this->formErrors = [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            \Log::error('Erreur de validation dans save : ', $e->errors());
            $this->formErrors = $e->errors();
            $this->showNotification('Veuillez corriger les erreurs dans le formulaire.', 'error');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans save : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ajout du plat : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowAddModal()
    {
        \Log::info('functionShowAddModal appelé');
        $this->resetForm();
        $this->showAddModal = true;
        $this->formErrors = [];
    }

    public function functionShowEditModal($id)
    {
        try {
            \Log::info('functionShowEditModal appelé avec ID: ' . $id);
            $this->menuElement = Menu::with('menuDays')->findOrFail($id);
            $this->name = $this->menuElement->name;
            $this->status = $this->menuElement->status;
            $this->type = $this->menuElement->type;
            $this->categorie = $this->menuElement->categorie;
            $this->price = $this->menuElement->price; // Ajout du champ price
            $menuDay = $this->menuElement->menuDays->first();
            $this->day = $menuDay->day ?? '';
            $this->hours = $menuDay->hours ? \Carbon\Carbon::parse($menuDay->hours)->format('H:i') : '';
            $this->specialday = $menuDay->specialday ?? '';
            $this->menu_day_type = $menuDay->type ?? '';
            $this->showEditModal = true;
            $this->formErrors = [];
            $this->dispatch('edit-modal-opened', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans functionShowEditModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'édition : ' . $e->getMessage(), 'error');
        }
    }

    public function update()
    {
        \Log::info('Méthode update appelée', [
            'name' => $this->name,
            'status' => $this->status,
            'type' => $this->type,
            'categorie' => $this->categorie,
            'day' => $this->day ?: null,
            'hours' => $this->hours ?: null,
            'specialday' => $this->specialday ?: null,
            'menu_day_type' => $this->menu_day_type,
            'price' => $this->price, // Ajout du log pour price
        ]);

        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'status' => 'nullable|in:Success,pending',
                'type' => 'required|in:dessert,repas,complement',
                'categorie' => 'required|in:dejeuner,diner,souper',
                'day' => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'hours' => 'nullable|date_format:H:i',
                'specialday' => 'nullable|date',
                'menu_day_type' => 'nullable|in:one,allDay',
                'price' => 'required|integer|min:0', // Ajout de la validation pour price
            ]);

            $this->menuElement->update([
                'name' => $this->name,
                'status' => $this->status,
                'type' => $this->type,
                'categorie' => $this->categorie,
                'price' => $this->price, // Ajout du champ price
            ]);

            $menuDay = $this->menuElement->menuDays->first();
            if ($menuDay) {
                $menuDay->update([
                    'day' => $this->day ?: null,
                    'hours' => $this->hours ?: null,
                    'specialday' => $this->specialday ?: null,
                    'type' => $this->menu_day_type ?: 'one',
                    'status' => $this->status == 'Success' ? 'Success' : 'pending',
                ]);
            } else if ($this->day || $this->specialday || $this->hours || $this->menu_day_type) {
                MenuDay::create([
                    'menu_id' => $this->menuElement->id,
                    'day' => $this->day ?: null,
                    'hours' => $this->hours ?: null,
                    'specialday' => $this->specialday ?: null,
                    'status' => $this->status == 'Success' ? 'Success' : 'pending',
                    'type' => $this->menu_day_type ?: 'one',
                ]);
            }

            \Log::info('Plat mis à jour avec succès');
            $this->resetForm();
            $this->showEditModal = false;
            $this->menuElement = null;
            $this->loadData();
            $this->showNotification('Plat mis à jour avec succès !', 'success');
            $this->formErrors = [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            \Log::error('Erreur de validation dans update : ', $e->errors());
            $this->formErrors = $e->errors();
            $this->showNotification('Veuillez corriger les erreurs dans le formulaire.', 'error');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans update : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la mise à jour du plat : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowActivateModal($id)
    {
        try {
            \Log::info('functionShowActivateModal appelé avec ID: ' . $id);
            $this->menuElement = Menu::findOrFail($id);
            $this->showActivateModal = true;
            $this->dispatch('activate-modal-opened', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans functionShowActivateModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'activation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeactivateModal($id)
    {
        try {
            \Log::info('functionShowDeactivateModal appelé avec ID: ' . $id);
            $this->menuElement = Menu::findOrFail($id);
            $this->showDeactivateModal = true;
            $this->dispatch('deactivate-modal-opened', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans functionShowDeactivateModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de désactivation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeleteModal($id)
    {
        try {
            \Log::info('functionShowDeleteModal appelé avec ID: ' . $id);
            $this->menuElement = Menu::findOrFail($id);
            $this->showDeleteModal = true;
            $this->dispatch('delete-modal-opened', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans functionShowDeleteModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de suppression : ' . $e->getMessage(), 'error');
        }
    }

    public function activateMenu($id)
    {
        try {
            \Log::info('activateMenu appelé avec ID: ' . $id);
            $menu = Menu::findOrFail($id);
            $menu->update(['status' => 'Success']);
            $menuDay = $menu->menuDays->first();
            if ($menuDay) {
                $menuDay->update(['status' => 'Success']);
            }
            \Log::info('Plat activé avec succès');
            $this->showActivateModal = false;
            $this->menuElement = null;
            $this->loadData();
            $this->showNotification('Plat activé avec succès !', 'success');
            $this->dispatch('menu-activated', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans activateMenu : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'activation du plat : ' . $e->getMessage(), 'error');
        }
    }

    public function deactivateMenu($id)
    {
        try {
            \Log::info('deactivateMenu appelé avec ID: ' . $id);
            $menu = Menu::findOrFail($id);
            $menu->update(['status' => 'pending']);
            $menuDay = $menu->menuDays->first();
            if ($menuDay) {
                $menuDay->update(['status' => 'pending']);
            }
            \Log::info('Plat désactivé avec succès');
            $this->showDeactivateModal = false;
            $this->menuElement = null;
            $this->loadData();
            $this->showNotification('Plat désactivé avec succès !', 'success');
            $this->dispatch('menu-deactivated', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans deactivateMenu : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la désactivation du plat : ' . $e->getMessage(), 'error');
        }
    }

    public function deleteMenu($id)
    {
        try {
            \Log::info('deleteMenu appelé avec ID: ' . $id);
            $menu = Menu::findOrFail($id);
            $menu->update(['status' => 'failed']);
            $menuDay = $menu->menuDays->first();
            if ($menuDay) {
                $menuDay->update(['status' => 'failed']);
            }
            \Log::info('Plat marqué comme supprimé avec succès');
            $this->showDeleteModal = false;
            $this->menuElement = null;
            $this->loadData();
            $this->showNotification('Plat supprimé avec succès !', 'success');
            $this->dispatch('menu-deleted', id: $id);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur dans deleteMenu : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la suppression du plat : ' . $e->getMessage(), 'error');
        }
    }

    public function closeModal()
    {
        \Log::info('closeModal appelé');
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->menuElement = null;
        $this->resetForm();
        $this->formErrors = [];
    }

    private function resetForm()
    {
        \Log::info('resetForm appelé');
        $this->reset(['name', 'status', 'type', 'categorie', 'day', 'hours', 'specialday', 'menu_day_type', 'price']); // Ajout de price dans reset
    }

    private function showNotification($message, $type)
    {
        \Log::info('showNotification appelé', ['message' => $message, 'type' => $type]);
        $this->notificationMessage = $message;
        $this->notificationType = $type;
        $this->showNotification = true;
        $this->dispatch('auto-hide-notification');
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Plats</h1>
                    <p class="text-gray-500">Créez, modifiez, activez, désactivez ou supprimez des plats et configurez le menu du jour</p>
                </div>
            </div>

            <!-- Bouton Ajouter un plat -->
            <div class="mb-12 text-center">
                <button wire:click="functionShowAddModal"
                    class="bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    x-on:click="console.log('Bouton Ajouter un plat cliqué')">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un plat
                </button>
            </div>

            <!-- Ligne 1 : Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Total Plats</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ $menus->count() }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Plats Actifs</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $menus->where('status', 'Success')->count() }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Commandes Actives</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ $orders->count() }}</p>
                </div>
            </div>

            <!-- Ligne 2 : Liste des Plats et Menu du Jour -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12 mt-6">
                <!-- Liste des Plats -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Plats</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Nom</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Type</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Catégorie</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Prix</th> <!-- Ajout de la colonne Prix -->
                                    <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menus as $menu)
                                    <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <td class="p-4 rounded-l-lg">{{ $menu->name ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $menu->type ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $menu->categorie ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $menu->price ?? 0 }} FCFA</td> <!-- Affichage du prix -->
                                        <td class="p-4">
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                                {{ $menu->status === 'Success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $menu->status === 'Success' ? 'Actif' : 'En attente' }}
                                            </span>
                                        </td>
                                        <td class="p-4 rounded-r-lg flex space-x-2">
                                            @if ($menu->status === 'pending')
                                                <button wire:click="functionShowActivateModal({{ $menu->id }})"
                                                    class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                                                    x-on:click="console.log('Bouton Activer cliqué pour plat ID: {{ $menu->id }}')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Activer
                                                </button>
                                            @else
                                                <button wire:click="functionShowDeactivateModal({{ $menu->id }})"
                                                    class="inline-flex items-center px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200"
                                                    x-on:click="console.log('Bouton Désactiver cliqué pour plat ID: {{ $menu->id }}')">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Désactiver
                                                </button>
                                            @endif
                                            <button wire:click="functionShowEditModal({{ $menu->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200"
                                                x-on:click="console.log('Bouton Modifier cliqué pour plat ID: {{ $menu->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Modifier
                                            </button>
                                            <button wire:click="functionShowDeleteModal({{ $menu->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                                                x-on:click="console.log('Bouton Supprimer cliqué pour plat ID: {{ $menu->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Liste du Menu du Jour -->
                <div class="bg-white rounded-2xl shadow-lg p-8 ">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Menu du Jour</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Plat</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Jour</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Heure</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Jour Spécial</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Type</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Prix</th> <!-- Ajout de la colonne Prix -->
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menuDays as $menuDay)
                                    <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <td class="p-4 rounded-l-lg">{{ $menuDay->menu->name ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $menuDay->day ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $menuDay->hours ? \Carbon\Carbon::parse($menuDay->hours)->format('H:i') : 'Non défini' }}</td>
                                        <td class="p-4">{{ $menuDay->specialday ? \Carbon\Carbon::parse($menuDay->specialday)->format('d/m/Y') : 'Non défini' }}</td>
                                        <td class="p-4">{{ $menuDay->type === 'one' ? 'Un jour' : ($menuDay->type === 'allDay' ? 'Tous les lundis' : 'Non défini') }}</td>
                                        <td class="p-4">{{ $menuDay->menu->price ?? 0 }} FCFA</td> <!-- Affichage du prix -->
                                        <td class="p-4 rounded-r-lg">
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                Actif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ligne 3 : Liste des Commandes -->
            <div class="grid grid-cols-1 mt-6">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Commandes</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-2">
                            <thead>
                                <tr class="bg-gray-100 rounded-lg">
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Code</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Plat</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Utilisateur</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Heure</th>
                                    <th class="p-4 text-sm font-medium text-gray-600">Prix</th> <!-- Ajout de la colonne Prix -->
                                    <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <td class="p-4 rounded-l-lg">{{ $order->code ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $order->menuDay->menu->name ?? 'Non défini' }}</td>
                                        <td class="p-4">{{ $order->user->name ?? 'Utilisateur inconnu' }}</td>
                                        <td class="p-4">{{ $order->hours ? \Carbon\Carbon::parse($order->hours)->format('H:i') : 'Non défini' }}</td>
                                        <td class="p-4">{{ $order->menuDay->menu->price ?? 0 }} FCFA</td> <!-- Affichage du prix -->
                                        <td class="p-4 rounded-r-lg">
                                            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                                {{ $order->status === 'Success' ? 'bg-green-100 text-green-800' : ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ $order->status === 'Success' ? 'Actif' : ($order->status === 'pending' ? 'En attente' : 'Échoué') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                    x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4">
                    <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} p-4 rounded-xl shadow-md border-l-4 {{ $notificationType === 'success' ? 'border-green-500' : 'border-red-500' }} animate-pulse">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif

            <!-- Modal Ajouter Plat -->
            @if ($showAddModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Ajouter un Plat</h2>
                        @if (!empty($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                Veuillez corriger les erreurs suivantes :
                                <ul class="list-disc ml-5">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ($errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form wire:submit.prevent="save">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom du Plat</label>
                                    <input type="text" wire:model.defer="name" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model.defer="status" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un statut</option>
                                        <option value="Success">Actif</option>
                                        <option value="pending">En attente</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select wire:model.defer="type" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un type</option>
                                        <option value="dessert">Dessert</option>
                                        <option value="repas">Repas</option>
                                        <option value="complement">Complément</option>
                                    </select>
                                    @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                                    <select wire:model.defer="categorie" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner une catégorie</option>
                                        <option value="dejeuner">Déjeuner</option>
                                        <option value="diner">Dîner</option>
                                        <option value="souper">Souper</option>
                                    </select>
                                    @error('categorie') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prix</label> <!-- Ajout du champ Prix -->
                                    <input type="number" wire:model.defer="price" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200" min="0">
                                    @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jour</label>
                                    <select wire:model.defer="day" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un jour</option>
                                        <option value="Monday">Lundi</option>
                                        <option value="Tuesday">Mardi</option>
                                        <option value="Wednesday">Mercredi</option>
                                        <option value="Thursday">Jeudi</option>
                                        <option value="Friday">Vendredi</option>
                                        <option value="Saturday">Samedi</option>
                                        <option value="Sunday">Dimanche</option>
                                    </select>
                                    @error('day') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Heure</label>
                                    <input type="time" wire:model.defer="hours" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jour Spécial</label>
                                    <input type="date" wire:model.defer="specialday" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @error('specialday') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type de Disponibilité</label>
                                    <select wire:model.defer="menu_day_type" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un type</option>
                                        <option value="one">Un jour</option>
                                        <option value="allDay">Toutes les semaines</option>
                                    </select>
                                    @error('menu_day_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                                    x-on:click="console.log('Bouton Enregistrer cliqué')">
                                    Enregistrer
                                </button>
                                <button wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Modifier Plat -->
            @if ($showEditModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out scale-100 hover:scale-[1.02]">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Modifier un Plat</h2>
                        @if (!empty($formErrors))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                                Veuillez corriger les erreurs suivantes :
                                <ul class="list-disc ml-5">
                                    @foreach ($formErrors as $field => $errors)
                                        @foreach ($errors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form wire:submit.prevent="update">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom du Plat</label>
                                    <input type="text" wire:model.defer="name" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                                    <select wire:model.defer="status" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un statut</option>
                                        <option value="Success">Actif</option>
                                        <option value="pending">En attente</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select wire:model.defer="type" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un type</option>
                                        <option value="dessert">Dessert</option>
                                        <option value="repas">Repas</option>
                                        <option value="complement">Complément</option>
                                    </select>
                                    @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Catégorie</label>
                                    <select wire:model.defer="categorie" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner une catégorie</option>
                                        <option value="dejeuner">Déjeuner</option>
                                        <option value="diner">Dîner</option>
                                        <option value="souper">Souper</option>
                                    </select>
                                    @error('categorie') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prix</label> <!-- Ajout du champ Prix -->
                                    <input type="number" wire:model.defer="price" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200" min="0">
                                    @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jour</label>
                                    <select wire:model.defer="day" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un jour</option>
                                        <option value="Monday">Lundi</option>
                                        <option value="Tuesday">Mardi</option>
                                        <option value="Wednesday">Mercredi</option>
                                        <option value="Thursday">Jeudi</option>
                                        <option value="Friday">Vendredi</option>
                                        <option value="Saturday">Samedi</option>
                                        <option value="Sunday">Dimanche</option>
                                    </select>
                                    @error('day') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Heure</label>
                                    <input type="time" wire:model.defer="hours" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @error('hours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jour Spécial</label>
                                    <input type="date" wire:model.defer="specialday" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                    @error('specialday') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type de Disponibilité</label>
                                    <select wire:model.defer="menu_day_type" class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                                        <option value="">Sélectionner un type</option>
                                        <option value="one">Un jour</option>
                                        <option value="allDay">Tous les lundis</option>
                                    </select>
                                    @error('menu_day_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end space-x-4">
                                <button type="submit"
                                    class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                                    x-on:click="console.log('Bouton Mettre à jour cliqué')">
                                    Mettre à jour
                                </button>
                                <button wire:click="closeModal"
                                    class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Modal Activer -->
            @if ($showActivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Activer le plat</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous activer le plat {{ $menuElement->name ?? 'N/A' }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="activateMenu({{ $menuElement->id }})"
                                class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Activer) cliqué pour plat ID: {{ $menuElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Désactiver -->
            @if ($showDeactivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Désactiver le plat</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous désactiver le plat {{ $menuElement->name ?? 'N/A' }} ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deactivateMenu({{ $menuElement->id }})"
                                class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Désactiver) cliqué pour plat ID: {{ $menuElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer -->
            @if ($showDeleteModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer le plat</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous supprimer le plat {{ $menuElement->name ?? 'N/A' }} ? Cette action est irréversible.</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deleteMenu({{ $menuElement->id }})"
                                class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Supprimer) cliqué pour plat ID: {{ $menuElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <script>
            document.addEventListener('livewire:load', function () {
                Livewire.on('edit-modal-opened', (data) => {
                    console.log('Modal d\'édition ouvert pour ID:', data.id);
                });
                Livewire.on('activate-modal-opened', (data) => {
                    console.log('Modal d\'activation ouvert pour ID:', data.id);
                });
                Livewire.on('deactivate-modal-opened', (data) => {
                    console.log('Modal de désactivation ouvert pour ID:', data.id);
                });
                Livewire.on('delete-modal-opened', (data) => {
                    console.log('Modal de suppression ouvert pour ID:', data.id);
                });
                Livewire.on('menu-activated', (data) => {
                    console.log('Plat activé pour ID:', data.id);
                });
                Livewire.on('menu-deactivated', (data) => {
                    console.log('Plat désactivé pour ID:', data.id);
                });
                Livewire.on('menu-deleted', (data) => {
                    console.log('Plat supprimé pour ID:', data.id);
                });
                Livewire.on('auto-hide-notification', () => {
                    setTimeout(() => {
                        @this.set('showNotification', false);
                    }, 3000);
                });
            });
        </script>
    @endvolt
</x-layouts.app>

