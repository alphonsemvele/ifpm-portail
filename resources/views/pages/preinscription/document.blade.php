@php abort_unless(isset($preinscription), 404) @endphp
<x-layouts.app :header="false">

    <div>
        <div class="flex justify-center items-center min-h-screen mx-auto my-auto">
            <div
                class="border-b w-full max-w-xl p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-6 md:p-8 bg-opacity-90">

                <div class="flex justify-center">
                    <img class="h-20" src="{{ asset('images/logo.png') }}" alt="">
                </div>

                <h2 class="text-center text-xl font-bold mt-4">FICHE D'INSCRIPTION</h2>

                <div class="mt-5 text-center">
                    <p>Mr/Mme {{$preinscription->name}} merci de finaliser votre inscription en cliquant sur le lien ci-dessous .</p>

                    {{-- <form method="POST" action="{{ route('generate.pdf', ['id' => $preinscription->id]) }}"> --}}
                    <div class="mt-3">

                        <a href="{{ route('generate.pdf', ['id' => $preinscription->id]) }}"
                            class="mt-5 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            Télécharger ma fiche d'inscription
                        </a>


                    </div>



                </div>
            </div>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            console.log('Alpine.js is loaded!');
        });
    </script>

</x-layouts.app>
