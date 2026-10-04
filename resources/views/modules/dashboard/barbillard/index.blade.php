
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('barbillard.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Barbillard Numérique</h1>
                <p class="mt-2 text-lg text-gray-500">Restez informé des dernières annonces et communications officielles.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Annonces Récentes</h2>
                <div class="space-y-6">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <h3 class="text-lg font-medium text-gray-800">Rentrée Académique 2025</h3>
                        <p class="text-gray-600">La rentrée académique est prévue pour le 15 septembre 2025. Assurez-vous de finaliser vos inscriptions.</p>
                        <span class="text-sm text-gray-500">Publié le 01/07/2025</span>
                    </div>
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <h3 class="text-lg font-medium text-gray-800">Examen Semestriel</h3>
                        <p class="text-gray-600">Les examens du premier semestre débuteront le 10 décembre 2025.</p>
                        <span class="text-sm text-gray-500">Publié le 20/06/2025</span>
                    </div>
                </div>
                <div class="mt-8 flex justify-center">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Charger plus d'annonces</button>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>

