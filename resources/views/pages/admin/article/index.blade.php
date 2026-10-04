<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\Article;
use App\Models\Image;
use App\Models\Document;

name('admin.articles');
middleware(['auth', 'verified']);

new class extends Component {
    use WithFileUploads;

    public $articles;
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showImageModal = false;
    public $showDocumentModal = false;
    public $showShareModal = false;
    public $showDeleteModal = false;
    public $showActivateModal = false;
    public $showDeactivateModal = false;
    public $articleElement;
    public $showNotification = false;
    public $notificationMessage = '';
    public $notificationType = '';

    public $title = '';
    public $content = '';
    public $published_at = '';
    public $images = [];
    public $documents = [];
    public $currentImages = [];
    public $currentDocuments = [];
    public $imagesToDelete = [];
    public $documentsToDelete = [];

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        \Log::info('loadData appelé');
        $this->articles = Article::with(['images', 'documents'])
            ->where('status', '!=', 'failed')
            ->get();
        \Log::info('Données chargées : ', [
            'articles' => $this->articles->count(),
        ]);
    }

    public function save()
    {
        \Log::info('Méthode save appelée', [
            'title' => $this->title,
            'content' => $this->content,
            'published_at' => $this->published_at,
            'images' => $this->images ? count($this->images) . ' images' : 'Pas d\'image',
            'documents' => $this->documents ? count($this->documents) . ' documents' : 'Pas de document',
        ]);

       

            $data = [
                'title' => $this->title,
                'content' => $this->content,
                'published_at' => $this->published_at,
                'status' => 'pending',
            ];

            $article = Article::create($data);

            // Enregistrement des images (obligatoire)
            foreach ($this->images as $image) {
                $img_name = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('images', $img_name, 'public');
                $img_url = 'https://ism-ndazoa.com/images/' . $img_name;
                
                Image::create([
                    'path' => $img_url,
                    'article_id' => $article->id,
                ]);
            }

            // Enregistrement des documents (optionnel)
            if ($this->documents && count($this->documents) > 0) {
                foreach ($this->documents as $document) {
                    $doc_name = hexdec(uniqid()) . '.' . $document->getClientOriginalExtension();
                    $document->storeAs('documents', $doc_name, 'public');
                    $doc_url = 'https://ism-ndazoa.com/documents/' . $doc_name;
                    
                    Document::create([
                        'path' => $doc_url,
                        'article_id' => $article->id,
                    ]);
                }
            }

            \Log::info('Article créé avec succès');
            $this->resetForm();
            $this->showAddModal = false;
            $this->loadData();
            $this->showNotification('Article ajouté avec succès !', 'success');
     
    }

    public function functionShowAddModal()
    {
        \Log::info('functionShowAddModal appelé');
        $this->resetForm();
        $this->resetValidation(); // Réinitialiser les erreurs de validation
        $this->showAddModal = true;
    }

    public function functionShowEditModal($id)
    {
        try {
            \Log::info('functionShowEditModal appelé avec ID: ' . $id);
            $this->articleElement = Article::with(['images', 'documents'])->findOrFail($id);
            $this->title = $this->articleElement->title;
            $this->content = $this->articleElement->content;
            $this->published_at = $this->articleElement->published_at
                ? $this->articleElement->published_at->format('Y-m-d\TH:i')
                : '';
            $this->images = [];
            $this->documents = [];
            $this->currentImages = $this->articleElement->images->toArray();
            $this->currentDocuments = $this->articleElement->documents->toArray();
            $this->imagesToDelete = [];
            $this->documentsToDelete = [];
            $this->resetValidation(); // Réinitialiser les erreurs de validation
            $this->showEditModal = true;
            $this->dispatch('edit-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowEditModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'édition : ' . $e->getMessage(), 'error');
        }
    }

    public function markImageForDeletion($imageId)
    {
        if (!in_array($imageId, $this->imagesToDelete)) {
            $this->imagesToDelete[] = $imageId;
        }
    }

    public function markDocumentForDeletion($documentId)
    {
        if (!in_array($documentId, $this->documentsToDelete)) {
            $this->documentsToDelete[] = $documentId;
        }
    }

    public function functionShowViewModal($id)
    {
        try {
            \Log::info('functionShowViewModal appelé avec ID: ' . $id);
            $this->articleElement = Article::with(['images', 'documents'])->findOrFail($id);
            $this->showViewModal = true;
            $this->dispatch('view-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowViewModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de visualisation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowImageModal($id)
    {
        try {
            \Log::info('functionShowImageModal appelé avec ID: ' . $id);
            $this->articleElement = Article::with('images')->findOrFail($id);
            if ($this->articleElement->images->count() > 0) {
                $this->showImageModal = true;
                $this->dispatch('image-modal-opened', id: $id);
            } else {
                $this->showNotification('Aucune image disponible pour cet article.', 'error');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowImageModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'image : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDocumentModal($id)
    {
        try {
            \Log::info('functionShowDocumentModal appelé avec ID: ' . $id);
            $this->articleElement = Article::with('documents')->findOrFail($id);
            if ($this->articleElement->documents->count() > 0) {
                $this->showDocumentModal = true;
                $this->dispatch('document-modal-opened', id: $id);
            } else {
                $this->showNotification('Aucun document disponible pour cet article.', 'error');
            }
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowDocumentModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de document : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowShareModal($id)
    {
        try {
            \Log::info('functionShowShareModal appelé avec ID: ' . $id);
            $this->articleElement = Article::findOrFail($id);
            $this->showShareModal = true;
            $this->dispatch('share-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowShareModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de partage : ' . $e->getMessage(), 'error');
        }
    }

    public function update()
    {
        \Log::info('Méthode update appelée', [
            'title' => $this->title,
            'content' => $this->content,
            'published_at' => $this->published_at,
            'images' => $this->images ? count($this->images) . ' nouvelles images' : 'Pas de nouvelle image',
            'documents' => $this->documents ? count($this->documents) . ' nouveaux documents' : 'Pas de nouveau document',
        ]);

        try {
            // Vérifier qu'il restera au moins une image après suppression
            $remainingImagesCount = count($this->currentImages) - count($this->imagesToDelete) + count($this->images);
            
            if ($remainingImagesCount < 1) {
                $this->addError('images', 'Au moins une image est requise pour l\'article.');
                $this->showNotification('Au moins une image est requise pour l\'article.', 'error');
                return;
            }

          

            $data = [
                'title' => $this->title,
                'content' => $this->content,
                'published_at' => $this->published_at,
            ];

            $this->articleElement->update($data);

            // Suppression des images marquées
            if (!empty($this->imagesToDelete)) {
                foreach ($this->imagesToDelete as $imageId) {
                    $image = Image::find($imageId);
                    if ($image) {
                        \Storage::disk('public')->delete(str_replace('storage/', '', $image->path));
                        $image->delete();
                    }
                }
            }

            // Suppression des documents marqués
            if (!empty($this->documentsToDelete)) {
                foreach ($this->documentsToDelete as $documentId) {
                    $document = Document::find($documentId);
                    if ($document) {
                        \Storage::disk('public')->delete(str_replace('storage/', '', $document->path));
                        $document->delete();
                    }
                }
            }

            // Ajout de nouvelles images
            if ($this->images && count($this->images) > 0) {
                foreach ($this->images as $image) {
                    $img_name = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('images', $img_name, 'public');
                    $img_url = 'storage/images/' . $img_name;
                    
                    Image::create([
                        'path' => $img_url,
                        'article_id' => $this->articleElement->id,
                    ]);
                }
            }

            // Ajout de nouveaux documents
            if ($this->documents && count($this->documents) > 0) {
                foreach ($this->documents as $document) {
                    $doc_name = hexdec(uniqid()) . '.' . $document->getClientOriginalExtension();
                    $document->storeAs('documents', $doc_name, 'public');
                    $doc_url = 'storage/documents/' . $doc_name;
                    
                    Document::create([
                        'path' => $doc_url,
                        'article_id' => $this->articleElement->id,
                    ]);
                }
            }

            \Log::info('Article mis à jour avec succès');
            $this->resetForm();
            $this->showEditModal = false;
            $this->articleElement = null;
            $this->loadData();
            $this->showNotification('Article mis à jour avec succès !', 'success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation dans update : ', $e->errors());
            $this->showNotification('Veuillez corriger les erreurs dans le formulaire.', 'error');
            throw $e; // Relancer l'exception pour que Livewire gère les erreurs
        } catch (\Exception $e) {
            \Log::error('Erreur dans update : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la mise à jour de l\'article : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowActivateModal($id)
    {
        try {
            \Log::info('functionShowActivateModal appelé avec ID: ' . $id);
            $this->articleElement = Article::findOrFail($id);
            $this->showActivateModal = true;
            $this->dispatch('activate-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowActivateModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal d\'activation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeactivateModal($id)
    {
        try {
            \Log::info('functionShowDeactivateModal appelé avec ID: ' . $id);
            $this->articleElement = Article::findOrFail($id);
            $this->showDeactivateModal = true;
            $this->dispatch('deactivate-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowDeactivateModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de désactivation : ' . $e->getMessage(), 'error');
        }
    }

    public function functionShowDeleteModal($id)
    {
        try {
            \Log::info('functionShowDeleteModal appelé avec ID: ' . $id);
            $this->articleElement = Article::findOrFail($id);
            $this->showDeleteModal = true;
            $this->dispatch('delete-modal-opened', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans functionShowDeleteModal : ' . $e->getMessage());
            $this->showNotification('Erreur lors de l\'ouverture du modal de suppression : ' . $e->getMessage(), 'error');
        }
    }

    public function activateArticle($id)
    {
        try {
            \Log::info('activateArticle appelé avec ID: ' . $id);
            $article = Article::findOrFail($id);
            $article->update(['status' => 'Success']);
            \Log::info('Article validé avec succès');
            $this->showActivateModal = false;
            $this->articleElement = null;
            $this->loadData();
            $this->showNotification('Article validé avec succès !', 'success');
            $this->dispatch('article-activated', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans activateArticle : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la validation de l\'article : ' . $e->getMessage(), 'error');
        }
    }

    public function deactivateArticle($id)
    {
        try {
            \Log::info('deactivateArticle appelé avec ID: ' . $id);
            $article = Article::findOrFail($id);
            $article->update(['status' => 'pending']);
            \Log::info('Article désactivé avec succès');
            $this->showDeactivateModal = false;
            $this->articleElement = null;
            $this->loadData();
            $this->showNotification('Article marqué comme en attente avec succès !', 'success');
            $this->dispatch('article-deactivated', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans deactivateArticle : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la désactivation de l\'article : ' . $e->getMessage(), 'error');
        }
    }

    public function deleteArticle($id)
    {
        try {
            \Log::info('deleteArticle appelé avec ID: ' . $id);
            $article = Article::with(['images', 'documents'])->findOrFail($id);
            
            // Suppression des images
            foreach ($article->images as $image) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $image->path));
                $image->delete();
            }
            
            // Suppression des documents
            foreach ($article->documents as $document) {
                \Storage::disk('public')->delete(str_replace('storage/', '', $document->path));
                $document->delete();
            }
            
            $article->update(['status' => 'failed']);
            \Log::info('Article marqué comme supprimé avec succès');
            $this->showDeleteModal = false;
            $this->articleElement = null;
            $this->loadData();
            $this->showNotification('Article marqué comme supprimé avec succès !', 'success');
            $this->dispatch('article-deleted', id: $id);
        } catch (\Exception $e) {
            \Log::error('Erreur dans deleteArticle : ' . $e->getMessage());
            $this->showNotification('Erreur lors de la suppression de l\'article : ' . $e->getMessage(), 'error');
        }
    }

    public function closeModal()
    {
        \Log::info('closeModal appelé');
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showImageModal = false;
        $this->showDocumentModal = false;
        $this->showShareModal = false;
        $this->showDeleteModal = false;
        $this->showActivateModal = false;
        $this->showDeactivateModal = false;
        $this->resetForm();
        $this->resetValidation(); // Réinitialiser les erreurs de validation
        $this->articleElement = null;
    }

    private function resetForm()
    {
        \Log::info('resetForm appelé');
        $this->reset(['title', 'content', 'published_at', 'images', 'documents', 'currentImages', 'currentDocuments', 'imagesToDelete', 'documentsToDelete']);
    }

    private function showNotification($message, $type)
    {
        \Log::info('showNotification appelé', ['message' => $message, 'type' => $type]);
        $this->notificationMessage = $message;
        $this->notificationType = $type;
        $this->showNotification = true;
        $this->dispatch('auto-hide-notification');
    }
};
?>
<x-layouts.app header="true">
    @volt
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestion des Articles</h1>
                    <p class="text-gray-500">Créez, modifiez, visualisez, partagez, validez ou supprimez des articles avec titre, contenu, images et documents</p>
                </div>
                <button wire:click="functionShowAddModal"
                    class="mt-6 bg-indigo-600 text-white py-3 px-8 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    x-on:click="console.log('Bouton Ajouter un article cliqué')">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un article
                </button>
            </div>

            <!-- Notification -->
            @if ($showNotification)
                <div class="fixed top-6 right-6 z-50 max-w-sm w-full" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
                    x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4">
                    <div class="{{ $notificationType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} p-4 rounded-xl shadow-md border-l-4 {{ $notificationType === 'success' ? 'border-green-500' : 'border-red-500' }} animate-pulse">
                        {{ $notificationMessage }}
                    </div>
                </div>
            @endif

     
@if ($showAddModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] shadow-2xl transform transition-all duration-300 ease-in-out flex flex-col">
            <!-- Header fixe -->
            <div class="relative p-6 border-b border-gray-200 flex-shrink-0">
                <h2 class="text-3xl font-extrabold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Ajouter un Article
                </h2>
                <button wire:click="closeModal" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700 transition duration-200" x-on:click="console.log('Bouton Fermer modal cliqué')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Contenu scrollable -->
            <div class="overflow-y-auto flex-1 p-6">
                <!-- Error Messages -->
                @if (!empty($formErrors))
                    <div class="mb-6 p-4 bg-red-50 text-red-800 rounded-lg border border-red-200 shadow-sm">
                        <p class="font-medium">Veuillez corriger les erreurs suivantes :</p>
                        <ul class="list-disc ml-5 mt-2 text-sm">
                            @foreach ($formErrors as $field => $errors)
                                @foreach ($errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form -->
                <form wire:submit.prevent="save" enctype="multipart/form-data" id="addArticleForm">
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-800">Titre</label>
                            <input type="text" wire:model.defer="title" id="title" required
                                class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                                placeholder="Entrez le titre de l'article">
                            @error('title')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-semibold text-gray-800">Contenu</label>
                            <textarea wire:model.defer="content" id="content" required
                                class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 resize-y"
                                rows="6" placeholder="Entrez le contenu de l'article"></textarea>
                            @error('content')
                                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Images (required) -->
                        <div>
                            <label for="images" class="block text-sm font-semibold text-gray-800">Images <span class="text-red-500">*</span> (Au moins une image requise)</label>
                            <div class="mt-1 relative">
                                <input type="file" wire:model="images" id="images" multiple required
                                    class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-700 file:font-medium file:hover:bg-indigo-200 file:transition file:duration-200">
                                @error('images')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                                @error('images.*')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Documents (optional) -->
                        <div>
                            <label for="documents" class="block text-sm font-semibold text-gray-800">Documents (Optionnel)</label>
                            <div class="mt-1 relative">
                                <input type="file" wire:model="documents" id="documents" multiple
                                    class="w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-purple-100 file:text-purple-700 file:font-medium file:hover:bg-purple-200 file:transition file:duration-200">
                                @error('documents')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                                @error('documents.*')
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Published At -->
                        <div>
                            <label for="published_at" class="block text-sm font-semibold text-gray-800">Date de publication</label>
                            <input type="datetime-local" wire:model.defer="published_at" id="published_at" required
                                class="mt-1 w-full p-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200">
                            @error('published_at')
                                <span class="text-red-500 text-xs mt-1">{{ $message}}</span>
                            @enderror
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer avec boutons fixes -->
            <div class="border-t border-gray-200 p-6 flex-shrink-0 bg-gray-50 rounded-b-2xl">
                <div class="flex justify-end space-x-4">
                    <button type="submit" form="addArticleForm"
                        class="bg-indigo-600 text-white py-3 px-6 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                        x-on:click="console.log('Bouton Enregistrer cliqué')">
                        Enregistrer
                    </button>
                    <button type="button" wire:click="closeModal"
                        class="bg-gray-200 text-gray-800 py-3 px-6 rounded-lg hover:bg-gray-300 focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif


@if ($showEditModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] shadow-2xl transform transition-all duration-300 ease-in-out flex flex-col">
            <!-- Header fixe -->
            <div class="p-6 border-b border-gray-200 flex-shrink-0">
                <h2 class="text-3xl font-bold text-gray-800 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Modifier un Article</h2>
            </div>

            <!-- Contenu scrollable -->
            <div class="overflow-y-auto flex-1 p-6">
                @if (!empty($formErrors))
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-xl border-l-4 border-red-500">
                        Veuillez corriger les erreurs suivantes :
                        <ul class="list-disc ml-5">
                            @foreach ($formErrors as $field => $errors)
                                @foreach ($errors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form wire:submit.prevent="update" enctype="multipart/form-data" id="editArticleForm">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Titre</label>
                            <input type="text" wire:model.defer="title" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200" placeholder="Entrez le titre de l'article">
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contenu</label>
                            <textarea wire:model.defer="content" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200" rows="6" placeholder="Entrez le contenu de l'article"></textarea>
                            @error('content') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Images actuelles -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Images actuelles</label>
                            @if (count($currentImages) > 0)
                                <div class="mt-2 grid grid-cols-3 gap-4">
                                    @foreach ($currentImages as $image)
                                        @if (!in_array($image['id'], $imagesToDelete))
                                            <div class="relative group">
                                                <img src="{{ $image['path'] }}" alt="Image" class="w-full h-32 object-cover rounded-lg">
                                                <button type="button" wire:click="markImageForDeletion({{ $image['id'] }})"
                                                    class="absolute top-2 right-2 bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition duration-200 opacity-0 group-hover:opacity-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <div class="relative">
                                                <img src="{{ $image['path'] }}" alt="Image" class="w-full h-32 object-cover rounded-lg opacity-50">
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm">À supprimer</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-gray-600">Aucune image</p>
                            @endif
                            @if (count($currentImages) - count($imagesToDelete) < 1)
                                <p class="mt-2 text-red-600 text-sm font-medium">⚠️ Vous devez conserver au moins une image ou en ajouter une nouvelle</p>
                            @endif
                        </div>

                        <!-- Nouvelles images -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ajouter de nouvelles images</label>
                            <input type="file" wire:model="images" multiple class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                            @error('images') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @error('images.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Documents actuels -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Documents actuels</label>
                            @if (count($currentDocuments) > 0)
                                <div class="mt-2 space-y-2">
                                    @foreach ($currentDocuments as $document)
                                        @if (!in_array($document['id'], $documentsToDelete))
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <a href="{{ $document['path'] }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    {{ basename($document['path']) }}
                                                </a>
                                                <button type="button" wire:click="markDocumentForDeletion({{ $document['id'] }})"
                                                    class="bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg opacity-50">
                                                <span class="text-gray-600 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    {{ basename($document['path']) }}
                                                </span>
                                                <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm">À supprimer</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-gray-600">Aucun document</p>
                            @endif
                        </div>

                        <!-- Nouveaux documents -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ajouter de nouveaux documents</label>
                            <input type="file" wire:model="documents" multiple class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                            @error('documents') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @error('documents.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de publication</label>
                            <input type="datetime-local" wire:model.defer="published_at" required class="mt-2 w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 transition-all duration-200">
                            @error('published_at') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer avec boutons fixes -->
            <div class="border-t border-gray-200 p-6 flex-shrink-0 bg-gray-50 rounded-b-2xl">
                <div class="flex justify-end space-x-4">
                    <button type="submit" form="editArticleForm"
                        class="bg-indigo-600 text-white py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                        x-on:click="console.log('Bouton Mettre à jour cliqué')">
                        Mettre à jour
                    </button>
                    <button type="button" wire:click="closeModal"
                        class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

            <!-- Modal Visualiser Article -->
            @if ($showViewModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Visualiser l'Article</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-700">Titre</h3>
                                <p class="mt-2 text-gray-900 text-base">{{ $articleElement->title ?? 'Non défini' }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-700">Statut</h3>
                                <span class="mt-2 inline-block px-3 py-1 text-sm font-medium rounded-full
                                    {{ $articleElement->status === 'Success' ? 'bg-green-100 text-green-800' : ($articleElement->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $articleElement->status === 'Success' ? 'Validé' : ($articleElement->status === 'pending' ? 'En attente' : 'Supprimé') }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-700">Date de publication</h3>
                                <p class="mt-2 text-gray-900 text-base">{{ $articleElement->published_at ? \Carbon\Carbon::parse($articleElement->published_at)->format('d/m/Y H:i') : 'Non définie' }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-700">Images ({{ $articleElement->images->count() }})</h3>
                                @if ($articleElement->images->count() > 0)
                                    <button wire:click="functionShowImageModal({{ $articleElement->id }})"
                                        class="mt-2 bg-blue-600 text-white py-2 px-4 rounded-xl hover:bg-blue-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                                        x-on:click="console.log('Bouton Voir les images cliqué')">
                                        Voir les images
                                    </button>
                                @else
                                    <p class="mt-2 text-gray-900 text-base">Aucune image</p>
                                @endif
                            </div>
                            <div class="col-span-2">
                                <h3 class="text-lg font-medium text-gray-700">Documents ({{ $articleElement->documents->count() }})</h3>
                                @if ($articleElement->documents->count() > 0)
                                    <button wire:click="functionShowDocumentModal({{ $articleElement->id }})"
                                        class="mt-2 bg-purple-600 text-white py-2 px-4 rounded-xl hover:bg-purple-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                                        x-on:click="console.log('Bouton Voir les documents cliqué')">
                                        Voir les documents
                                    </button>
                                @else
                                    <p class="mt-2 text-gray-900 text-base">Aucun document</p>
                                @endif
                            </div>
                            <div class="col-span-2">
                                <h3 class="text-lg font-medium text-gray-700">Contenu</h3>
                                <p class="mt-2 text-gray-900 text-base whitespace-pre-wrap">{{ $articleElement->content ?? 'Non défini' }}</p>
                            </div>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Images -->
            @if ($showImageModal)
                <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
                    <div class="relative p-4 max-w-5xl max-h-[90vh] overflow-auto bg-white rounded-2xl">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Images de l'article</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach ($articleElement->images as $image)
                                <img src="{{ asset($image->path) }}" alt="Image de l'article" class="w-full h-auto rounded-xl shadow-lg">
                            @endforeach
                        </div>
                        <button wire:click="closeModal"
                            class="mt-4 bg-gray-800 text-white py-2 px-6 rounded-full hover:bg-gray-900 transition duration-300"
                            x-on:click="console.log('Bouton Fermer images cliqué')">
                            Fermer
                        </button>
                    </div>
                </div>
            @endif

            <!-- Modal Documents -->
            @if ($showDocumentModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center z-50">
                    <div class="relative p-8 max-w-2xl max-h-[90vh] overflow-auto bg-white rounded-2xl shadow-2xl">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Documents de l'article</h3>
                        <div class="space-y-3">
                            @foreach ($articleElement->documents as $document)
                                <a href="{{ $document->path }}" target="_blank" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition duration-200">
                                    <span class="flex items-center text-indigo-600 font-medium">
                                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ basename($document->path) }}
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                        <button wire:click="closeModal"
                            class="mt-6 w-full bg-gray-800 text-white py-3 px-6 rounded-xl hover:bg-gray-900 transition duration-300"
                            x-on:click="console.log('Bouton Fermer documents cliqué')">
                            Fermer
                        </button>
                    </div>
                </div>
            @endif

            <!-- Modal Partager Article -->
            @if ($showShareModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-8 w-full max-w-lg shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Partager l'Article</h2>
                        <p class="mb-4 text-gray-600">Partagez l'article "{{ $articleElement->title ?? 'N/A' }}" via :</p>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="https://wa.me/?text={{ urlencode($articleElement->title . ': ' . \Str::limit($articleElement->content, 100) . ' ' . env('APP_URL') . '/articles/' . $articleElement->id) }}"
                                target="_blank"
                                class="flex items-center justify-center bg-green-600 text-white py-3 px-4 rounded-xl hover:bg-green-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.198-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.134.297-.347.446-.52.149-.174.297-.347.397-.496.099-.149.124-.347-.025-.496-.149-.149-.669-.669-1.015-.986-.347-.317-.669-.347-.966-.049-.297.297-1.255.966-1.553 1.164-.297.198-.595.446-.892.743-.297.297-.297.744-.05.992.248.248.992 1.24 1.24 1.737.248.496.992 2.975 3.717 4.114 1.737.744 3.468.595 4.411.347.943-.248 1.737-.992 1.985-1.985.248-.992.124-1.836-.124-2.033-.248-.198-.471-.297-.669-.297z" />
                                </svg>
                                WhatsApp
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(env('APP_URL') . '/articles/' . $articleElement->id) }}"
                                target="_blank"
                                class="flex items-center justify-center bg-blue-600 text-white py-3 px-4 rounded-xl hover:bg-blue-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.437 9.878v-6.987h-2.54v-2.891h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.563V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                                </svg>
                                Facebook
                            </a>
                            <a href="sms:?body={{ urlencode($articleElement->title . ': ' . \Str::limit($articleElement->content, 100) . ' ' . env('APP_URL') . '/articles/' . $articleElement->id) }}"
                                class="flex items-center justify-center bg-gray-600 text-white py-3 px-4 rounded-xl hover:bg-gray-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                SMS
                            </a>
                            <a href="mailto:?subject={{ urlencode($articleElement->title) }}&body={{ urlencode(\Str::limit($articleElement->content, 100) . ' ' . env('APP_URL') . '/articles/' . $articleElement->id) }}"
                                class="flex items-center justify-center bg-indigo-600 text-white py-3 px-4 rounded-xl hover:bg-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email
                            </a>
                        </div>
                        <div class="mt-8 flex justify-end">
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-3 px-6 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-1">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Valider -->
            @if ($showActivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Valider l'article</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous valider l'article "{{ $articleElement->title ?? 'N/A' }}" ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="activateArticle({{ $articleElement->id }})"
                                class="bg-green-600 text-white py-2 px-4 rounded-xl hover:bg-green-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Valider) cliqué pour article ID: {{ $articleElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Marquer comme En attente -->
            @if ($showDeactivateModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Marquer l'article comme En attente</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous marquer l'article "{{ $articleElement->title ?? 'N/A' }}" comme en attente ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deactivateArticle({{ $articleElement->id }})"
                                class="bg-yellow-600 text-white py-2 px-4 rounded-xl hover:bg-yellow-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (En attente) cliqué pour article ID: {{ $articleElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Supprimer -->
            @if ($showDeleteModal)
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl transform transition-all duration-300 ease-in-out">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Supprimer l'article</h3>
                        <p class="mb-4 text-gray-600">Voulez-vous marquer l'article "{{ $articleElement->title ?? 'N/A' }}" comme supprimé ?</p>
                        <div class="flex justify-end space-x-4">
                            <button wire:click="deleteArticle({{ $articleElement->id }})"
                                class="bg-red-600 text-white py-2 px-4 rounded-xl hover:bg-red-700 transition duration-300 shadow-md"
                                x-on:click="console.log('Bouton Oui (Supprimer) cliqué pour article ID: {{ $articleElement->id }}')">
                                Oui
                            </button>
                            <button wire:click="closeModal"
                                class="bg-gray-500 text-white py-2 px-4 rounded-xl hover:bg-gray-600 transition duration-300 shadow-md">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Liste des Articles -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Liste des Articles</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="bg-gray-100 rounded-lg">
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tl-lg">Titre</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Images</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Documents</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Date de publication</th>
                                <th class="p-4 text-sm font-medium text-gray-600">Statut</th>
                                <th class="p-4 text-sm font-medium text-gray-600 rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articles as $article)
                                <tr class="bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                    <td class="p-4 rounded-l-lg">{{ $article->title ?? 'Non défini' }}</td>
                                    <td class="p-4">
                                        @if ($article->images->count() > 0)
                                            <button wire:click="functionShowImageModal({{ $article->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                                                x-on:click="console.log('Bouton Voir les images cliqué pour article ID: {{ $article->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $article->images->count() }} image(s)
                                            </button>
                                        @else
                                            <span class="text-gray-500 text-sm">Aucune image</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if ($article->documents->count() > 0)
                                            <button wire:click="functionShowDocumentModal({{ $article->id }})"
                                                class="inline-flex items-center px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200"
                                                x-on:click="console.log('Bouton Voir les documents cliqué pour article ID: {{ $article->id }}')">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                {{ $article->documents->count() }} doc(s)
                                            </button>
                                        @else
                                            <span class="text-gray-500 text-sm">Aucun document</span>
                                        @endif
                                    </td>
                                    <td class="p-4">{{ $article->published_at ? \Carbon\Carbon::parse($article->published_at)->format('d/m/Y H:i') : 'Non définie' }}</td>
                                    <td class="p-4">
                                        <span class="inline-block px-3 py-1 text-xs font-medium rounded-full
                                            {{ $article->status === 'Success' ? 'bg-green-100 text-green-800' : ($article->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $article->status === 'Success' ? 'Validé' : ($article->status === 'pending' ? 'En attente' : 'Supprimé') }}
                                        </span>
                                    </td>
                                    <td class="p-4 rounded-r-lg">
                                        <div class="flex flex-wrap gap-2">
                                            <button wire:click="functionShowViewModal({{ $article->id }})"
                                                class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                                                x-on:click="console.log('Bouton Visualiser cliqué pour article ID: {{ $article->id }}')">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Voir
                                            </button>
                                            <button wire:click="functionShowShareModal({{ $article->id }})"
                                                class="inline-flex items-center px-2 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200"
                                                x-on:click="console.log('Bouton Partager cliqué pour article ID: {{ $article->id }}')">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                </svg>
                                                Partager
                                            </button>
                                            @if ($article->status === 'pending')
                                                <button wire:click="functionShowActivateModal({{ $article->id }})"
                                                    class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                                                    x-on:click="console.log('Bouton Valider cliqué pour article ID: {{ $article->id }}')">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Valider
                                                </button>
                                            @else
                                                <button wire:click="functionShowDeactivateModal({{ $article->id }})"
                                                    class="inline-flex items-center px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200"
                                                    x-on:click="console.log('Bouton En attente cliqué pour article ID: {{ $article->id }}')">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    En attente
                                                </button>
                                            @endif
                                            <button wire:click="functionShowEditModal({{ $article->id }})"
                                                class="inline-flex items-center px-2 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200"
                                                x-on:click="console.log('Bouton Modifier cliqué pour article ID: {{ $article->id }}')">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Modifier
                                            </button>
                                            <button wire:click="functionShowDeleteModal({{ $article->id }})"
                                                class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                                                x-on:click="console.log('Bouton Supprimer cliqué pour article ID: {{ $article->id }}')">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Supprimer
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.app>