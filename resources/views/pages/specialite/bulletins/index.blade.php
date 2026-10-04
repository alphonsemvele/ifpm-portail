<?php
use function Laravel\Folio\{name, middleware};

name('specialite.bulletins.index');
middleware(['auth', 'verified']);

/*
 * Cette page affichait des bulletins fictifs. Les bulletins de paie reels de
 * chaque membre du personnel sont servis par l'espace personnel.
 */
?>
@php(redirect('/personnel/bulletins')->send())
