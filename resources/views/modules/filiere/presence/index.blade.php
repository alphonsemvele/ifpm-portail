
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('filiere.presences');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Suivi des Présences</h1>
                <p class="mt-2 text-lg text-gray-500">Consultez les statistiques de présence et d'absence par spécialité et par étudiant dans la filière.</p>
            </header>

            <!-- Statistiques Globales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Taux de Présence Global</h3>
                    <p class="text-3xl font-bold text-indigo-600">90%</p>
                    <p class="text-sm text-gray-500">Filière, Semestre 1, 2025</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Séances Enregistrées</h3>
                    <p class="text-3xl font-bold text-indigo-600">180</p>
                    <p class="text-sm text-gray-500">Toutes spécialités</p>
                </div>
                <div class="bg-indigo-50 rounded-xl shadow-lg p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-800">Étudiants Suivis</h3>
                    <p class="text-3xl font-bold text-indigo-600">350</p>
                    <p class="text-sm text-gray-500">Dans la filière</p>
                </div>
            </div>

            <!-- Graphique : Taux de Présence par Spécialité -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Taux de Présence par Spécialité</h2>
                <div class="h-96">
                    <canvas id="specialityPresenceChart"></canvas>

                </div>
            </div>

            <!-- Filtre par Spécialité -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Filtrer par Spécialité</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Sélectionner une Spécialité</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="all">Toutes les spécialités</option>
                            <option value="dev">Développement Logiciel</option>
                            <option value="cyber">Cybersécurité</option>
                            <option value="ia">Intelligence Artificielle</option>
                            <option value="reseaux">Réseaux et Télécommunications</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Statistiques par Étudiant -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Statistiques par Étudiant</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Étudiant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Matricule</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Spécialité</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Présences</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Absences</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Taux de Présence</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Marie Ngo</td>
                                <td class="p-4">ISM2023-001</td>
                                <td class="p-4">Développement Logiciel</td>
                                <td class="p-4">43</td>
                                <td class="p-4">2</td>
                                <td class="p-4 text-green-600">95.6%</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Jean Dupont</td>
                                <td class="p-4">ISM2023-002</td>
                                <td class="p-4">Cybersécurité</td>
                                <td class="p-4">40</td>
                                <td class="p-4">5</td>
                                <td class="p-4 text-yellow-600">88.9%</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Amina Essono</td>
                                <td class="p-4">ISM2023-003</td>
                                <td class="p-4">Intelligence Artificielle</td>
                                <td class="p-4">42</td>
                                <td class="p-4">3</td>
                                <td class="p-4 text-green-600">93.3%</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Paul Mvondo</td>
                                <td class="p-4">ISM2023-004</td>
                                <td class="p-4">Réseaux et Télécommunications</td>
                                <td class="p-4">41</td>
                                <td class="p-4">4</td>
                                <td class="p-4 text-yellow-600">91.1%</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Exporter les statistiques (PDF)</button>
                </div>
            </div>

            <!-- Graphique : Historique des Présences d'un Étudiant -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Historique des Présences (Étudiant : Marie Ngo)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Sélectionner un Étudiant</label>
                        <select class="mt-1 w-full p-3 border border-gray-300 rounded-lg">
                            <option value="marie-ngo">Marie Ngo</option>
                            <option value="jean-dupont">Jean Dupont</option>
                            <option value="amina-essono">Amina Essono</option>
                            <option value="paul-mvondo">Paul Mvondo</option>
                        </select>
                    </div>
                </div>
                <div class="h-96">
                    <canvas id="studentPresenceChart"></canvas>
                   
                </div>
            </div>

            <!-- Inclusion de Chart.js et initialisation des graphiques -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
            <script>
                // Graphique en donut : Taux de Présence par Spécialité
                const specialityPresenceCtx = document.getElementById('specialityPresenceChart').getContext('2d');
                new Chart(specialityPresenceCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Développement Logiciel', 'Cybersécurité', 'Intelligence Artificielle', 'Réseaux et Télécommunications'],
                        datasets: [{
                            label: 'Taux de Présence (%)',
                            data: [92, 88, 91, 89],
                            backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444'],
                            borderColor: ['#ffffff', '#ffffff', '#ffffff', '#ffffff'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Taux de Présence par Spécialité (2025)',
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                });

                // Graphique en barres : Historique des Présences d'un Étudiant
                const studentPresenceCtx = document.getElementById('studentPresenceChart').getContext('2d');
                new Chart(studentPresenceCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4', 'Semaine 5'],
                        datasets: [{
                            label: 'Présences',
                            data: [10, 8, 9, 10, 6],
                            backgroundColor: '#4f46e5',
                            borderColor: '#4f46e5',
                            borderWidth: 1
                        }, {
                            label: 'Absences',
                            data: [0, 2, 1, 0, 4],
                            backgroundColor: '#ef4444',
                            borderColor: '#ef4444',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Nombre de Séances'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Semaines (2025)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Historique des Présences de Marie Ngo (2025)',
                                font: {
                                    size: 16
                                }
                            }
                        }
                    }
                });
            </script>
        </div>
    @endvolt
</x-layouts.app>

