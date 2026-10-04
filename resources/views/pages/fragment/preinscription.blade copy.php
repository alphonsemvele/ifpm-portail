@fragment('cycle')
    <div class="">
        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cycles</label>
        <select id="countries" hx-get='/fetch-item/cycle' hx-target = '#filiere' hx-swap = 'innerHTML' name="cycle_type"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <option selected>Choisir un cycle </option>

            @foreach ($cycles as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach

        </select>

    </div>
@endfragment



@fragment('filiere')
    <div class="">
        <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Filière</label>
        <select id="countries" name="filiere_type" hx-get="/fetch-item/filiere" hx-target = '#specialite'
            hx-swap = 'innerHTML'
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">



            @foreach ($filieres as $data)
                <option value="{{ $data->id }}"> {{ $data->name }} </option>
            @endforeach

        </select>

    </div>
@endfragment


@fragment('specialite')
    <div class="" id="specialite" >
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Spécialité
        </label>
        <select id="countries"
        @click= "open = !open"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

            @foreach ($specialites as $data)
                <option value="{{ $data->id }}"> {{ $data->name }} </option>
            @endforeach

        </select>

    </div>
@endfragment



@fragment('departement')
    <div class="">
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Departement
        </label>
        <select id="countries"

        name="departement_type"
        hx-get="/fetch-item/departement"
        hx-swap = "innerHTML"
        hx-target = "#arrondissement"


            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            @foreach ($departments as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach

        </select>
    </div>
@endfragment


@fragment('arrondissement')
    <div id="arrondissement">
        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Arrondissement
        </label>
        <select id="countries"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            @foreach ($arrondissements as $data)
                <option value="{{ $data->id }}">{{ $data->name }}</option>
            @endforeach

        </select>
    </div>
@endfragment
