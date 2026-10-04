
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('specialite.etudiants');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Liste des Étudiants</h1>
                <p class="mt-2 text-lg text-gray-500">Consultez les informations des étudiants de la spécialité : Développement Logiciel</p>
            </header>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Étudiants Inscrits</h3>
                    <p class="text-3xl font-bold text-indigo-600">120</p>
                    <p class="text-sm text-gray-500">Dans la spécialité</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Étudiants Actifs</h3>
                    <p class="text-3xl font-bold text-indigo-600">115</p>
                    <p class="text-sm text-gray-500">Semestre 1, 2025</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Taux de Présence</h3>
                    <p class="text-3xl font-bold text-indigo-600">92%</p>
                    <p class="text-sm text-gray-500">Moyenne spéciale (2025)</p>
                </div>
            </div>

            <!-- Liste des Étudiants -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Étudiants Inscrits</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Nom</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Matricule</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Contact</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">ISM2023-001</td>
                                <td class="p-4">marie.ngo@ismndazoa.cm</td>
                                <td class="p-4 text-green-600">Inscrit</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Luc Bengono</td>
                                <td class="p-4">ISM2023-002</td>
                                <td class="p-4">luc.bengono@ismndazoa.cm</td>
                                <td class="p-4 text-green-600">Inscrit</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Aline Essomba</td>
                                <td class="p-4">ISM2023-003</td>
                                <td class="p-4">aline.essomba@ismndazoa.cm</td>
                                <td class="p-4 text-green-600">Inscrit</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Paul Mvondo</td>
                                <td class="p-4">ISM2023-004</td>
                                <td class="p-4">paul.mvondo@ismndazoa.cm</td>
                                <td class="p-4 text-yellow-600">En attente</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Détails</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Exporter la liste (PDF)</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
