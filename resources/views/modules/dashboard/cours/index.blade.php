
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('cours.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Mes Cours & Calendrier</h1>
                <p class="mt-2 text-lg text-gray-500">Planifiez et suivez vos cours avec une vue d'ensemble claire.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Cours</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Enseignant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Horaire</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Algorithmique</td>
                                <td class="p-4">Dr. Marie Ndiaye</td>
                                <td class="p-4">Lundi 08:00 - 10:00</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 rounded-lg">
                                <td class="p-4">Base de Données</td>
                                <td class="p-4">Pr. Paul Biya</td>
                                <td class="p-4">Mercredi 10:00 - 12:00</td>
                                <td class="p-4">
                                    <a href="#" class="text-indigo-600 hover:underline">Voir détails</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Calendrier Académique</h2>
                <div id="calendar" class="w-full h-[600px] bg-gray-50 rounded-lg"></div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var calendarEl = document.getElementById('calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'fr',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        events: [
                            {
                                title: 'Cours Algorithmique',
                                start: '2025-07-14T08:00:00',
                                end: '2025-07-14T10:00:00',
                                backgroundColor: '#4f46e5',
                                borderColor: '#4f46e5'
                            },
                            {
                                title: 'Cours Base de Données',
                                start: '2025-07-16T10:00:00',
                                end: '2025-07-16T12:00:00',
                                backgroundColor: '#4f46e5',
                                borderColor: '#4f46e5'
                            },
                            {
                                title: 'Examen Algorithmique',
                                start: '2025-12-10T08:00:00',
                                end: '2025-12-10T10:00:00',
                                backgroundColor: '#e11d48',
                                borderColor: '#e11d48'
                            }
                        ],
                        eventClick: function(info) {
                            alert('Événement : ' + info.event.title + '\nDate : ' + info.event.start.toLocaleString('fr-FR'));
                        }
                    });
                    calendar.render();
                });
            </script>
        </div>
    @endvolt
</x-layouts.app>

