@php abort_unless(isset($cycles) || isset($filieres) || isset($specialites), 404) @endphp
@fragment('cycle')
    @if(isset($cycles) && $cycles->isNotEmpty())
    <div>
        <label for="cycle_id" class="block mb-2 text-sm font-medium text-gray-900">Cycle <b class="text-red-500">*</b></label>
        <select id="cycle_id" name="cycle_id" hx-get='/fetch-item/cycle' hx-target='#filiere' hx-swap='innerHTML' hx-trigger='change'
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            required>
            <option value="">Choisir un cycle</option>
            @foreach ($cycles as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
@endfragment

@fragment('filiere')
    <div>
        <label for="filiere_id" class="block mb-2 text-sm font-medium text-gray-900">Filière <b class="text-red-500">*</b></label>
        <select id="filiere_id" name="filiere_id" hx-get="/fetch-item/filiere" hx-target="#specialite" hx-swap="innerHTML" hx-trigger="change"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            required>
            <option value="">Choisir une filière</option>
            @foreach (($filieres ?? []) as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach
        </select>
    </div>
@endfragment

@fragment('specialite')
    <div>
        <label for="specialite_id" class="block mb-2 text-sm font-medium text-gray-900">Spécialité <b class="text-red-500">*</b></label>
        <select id="specialite_id" name="specialite_id"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            required>
            <option value="">Choisir une spécialité</option>
            @foreach (($specialites ?? []) as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach
        </select>
    </div>
@endfragment

@fragment('departement')
    <div>
        <label for="departement_id" class="block mb-2 text-sm font-medium text-gray-900">Département</label>
        <select id="departement_id" name="departement_id" hx-get="/fetch-item/departement" hx-swap="innerHTML" hx-target="#arrondissement" hx-trigger="change"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            <option value="">Choisir un département</option>
            @foreach ($departments as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach
        </select>
    </div>
@endfragment

@fragment('arrondissement')
    <div id="arrondissement">
        <label for="arrondissement_id" class="block mb-2 text-sm font-medium text-gray-900">Arrondissement</label>
        <select id="arrondissement_id" name="arrondissement_id"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
            <option value="">Choisir un arrondissement</option>
            @foreach ($arrondissements as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach
        </select>
    </div>
@endfragment
