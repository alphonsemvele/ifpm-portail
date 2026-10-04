
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('admin.finances');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-12 text-center">
                <h1 class="text-4xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">Gestion Financière</h1>
                <p class="mt-3 text-lg text-gray-600">Consultez les statistiques financières et les détails des transactions.</p>
            </header>

            <!-- Statistiques Financières -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-2xl shadow-xl p-6 text-center">
                    <h3 class="text-lg font-semibold text-white">Total des Recettes</h3>
                    <p class="text-3xl font-bold text-black">120 000 000 FCFA</p>
                    <p class="text-sm text-green-100">Semestre 1, 2025</p>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-2xl shadow-xl p-6 text-center">
                    <h3 class="text-lg font-semibold text-white">Dépenses</h3>
                    <p class="text-3xl font-bold text-black">45 000 000 FCFA</p>
                    <p class="text-sm text-red-100">Semestre 1, 2025</p>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-xl p-6 text-center">
                    <h3 class="text-lg font-semibold text-white">Transactions</h3>
                    <p class="text-3xl font-bold text-white">450</p>
                    <p class="text-sm text-blue-100">Semestre 1, 2025</p>
                </div>
            </div>

            <!-- Liste des Transactions -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Détails des Transactions</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Type</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Montant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Émetteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">09/07/2025</td>
                                <td class="p-4">Paiement Frais</td>
                                <td class="p-4">500 000 FCFA</td>
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">Frais de scolarité Semestre 1</td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">08/07/2025</td>
                                <td class="p-4">Dépense</td>
                                <td class="p-4">1 000 000 FCFA</td>
                                <td class="p-4">Administration</td>
                                <td class="p-4">Achat de matériel informatique</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300">Exporter (PDF)</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

