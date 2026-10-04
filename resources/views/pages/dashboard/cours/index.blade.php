
<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\Cours;

name('cours.index');
middleware(['auth', 'verified']);

new class extends Component {
    public $courses;
    public $calendarEvents;

    public function mount()
    {
        \Log::info('mount appelé pour cours.index à ' . now()->toDateTimeString());
        $user = auth()->user();
        \Log::info('Utilisateur chargé', [
            'user_id' => $user->id,
            'specialite_id' => $user->specialite_id,
        ]);

        // Filtrer les cours par specialite_id et status='Success'
        $this->courses = Cours::where('specialite_id', $user->specialite_id)
            ->where('status', 'Success')
            ->with(['responsable' => function ($query) {
                $query->where('role', 'enseignant');
            }, 'filiere', 'specialite'])
            ->get();

        // Générer les événements pour le calendrier
        $this->calendarEvents = $this->courses->map(function ($course) {
            try {
                return [
                    'title' => $course->name,
                    'start' => \Carbon\Carbon::parse($course->start)->toIso8601String(),
                    'end' => \Carbon\Carbon::parse($course->end)->toIso8601String(),
                    'backgroundColor' => '#4f46e5',
                    'borderColor' => '#4f46e5',
                ];
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la génération de l\'événement pour le cours ID: ' . ($course->id ?? 'inconnu'), ['error' => $e->getMessage()]);
                return null;
            }
        })->filter()->toArray();

        \Log::info('Cours et événements chargés', [
            'courses_count' => $this->courses->count(),
            'events_count' => count($this->calendarEvents),
        ]);
    }

    public function showCourseDetail($id)
    {
        try {
            \Log::info('showCourseDetail appelé avec ID: ' . $id . ' à ' . now()->toDateTimeString());
            $course = Cours::with(['responsable' => function ($query) {
                $query->where('role', 'enseignant');
            }, 'filiere', 'specialite'])->findOrFail($id);

            $details = [
                'name' => $course->name ?? 'Non défini',
                'teacher' => $course->responsable ? $course->responsable->name : 'Non défini',
                'filiere' => optional($course->filiere)->name ?? 'Non défini',
                'specialite' => optional($course->specialite)->name ?? 'Non défini',
                'start' => $course->start ? \Carbon\Carbon::parse($course->start)->translatedFormat('d/m/Y H:i') : 'Non défini',
                'end' => $course->end ? \Carbon\Carbon::parse($course->end)->translatedFormat('d/m/Y H:i') : 'Non défini',
                'credit' => $course->credit ?? 'Non défini',
                'status' => $course->status ?? 'Non défini',
            ];

            \Log::info('Détails du cours dispatchés', $details);

            $this->dispatch('show-course-detail', $details)->then(function () {
                \Log::info('Événement show-course-detail dispatché avec succès');
            })->catch(function ($error) {
                \Log::error('Erreur lors du dispatch de l\'événement : ' . $error->getMessage());
            });
        } catch (\Exception $e) {
            \Log::error('Erreur dans showCourseDetail : ' . $e->getMessage());
            $this->dispatch('show-notification', message: 'Erreur lors de l\'affichage des détails : ' . $e->getMessage(), type: 'error');
        }
    }
};
?>

<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Mes Cours & Calendrier</h1>
                    <p class="text-gray-500">Planifiez et suivez vos cours avec une vue d'ensemble claire.</p>
                </div>
            </div>

            <!-- Notification -->
            <div x-data="{ showNotification: false, notificationMessage: '', notificationType: '' }" x-on:show-notification.window="showNotification = true; notificationMessage = $event.detail.message; notificationType = $event.detail.type; setTimeout(() => showNotification = false, 3000)">
                <div x-show="showNotification" class="fixed top-6 right-6 z-50 max-w-sm w-full" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4">
                    <div :class="notificationType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="p-4 rounded-xl shadow-md border-l-4" :class="notificationType === 'success' ? 'border-green-500' : 'border-red-500'">
                        <span x-text="notificationMessage"></span>
                    </div>
                </div>
            </div>

      
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Cours</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Cours</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Enseignant</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Horaire</th>
                                {{-- <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courses as $course)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $course->name ?? 'Non défini' }}</td>
                                    <td class="p-4">{{ $course->responsable->name ?? 'Non défini' }}</td>
                                    <td class="p-4">

                                            {{ \Carbon\Carbon::parse($course->start)->translatedFormat('l H:i') }} - {{ \Carbon\Carbon::parse($course->end)->format('H:i') }}

                                    </td>

                                </tr>
                            @endforeach
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
                        events: @json($calendarEvents),
                        eventClick: function(info) {
                            alert('Événement : ' + info.event.title + '\nDébut : ' + info.event.start.toLocaleString('fr-FR') + '\nFin : ' + (info.event.end ? info.event.end.toLocaleString('fr-FR') : 'Non défini'));
                        }
                    });
                    calendar.render();
                });
            </script>
        </div>
    @endvolt
</x-layouts.app>

