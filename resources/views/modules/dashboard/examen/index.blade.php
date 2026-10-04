
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('examens.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Gestion des Examens</h1>
                <p class="mt-2 text-lg text-gray-500">Consultez les plannings et résultats de vos examens.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Planning des Examens</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Salle</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Algorithmique</td>
                                <td class="p-4">10/12/2025</td>
                                <td class="p-4">Amphi A1</td>
                                <td class="p-4">08:00 - 10:00</td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Base de Données</td>
                                <td class="p-4">12/12/2025</td>
                                <td class="p-4">Salle B2</td>
                                <td class="p-4">14:00 - 16:00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Résultats des Examens</h2>
                <div class="space-y-4">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-gray-800">Algorithmique - <span class="text-gray-600">15/20 (Semestre 1, 2024)</span></p>
                    </div>
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-gray-800">Base de Données - <span class="text-gray-600">12/20 (Semestre 1, 2024)</span></p>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les résultats</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

