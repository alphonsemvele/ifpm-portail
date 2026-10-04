<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('gestion-chambres.index');
middleware(['auth','verified'])
?>
<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Chambres</h1>
                    <p class="text-gray-500">Réservez une chambre sur le campus et gérez votre hébergement en toute simplicité.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Disponibilité des Chambres</h2>
                <div class="space-y-6">
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <h3 class="text-lg font-medium text-gray-800">Chambres Simples</h3>
                        <p class="text-gray-600">10 chambres simples disponibles pour l'année académique 2025-2026. Réservez avant le 30/09/2025.</p>
                        <span class="text-sm text-gray-500">Mis à jour le 01/08/2025</span>
                    </div>
                    <div class="border-l-4 border-indigo-600 pl-4">
                        <h3 class="text-lg font-medium text-gray-800">Chambres Partagées</h3>
                        <p class="text-gray-600">20 chambres partagées disponibles avec commodités partagées. Soumettez votre demande avant le 15/10/2025.</p>
                        <span class="text-sm text-gray-500">Mis à jour le 01/08/2025</span>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <button class="bg-indigo-600 text-white py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-200">Voir toutes les disponibilités</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Services d'Hébergement</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800">Demande de Réservation</h3>
                        <p class="text-gray-600">Soumettez une demande pour réserver une chambre sur le campus.</p>
                        <a href="#" class="text-indigo-600 hover:underline">Faire une demande</a>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-medium text-gray-800">Support Hébergement</h3>
                        <p class="text-gray-600">Contactez notre équipe pour toute question ou problème lié à votre hébergement.</p>
                        <a href="#" class="text-indigo-600 hover:underline">Contacter le support</a>
                    </div>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>
