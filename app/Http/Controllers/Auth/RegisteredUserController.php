<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'matricule' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'contact' => ['required', 'string', 'max:20'],
            'poste' => ['required', 'string', 'max:255'],
            'entite' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'cropped_photo' => ['required', 'string'],
        ]);

        // Gestion de la photo
        $photoPath = null;
        if ($request->filled('cropped_photo')) {
            // Si une photo recadrée existe (en base64)
            $image = $request->cropped_photo;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'profile_' . time() . '.png';
            
            // Sauvegarder dans storage/app/public/profiles
            Storage::disk('public')->put('profiles/' . $imageName, base64_decode($image));
            $photoPath = 'profiles/' . $imageName;
        } elseif ($request->hasFile('photo')) {
            // Si une photo normale est uploadée sans recadrage
            $photoPath = $request->file('photo')->store('profiles', 'public');
        }

        $user = User::create([
            'matricule' => $request->matricule,
            'name' => $request->name,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'contact' => $request->contact,
            'poste' => $request->poste,
            'section_id' => $request->entite,
            'photo' => $photoPath,
            'password' => Hash::make($request->password),
            'role'=> "personnel",
            'status' => "pending", // Compte non validé par défaut
        ]);

        event(new Registered($user));

        // Ne pas connecter l'utilisateur automatiquement
        // Auth::login($user);

        // Rediriger vers la page register avec un message de succès
        return redirect()->route('register')->with('success', 'Votre demande d\'inscription a ete enregistree avec succes. Vous serez notifie par email une fois que votre compte aura ete valide par un administrateur.');
    }
}