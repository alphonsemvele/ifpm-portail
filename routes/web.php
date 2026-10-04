<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Spatie\RouteDiscovery\Discovery\Discover;
use App\Mail\notifMail;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\BulletinController;
use App\Http\Controllers\ReleveController;

// Relevé de notes de l'étudiant connecté (PDF)
Route::get('/mon-releve/pdf', [ReleveController::class, 'telecharger'])
    ->middleware(['auth', 'verified'])
    ->name('releve.mine');
 
// Prévisualisation inline (stream pour iframe)
Route::get('/bulletin/{id}/preview', [BulletinController::class, 'previsualiser'])
    ->middleware(['auth', 'verified'])
    ->name('bulletin.preview');
 
// Téléchargement PDF
Route::get('/bulletin/{id}/pdf', [BulletinController::class, 'telecharger'])
    ->middleware(['auth', 'verified'])
    ->name('bulletin.pdf');

// Téléchargement d'une période (plage de mois) en un seul PDF
Route::get('/bulletins/periode', [BulletinController::class, 'telechargerPeriode'])
    ->middleware(['auth', 'verified'])
    ->name('bulletin.periode');
 
// Batch PDF
Route::post('/bulletins/batch-pdf', [BulletinController::class, 'batch'])
    ->middleware(['auth', 'verified'])
    ->name('bulletins.batch.pdf');
 

 Discover::controllers()->in(app_path('Http/Controllers'));

Route::post('/preinscription', ['App\Http\Controllers\Pages\PreinscriptionController', 'logout'])->name('preinscription.index');


/*
 * Cette application est desormais accessible depuis le portail La Majestueuse.
 * La vitrine publique ne s'affiche plus : un visiteur est renvoye au portail,
 * un employe deja connecte rejoint directement son espace.
 *
 * Le controleur et ses vues restent en place ; il suffit de retablir les deux
 * lignes ci-dessous pour la remettre en service.
 */
Route::match(['get', 'post'], '/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->homePath());
    }

    return redirect(config('portail.url'));
})->name('ism');

Route::get('/articles/{id}', ['App\Http\Controllers\vitrineController', 'show'])->name('articles.show');


Route::view('/ifpm', 'ifpm')->name('ifpm');
Route::post('/logout', ['App\Http\Controllers\AuthController', 'logout'])->name('logout');

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/specialite/examens/notes/{examen}/{cours}', function ($examen, $cours) {
    return view('pages.specialite.examens.notes', [
        'examenId' => $examen,
        'coursId'  => $cours,
    ]);
})->name('specialite.examens.notes')->middleware(['auth', 'verified']);

Route::get('/send-test-mail', function () {

$subject = "Email Test";
$content = "Chers administrateurs, de la plateforme ERP  ISM NDAZOA. Nous sommes ravis de constater que notre système de messagerie fonctionne à merveille. Vous revevrez desormais les notifications sur des requêtes venant de toute la plateforme. Merci de patienter";

 Mail::to('elvinyondoua@gmail.com')->send(new notifMail($subject,$content));
    return 'Email sent!';
});

Route::get('/formation/{slug}', function ($slug) {
    $formations = config('formations');
    abort_unless(isset($formations[$slug]), 404);

    return view('formation-detail', [
        'formation' => $formations[$slug],
        'slug' => $slug,
    ]);
})->name('formation.show');
    
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';
