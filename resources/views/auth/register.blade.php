<x-guest-layout>
    
     @if (session('success'))
        <div class="mb-4 p-4 rounded-lg" style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46;">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" style="color: #10b981;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span style="font-weight: 500;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 z-50" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="flex items-center justify-center h-full">
            <div class="text-center" style="background-color: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                <div class="animate-spin rounded-full h-16 w-16 border-b-4 mx-auto mb-4" style="border-color: #4F46E5;"></div>
                <p class="text-lg font-semibold" style="color: #4F46E5;">Inscription en cours...</p>
                <p class="text-sm mt-2" style="color: #6B7280;">Veuillez patienter</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="max-w-4xl mx-auto" id="register-form">
        @csrf
        
        <!-- Photo de profil - Centré sur toute la largeur -->
        <div class="mt-4 col-span-2">
            <x-input-label for="photo" :value="__('Photo de profil')" />
            <div class="flex flex-col items-center">
                <div class="relative">
                    <div id="preview-container" class="w-24 h-24 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center border-2 border-gray-300">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <img id="preview-image" class="hidden w-full h-full object-cover" />
                    </div>
                </div>
                <input type="file" id="photo" name="photo" accept="image/*" class="mt-2 block w-full text-xs text-gray-500
                    file:mr-2 file:py-1 file:px-3
                    file:rounded-full file:border-0
                    file:text-xs file:font-semibold
                    file:bg-indigo-50 file:text-indigo-700
                    hover:file:bg-indigo-100" />
                <input type="hidden" id="cropped-image" name="cropped_photo" />
            </div>
            <x-input-error :messages="$errors->get('photo')" class="mt-2" />
        </div>

        <!-- Grille à 2 colonnes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Matricule -->
            <div>
                <x-input-label for="matricule" :value="__('Matricule')" />
                <x-text-input id="matricule" class="block mt-1 w-full" type="text" name="matricule" :value="old('matricule')" required autocomplete="off" />
                <x-input-error :messages="$errors->get('matricule')" class="mt-2" />
            </div>

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nom')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="family-name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Prenom -->
            <div>
                <x-input-label for="prenom" :value="__('Prenom')" />
                <x-text-input id="lastname" class="block mt-1 w-full" type="text" name="lastname" :value="old('lastname')" required autocomplete="given-name" />
                <x-input-error :messages="$errors->get('prenom')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Contact -->
            <div>
                <x-input-label for="contact" :value="__('Contact')" />
                <x-text-input id="contact" class="block mt-1 w-full" type="tel" name="contact" :value="old('contact')" required autocomplete="tel" placeholder="+237 6XX XXX XXX" />
                <x-input-error :messages="$errors->get('contact')" class="mt-2" />
            </div>

            <!-- Poste Occupe -->
            <div>
                <x-input-label for="poste" :value="__('Poste Occupe')" />
                <select id="poste" name="poste" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                    <option value="">{{ __('Selectionnez un poste') }}</option>
                    <option value="dir_ism" {{ old('poste') == 'dir_ism' ? 'selected' : '' }}>Directeur ISM</option>
                    <option value="dir_ifpm" {{ old('poste') == 'dir_ifpm' ? 'selected' : '' }}>Directrice IFPM</option>
                    <option value="dir_aaf" {{ old('poste') == 'dir_aaf' ? 'selected' : '' }}>Directrice Affaires Admin. et Financieres</option>
                    <option value="dir_aac" {{ old('poste') == 'dir_aac' ? 'selected' : '' }}>Directeur Affaires Academiques</option>
                    <option value="dir_rh" {{ old('poste') == 'dir_rh' ? 'selected' : '' }}>Directrice RH</option>
                    <option value="dir_mc" {{ old('poste') == 'dir_mc' ? 'selected' : '' }}>Directrice Marketing</option>
                    <option value="coord_sante" {{ old('poste') == 'coord_sante' ? 'selected' : '' }}>Coordonnateur Filiere Sante</option>
                    <option value="coord_industrie" {{ old('poste') == 'coord_industrie' ? 'selected' : '' }}>Coordonnateur Filiere Industrie</option>
                    <option value="coord_info" {{ old('poste') == 'coord_info' ? 'selected' : '' }}>Coordonnateur Informatique</option>
                    <option value="coord_meca" {{ old('poste') == 'coord_meca' ? 'selected' : '' }}>Coordonnateur Genie Mecanique</option>
                    <option value="coord_gi" {{ old('poste') == 'coord_gi' ? 'selected' : '' }}>Coordonnateur Genie Informatique</option>
                    <option value="comptable" {{ old('poste') == 'comptable' ? 'selected' : '' }}>Comptable</option>
                    <option value="asst_dir_fp" {{ old('poste') == 'asst_dir_fp' ? 'selected' : '' }}>Assistante Direction Formation Pro</option>
                    <option value="asst_dir_is" {{ old('poste') == 'asst_dir_is' ? 'selected' : '' }}>Assistante Direction Institut</option>
                    <option value="coord_ap" {{ old('poste') == 'coord_ap' ? 'selected' : '' }}>Coordonnateur Activites Pedagogiques</option>
                    <option value="coord_hnd" {{ old('poste') == 'coord_hnd' ? 'selected' : '' }}>Coordonnateur HND</option>
                    <option value="coord_tourisme" {{ old('poste') == 'coord_tourisme' ? 'selected' : '' }}>Coordonnateur Tourisme</option>
                    <option value="coord_adj_sante" {{ old('poste') == 'coord_adj_sante' ? 'selected' : '' }}>Coordonnateur Adjoint Sante</option>
                    <option value="medecin" {{ old('poste') == 'medecin' ? 'selected' : '' }}>Medecin Referent</option>
                    <option value="gest_stocks" {{ old('poste') == 'gest_stocks' ? 'selected' : '' }}>Gestionnaire Stocks</option>
                    <option value="chef_entretien" {{ old('poste') == 'chef_entretien' ? 'selected' : '' }}>Chef Agent Entretien</option>
                    <option value="agent_scolarite" {{ old('poste') == 'agent_scolarite' ? 'selected' : '' }}>Agent de Scolarite</option>
                    <option value="coord_droit" {{ old('poste') == 'coord_droit' ? 'selected' : '' }}>Coordonnateur Droit</option>
                </select>
                <x-input-error :messages="$errors->get('poste')" class="mt-2" />
            </div>

            <!-- Entite -->
            <div>
                <x-input-label for="entite" :value="__('Entite')" />
                <select id="entite" name="entite" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                    <option value="">{{ __('Selectionnez une entite') }}</option>
                    <option value="1" {{ old('entite') == '1' ? 'selected' : '' }}>ISM</option>
                    <option value="2" {{ old('entite') == '2' ? 'selected' : '' }}>IFPM</option>
                </select>
                <x-input-error :messages="$errors->get('entite')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Mot de passe')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Deja inscrit ?') }}
            </a>
            <x-primary-button class="ms-4" id="submit-btn">
                {{ __('S\'inscrire') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Modal de recadrage -->
    <div id="crop-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-4 border w-80 shadow-lg rounded-md bg-white">
            <div class="mt-2">
                <h3 class="text-base font-medium leading-6 text-gray-900 mb-3">Recadrer votre photo</h3>
                <div class="flex justify-center">
                    <div id="crop-container">
                        <canvas id="crop-canvas"></canvas>
                    </div>
                </div>
                <div class="mt-3 flex justify-end space-x-2">
                    <button type="button" id="cancel-crop" class="px-3 py-1.5 text-sm bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Annuler
                    </button>
                    <button type="button" id="apply-crop" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Appliquer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentImage = null;
        let canvas = null;
        let ctx = null;
        let isDragging = false;
        let dragStartX = 0;
        let dragStartY = 0;
        let imageX = 0;
        let imageY = 0;
        let scale = 1;

        // Gestion du chargement lors de la soumission du formulaire
        document.getElementById('register-form').addEventListener('submit', function(e) {
            // Afficher l'overlay de chargement
            document.getElementById('loading-overlay').classList.remove('hidden');
            
            // Désactiver le bouton de soumission
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
        });

        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    currentImage = new Image();
                    currentImage.onload = function() {
                        showCropModal();
                    };
                    currentImage.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        function showCropModal() {
            document.getElementById('crop-modal').classList.remove('hidden');
            canvas = document.getElementById('crop-canvas');
            ctx = canvas.getContext('2d');
            
            const size = 250;
            canvas.width = size;
            canvas.height = size;
            
            scale = Math.max(size / currentImage.width, size / currentImage.height);
            imageX = (size - currentImage.width * scale) / 2;
            imageY = (size - currentImage.height * scale) / 2;
            
            drawImage();
            
            canvas.addEventListener('mousedown', startDrag);
            canvas.addEventListener('mousemove', drag);
            canvas.addEventListener('mouseup', endDrag);
            canvas.addEventListener('mouseleave', endDrag);
            canvas.addEventListener('wheel', handleZoom);
        }

        function drawImage() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            ctx.save();
            ctx.drawImage(currentImage, imageX, imageY, currentImage.width * scale, currentImage.height * scale);
            ctx.restore();
            
            ctx.save();
            ctx.globalCompositeOperation = 'destination-in';
            ctx.beginPath();
            ctx.arc(canvas.width / 2, canvas.height / 2, canvas.width / 2, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
            
            ctx.strokeStyle = '#4F46E5';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(canvas.width / 2, canvas.height / 2, canvas.width / 2 - 1, 0, Math.PI * 2);
            ctx.stroke();
        }

        function startDrag(e) {
            isDragging = true;
            dragStartX = e.offsetX - imageX;
            dragStartY = e.offsetY - imageY;
        }

        function drag(e) {
            if (isDragging) {
                imageX = e.offsetX - dragStartX;
                imageY = e.offsetY - dragStartY;
                drawImage();
            }
        }

        function endDrag() {
            isDragging = false;
        }

        function handleZoom(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            const newScale = scale * delta;
            
            if (newScale > 0.1 && newScale < 5) {
                scale = newScale;
                drawImage();
            }
        }

        document.getElementById('apply-crop').addEventListener('click', function() {
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = 250;
            finalCanvas.height = 250;
            const finalCtx = finalCanvas.getContext('2d');
            
            finalCtx.drawImage(currentImage, imageX, imageY, currentImage.width * scale, currentImage.height * scale);
            
            finalCtx.globalCompositeOperation = 'destination-in';
            finalCtx.beginPath();
            finalCtx.arc(125, 125, 125, 0, Math.PI * 2);
            finalCtx.fill();
            
            const croppedImage = finalCanvas.toDataURL('image/png');
            
            const previewImage = document.getElementById('preview-image');
            previewImage.src = croppedImage;
            previewImage.classList.remove('hidden');
            
            document.getElementById('cropped-image').value = croppedImage;
            
            document.getElementById('crop-modal').classList.add('hidden');
        });

        document.getElementById('cancel-crop').addEventListener('click', function() {
            document.getElementById('crop-modal').classList.add('hidden');
            document.getElementById('photo').value = '';
        });
    </script>
</x-guest-layout>