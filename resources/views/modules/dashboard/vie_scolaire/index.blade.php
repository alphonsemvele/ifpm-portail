
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('vie-etudiante.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Vie Étudiante</h1>
                <p class="mt-2 text-lg text-gray-500">Participez aux activités et événements pour enrichir votre expérience universitaire.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Événements à Venir</h2>
                <div class="space-y-6">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <h3 class="text-lg font-medium text-gray-800">Journée Portes Ouvertes</h3>
                        <p class="text-gray-600">Découvrez les clubs et associations le 20/09/2025 à l'Amphi Central.</p>
                        <span class="text-sm text-gray-500">Publié le 01/07/2025</span>
                    </div>
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <h3 class="text-lg font-medium text-gray-800">Tournoi de Football</h3>
                        <p class="text-gray-600">Rejoignez le tournoi inter-filières le 15/10/2025 au Stade Universitaire.</p>
                        <span class="text-sm text-gray-500">Publié le 10/07/2025</span>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir tous les événements</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Clubs et Associations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800">Club Informatique</h3>
                        <p class="text-gray-600">Participez à des projets de codage et des hackathons.</p>
                        <a href="#" class="text-indigo-600 hover:underline">Rejoindre</a>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800">Club Culturel</h3>
                        <p class="text-gray-600">Organisez des événements culturels et artistiques.</p>
                        <a href="#" class="text-indigo-600 hover:underline">Rejoindre</a>
                    </div>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

