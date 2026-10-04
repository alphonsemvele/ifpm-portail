<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class vitrineController extends Controller
{
    public function index()
    {
        // Récupérer uniquement les articles validés avec leurs images
        $articles = Article::with(['images']) // Charger la première image pour l'aperçu
            ->where('status', 'Success')
            ->latest('published_at') // Trier par date de publication
            ->take(6) // Limiter à 6 articles pour la section
            ->get();
        
        return view('ism', compact('articles'));
    }

    public function show($id)
    {
        // Récupérer l'article avec toutes ses images et documents
        $article = Article::with(['images', 'documents'])
            ->where('status', 'Success')
            ->findOrFail($id);
        
        return view('show', compact('article'));
    }
}