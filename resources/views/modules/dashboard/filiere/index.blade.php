
<?php
use function Laravel\Folio\{name,middleware};
use Livewire\Volt\Component;
name('filiere.index');
middleware(['auth','verified'])
?>
<x-layouts.app>
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <header class="mb-10">
                <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight">Gestion des Filières</h1>
                <p class="mt-2 text-lg text-gray-500">Explorez les détails de votre filière et ses modules associés.</p>
            </header>

            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Détails de la Filière</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom de la Filière</label>
                        <input type="text" value="Informatique" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Responsable</label>
                        <input type="text" value="Dr. Sophie Mballa" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nombre d'étudiants</label>
                        <input type="text" value="120" class="mt-1 w-full p-3 border border-gray-300 rounded-lg bg-gray-50" disabled>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Modules de la Filière</h2>
                <ul class="space-y-4">
                    <li class="border-l-4 border-indigo-600 pl-4">
                        Algorithmique Avancée - <span class="text-gray-600">6 crédits</span>
                    </li>
                    <li class="border-l-4 border-indigo-600 pl-4">
                        Gestion de Bases de Données - <span class="text-gray-600">4 crédits</span>
                    </li>
                    <li class="border-l-4 border-indigo-600 pl-4">
                        Développement Web - <span class="text-gray-600">5 crédits</span>
                    </li>
                </ul>
            </div>
        </div>
    @endvolt
</x-layouts.app>
```
