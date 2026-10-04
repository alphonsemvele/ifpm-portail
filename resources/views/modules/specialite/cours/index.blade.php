
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('specialite.cours');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Cours de la Spécialité</h1>
                <p class="mt-2 text-lg text-gray-500">Gérez les cours de la spécialité : Développement Logiciel</p>
            </header>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Cours Actifs</h3>
                    <p class="text-3xl font-bold text-indigo-600">8</p>
                    <p class="text-sm text-gray-500">Dans la spécialité</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Enseignants</h3>
                    <p class="text-3xl font-bold text-indigo-600">10</p>
                    <p class="text-sm text-gray-500">Affectés aux cours</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Heures Dispensées</h3>
                    <p class="text-3xl font-bold text-indigo-600">240</p>
                    <p class="text-sm text-gray-500">Semestre 1, 2025</p>
                </div>
            </div>

            <!-- Liste des Cours -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Cours Actifs</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Enseignant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Crédits</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Horaire</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Algorithmique Avancée</td>
                                <td class="p-4">Dr. Sophie Mballa</td>
                                <td class="p-4">6</td>
                                <td class="p-4">Lundi 08:00 - 10:00</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Développement Web</td>
                                <td class="p-4">Pr. Jean Talla</td>
                                <td class="p-4">5</td>
                                <td class="p-4">Mercredi 10:00 - 12:00</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Programmation Orientée Objet</td>
                                <td class="p-4">Dr. Amina Ngono</td>
                                <td class="p-4">4</td>
                                <td class="p-4">Vendredi 14:00 - 16:00</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

