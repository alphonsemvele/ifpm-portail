
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('specialites.cours');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Cours par Spécialité</h1>
                <p class="mt-2 text-lg text-gray-500">Gérez les cours associés à chaque spécialité de la filière.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Cours de Développement Logiciel</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Enseignant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Crédits</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Algorithmique Avancée</td>
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">6</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                    <a href="#" class="text-indigo-600 hover:underline ml-2">Modifier</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Développement Web</td>
                                <td class="p-4">Pr. Jean Talla</td>
                                <td class="p-4">5</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                    <a href="#" class="text-indigo-600 hover:underline ml-2">Modifier</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Cours de Cybersécurité</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Enseignant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Crédits</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Sécurité des Réseaux</td>
                                <td class="p-4">Pr. Jean Talla</td>
                                <td class="p-4">4</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                    <a href="#" class="text-indigo-600 hover:underline ml-2">Modifier</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Ajouter un Cours</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom du Cours</label>
                        <input type="text" value="" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Spécialité</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="dev">Développement Logiciel</option>
                            <option value="cyber">Cybersécurité</option>
                            <option value="ia">Intelligence Artificielle</option>
                            <option value="reseaux">Réseaux et Télécommunications</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Enseignant</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="sophie">Dr. Sophie Mballa</option>
                            <option value="jean">Pr. Jean Talla</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Crédits</label>
                        <input type="number" value="0" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Ajouter le cours</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

