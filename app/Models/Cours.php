<?php

namespace App\Models;

/**
 * Alias historique de Cour : certaines pages (espace etudiant, espace filiere)
 * s'y referent encore. Meme table, memes relations.
 */
class Cours extends Cour
{
    protected $table = 'cours';
}
