<?php

use function Laravel\Folio\name;
use Livewire\Volt\Component;

?>

<x-layouts.app :header="false">
    @volt
        <div>

            {{-- <nav class="bg-[#3354a5] border-gray-200 border-b border-[#bf1e2e]">
                <div class="max-w-screen-xl flex flex-wrap items-center justify-center mx-auto p-4">


                    <span
                        class=" flex justify-center self-center text-2xl font-semibold whitespace-nowrap text-white">Préinscription
                        Octobre
                        2025 ouvert</span>

                </div>
            </nav> --}}

            <div class="flex justify-center items-center min-h-screen">


                <div
                    class="border-b  w-full max-w-xl p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-6 md:p-8 bg-opacity-90">


                    <div class="flex justify-center  ">
                        <img class="h-40 " src="{{ asset('images/logo.png') }}" alt="">
                    </div>


                    <form hx-post="" class="max-w-xl mx-auto mt-5">


                        <div class="mt-4">
                            <div class="">
                                <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Type de
                                    formation</label>
                                <select id="formation_type" name="formation_type"

                                hx-get='/fetch-item/cycle'
                                hx-targe= "#cycle"
                                hx-trigger='change'

                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">



                                        <option selected>Choisir un type </option>
                                        <option value="ISM">Formation Academique (ISM)</option>
                                        <option value="IFPM">Formation Professionnelle (IFPM)</option>


                                </select>
                            </div>
                        </div>
                        <div class="mt-4">


                            <div id="cycle"></div>


                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="">
                                <label for="password"
                                    class="block mb-2 text-sm font-medium text-gray-900">Filière</label>
                                <select id="countries"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

                                    @fragment('filiere')
                                        <option selected>Choisir une filière</option>
                                        @foreach($filieres as $data)
                                        <option value="{{$data->id}}"> {{$data->name}} </option>
                                        @endforeach
                                    @endfragment
                                </select>

                            </div>


                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Spécialité
                                </label>
                                <select id="countries"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

                                    @fragment('specialite')

                                        @foreach($specialites as $data)
                                        <option selected>Choisir une spécialité</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="FR">France</option>
                                        <option value="DE">Germany</option>
                                        @endforeach

                                    @endfragment

                                </select>



                            </div>



                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-5">

                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Votre nom
                                </label>
                                <input type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="votre nom" required />
                            </div>

                            <div class="">
                                <label for="password"
                                    class="block mb-2 text-sm font-medium text-gray-900">Votre
                                    Prenom</label>
                                <input type="text" id="lastname" placeholder="votre prenom"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required />
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="">
                                <label for="password"
                                    class="block mb-2 text-sm font-medium text-gray-900">Date de
                                    naissance</label>
                                <input type="date" id="lastname" placeholder="votre prenom"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required />
                            </div>
                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Région d'origine
                                </label>
                                <select id="countries"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option selected>Choisir une région</option>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="FR">France</option>
                                    <option value="DE">Germany</option>
                                </select>
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Departement
                                </label>
                                <select id="countries"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

                                    @fragment('department')

                                    @foreach($deparments as $data)
                                        <option selected>Choisir une région</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="FR">France</option>
                                        <option value="DE">Germany</option>
                                    @endforeach
                                    @endfragment

                                </select>
                            </div>
                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Arrondissement
                                </label>
                                <select id="countries"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">

                                    @fragment('arrondissement')

                                    @foreach($arrondissement as $data)

                                        <option selected>Choisir une région</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="FR">France</option>
                                        <option value="DE">Germany</option>

                                    @endforeach


                                    @endfragment
                                </select>
                            </div>



                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">

                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Contact
                                </label>
                                <input type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="votre contact" required />
                            </div>

                            <div class="">
                                <label for="password"
                                    class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                                <input type="text" id="lastname" placeholder="votre email"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required />
                            </div>

                        </div>


                        <div class="grid grid-cols-2 gap-4 mt-5">

                            <div class="">
                                <label for="email"
                                    class="block mb-2 text-sm font-medium text-gray-900">Nom du père
                                </label>
                                <input type="text" id="name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Nom du père" required />
                            </div>

                            <div class="">
                                <label for="password"
                                    class="block mb-2 text-sm font-medium text-gray-900">Nom de la
                                    mère</label>
                                <input type="text" id="lastname" placeholder="Nom de la mère"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required />
                            </div>

                        </div>



                        <div class="flex items-start mb-5">


                        </div>
                        <button type="submit"
                            class=" flex justify-center text-white bg-[#3354a5] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">Valider</button>
                    </form>

                </div>

            </div>

        </div>
    @endvolt
</x-layouts.app>
