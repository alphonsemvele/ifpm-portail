
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('specialite.presences');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Suivi des Présences</h1>
                <p class="mt-2 text-lg text-gray-500">Faites l'appel et gérez les présences des étudiants pour la spécialité : Développement Logiciel</p>
            </header>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Taux de Présence</h3>
                    <p class="text-3xl font-bold text-indigo-600">92%</p>
                    <p class="text-sm text-gray-500">Moyenne spéciale (2025)</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Séances Enregistrées</h3>
                    <p class="text-3xl font-bold text-indigo-600">45</p>
                    <p class="text-sm text-gray-500">Semestre 1, 2025</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Étudiants Suivis</h3>
                    <p class="text-3xl font-bold text-indigo-600">120</p>
                    <p class="text-sm text-gray-500">Dans la spécialité</p>
                </div>
            </div>

            <!-- Formulaire d'Appel -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Faire l'Appel</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Sélectionner un Cours</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="algo">Algorithmique Avancée</option>
                            <option value="web">Développement Web</option>
                            <option value="poo">Programmation Orientée Objet</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Date de la Séance</label>
                        <input type="date" value="2025-07-09" class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Étudiant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Matricule</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut de Présence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">ISM2023-001</td>
                                <td class="p-4">
                                    <select class="w-full p-2 border border-gray-300 rounded-lg">
                                        <option value="present" selected>Présent</option>
                                        <option value="absent">Absent</option>
                                        <option value="excused">Excusé</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Luc Bengono</td>
                                <td class="p-4">ISM2023-002</td>
                                <td class="p-4">
                                    <select class="w-full p-2 border border-gray-300 rounded-lg">
                                        <option value="present">Présent</option>
                                        <option value="absent" selected>Absent</option>
                                        <option value="excused">Excusé</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Aline Essomba</td>
                                <td class="p-4">ISM2023-003</td>
                                <td class="p-4">
                                    <select class="w-full p-2 border border-gray-300 rounded-lg">
                                        <option value="present" selected>Présent</option>
                                        <option value="absent">Absent</option>
                                        <option value="excused">Excusé</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Paul Mvondo</td>
                                <td class="p-4">ISM2023-004</td>
                                <td class="p-4">
                                    <select class="w-full p-2 border border-gray-300 rounded-lg">
                                        <option value="present">Présent</option>
                                        <option value="absent">Absent</option>
                                        <option value="excused" selected>Excusé</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Enregistrer l'appel</button>
                </div>
            </div>

            <!-- Historique des Présences -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Historique des Présences</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Filtrer par Cours</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="all">Tous les cours</option>
                            <option value="algo">Algorithmique Avancée</option>
                            <option value="web">Développement Web</option>
                            <option value="poo">Programmation Orientée Objet</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Étudiant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">Algorithmique Avancée</td>
                                <td class="p-4">07/07/2025</td>
                                <td class="p-4 text-green-600">Présent</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Luc Bengono</td>
                                <td class="p-4">Développement Web</td>
                                <td class="p-4">08/07/2025</td>
                                <td class="p-4 text-red-600">Absent</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Aline Essomba</td>
                                <td class="p-4">Programmation Orientée Objet</td>
                                <td class="p-4">09/07/2025</td>
                                <td class="p-4 text-green-600">Présent</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Exporter les présences (PDF)</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

