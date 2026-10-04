
<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Menu;
use App\Models\MenuDay;
use App\Models\Commande;
use Carbon\Carbon;
use Illuminate\Support\Str;

name('commande-plats.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $menuDays;
    public $userOrders;

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        $currentDay = Carbon::now()->format('l'); // Exemple : "Sunday"
        $currentDate = Carbon::now()->toDateString(); // Exemple : "2025-08-24"

        $this->menuDays = MenuDay::with('menu')
            ->where('status', 'Success')
            ->where(function ($query) use ($currentDay, $currentDate) {
                $query->where(function ($q) use ($currentDay) {
                    $q->where('day', $currentDay)
                      ->where('type', 'allDay');
                })->orWhere(function ($q) use ($currentDate) {
                    $q->where('specialday', $currentDate)
                      ->where('type', 'one');
                });
            })
            ->get();

        $this->userOrders = Commande::with(['menuDay', 'menuDay.menu', 'user'])
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'failed')
            ->get();
    }

    public function placeOrder($menuId, $price)
    {
        $code = Str::random(8); // Génère un code unique (exemple : "x7k9p2m4")
        Commande::create([
            'user_id' => auth()->id(),
            'menuDay_id' => $menuId,
            'code' => $code,
            'price' => $price,
            'hours' => Carbon::now()->format('H:i'),
            'status' => 'pending',
        ]);

        $this->loadData();
        session()->flash('message', 'Commande passée avec succès ! Code : ' . $code);
    }

    public function cancelOrder($orderId)
    {
        $order = Commande::findOrFail($orderId);
        if ($order->user_id === auth()->id() && $order->status === 'pending') {
            $order->update(['status' => 'failed']);
            $this->loadData();
            session()->flash('message', 'Commande annulée avec succès ! Code : ' . $order->code);
        }
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Commande de Plats</h1>
                    <p class="text-gray-500">Choisissez parmi notre sélection de plats disponibles pour une expérience culinaire savoureuse.</p>
                </div>
            </div>

            @if (session('message'))
                <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6">
                    {{ session('message') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Plats Disponibles Aujourd'hui</h2>
                <div class="space-y-6">
                    @forelse ($menuDays as $menuDay)
                        <div class="border-l-4 border-indigo-600 pl-4">
                            <h3 class="text-lg font-medium text-gray-800">{{ $menuDay->menu->name ?? 'Non défini' }}</h3>
                            <p class="text-gray-600">{{ $menuDay->menu->description ?? 'Aucune description disponible' }}</p>
                            <span class="text-sm text-gray-500">Prix : {{ $menuDay->menu->price ?? 0 }} FCFA</span>
                            <div class="mt-2">
                                <button wire:click="placeOrder({{ $menuDay->id }}, {{ $menuDay->menu->price ?? 0 }})"
                                    class="order-btn bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-200"
                                    data-plat="{{ $menuDay->menu->name }}"
                                    data-prix="{{ $menuDay->menu->price }}">
                                    Commander maintenant
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-600">Aucun plat disponible aujourd'hui.</p>
                    @endforelse
                </div>
                <div class="mt-8 flex justify-end">
                    <a href="#" class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les plats</a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Mes Commandes</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Code</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Plat</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Prix</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Heure</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($userOrders as $order)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $order->code ?? 'N/A' }}</td>
                                    <td class="p-4">{{ $order->menuDay->menu->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $order->price }} FCFA</td>
                                    <td class="p-4">{{ $order->hours ?? 'N/A' }}</td>
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                            {{ $order->status === 'Success' ? 'bg-green-100 text-green-800' : ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $order->status === 'Success' ? 'Actif' : ($order->status === 'pending' ? 'En attente' : 'Annulé') }}
                                        </span>
                                    </td>
                                    <td class="p-4 rounded-r-lg">
                                        @if ($order->status === 'pending')
                                            <button wire:click="cancelOrder({{ $order->id }})"
                                                class="bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition duration-200">
                                                Annuler
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-gray-600">Aucune commande pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

