
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('bibliotheque.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Gestion de la Bibliothèque</h1>
                <p class="mt-2 text-lg text-gray-500">Consultez le catalogue et gérez vos emprunts de livres.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Catalogue des Livres</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Auteur</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Disponibilité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Introduction à l'Algorithmique</td>
                                <td class="p-4">Thomas H. Cormen</td>
                                <td class="p-4 text-green-600">Disponible</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Emprunter</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Bases de Données</td>
                                <td class="p-4">C.J. Date</td>
                                <td class="p-4 text-red-600">Emprunté</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Réserver</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Mes Emprunts</h2>
                <div class="space-y-4">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-gray-800">Introduction à l'Algorithmique - <span class="text-gray-600">Retour prévu le 15/08/2025</span></p>
                    </div>
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <p class="text-gray-800">Programmation Python - <span class="text-gray-600">Retour prévu le 20/08/2025</span></p>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les emprunts</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

