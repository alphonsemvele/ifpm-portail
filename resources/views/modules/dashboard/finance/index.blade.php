
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('finance.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Gestion Financière</h1>
                <p class="mt-2 text-lg text-gray-500">Suivez et gérez vos frais de scolarité en toute transparence.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">État des Paiements</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Montant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Type</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">01/06/2025</td>
                                <td class="p-4">500,000 FCFA</td>
                                <td class="p-4">Frais de scolarité</td>
                                <td class="p-4">
                                    <span class="text-green-600">Payé</span>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">01/09/2025</td>
                                <td class="p-4">250,000 FCFA</td>
                                <td class="p-4">Frais annexes</td>
                                <td class="p-4">
                                    <span class="text-red-600">En attente</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Effectuer un Paiement</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Montant</label>
                        <input type="number" value="0" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Méthode de Paiement</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="mobile">Mobile Money</option>
                            <option value="bank">Virement Bancaire</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Effectuer le paiement</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

