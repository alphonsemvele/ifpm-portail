
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('support-assistance.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Support & Assistance</h1>
                <p class="mt-2 text-lg text-gray-500">Obtenez de l'aide pour vos questions administratives et techniques.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Mes Tickets</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Numéro</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Sujet</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">TCK-2025-001</td>
                                <td class="p-4">Problème d'inscription</td>
                                <td class="p-4">05/07/2025</td>
                                <td class="p-4 text-green-600">Résolu</td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">TCK-2025-002</td>
                                <td class="p-4">Demande de relevé de notes</td>
                                <td class="p-4">07/07/2025</td>
                                <td class="p-4 text-yellow-600">En cours</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les tickets</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nouveau Ticket</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Sujet</label>
                        <input type="text" value="" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Description</label>
                        <textarea class="mt-1 w-full p-3 border border-gray-300 rounded-lg" rows="4"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Soumettre le ticket</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

