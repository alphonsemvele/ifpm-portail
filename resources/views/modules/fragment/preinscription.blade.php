@fragment('cycle')
    <div class="">
        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Cycles</label>
        <select id="countries" name="formation_type"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

            @foreach ($cycles as $data)
                <option selected>Choisir un cycle </option>
                <option value="ISM">Formation Academique (ISM)</option>
                <option value="IFPM">Formation Professionnelle (IFPM)</option>
            @endforeach

        </select>

    </div>
@endfragment
