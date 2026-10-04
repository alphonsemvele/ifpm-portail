<?php

/*
|--------------------------------------------------------------------------
| Portail La Majestueuse
|--------------------------------------------------------------------------
|
| Cette application est raccordee au portail : c'est lui qui authentifie
| l'employe, puis nous remet une identite signee. On ne demande plus de mot
| de passe ici.
|
| 'fallback_login' rouvre le formulaire local. A n'activer que si le portail
| est indisponible : c'est l'acces de secours.
|
*/

return [

    'url' => env('PORTAIL_URL', 'http://127.0.0.1:8001'),

    'client_id' => env('PORTAIL_CLIENT_ID'),

    'client_secret' => env('PORTAIL_CLIENT_SECRET'),

    'fallback_login' => (bool) env('PORTAIL_FALLBACK_LOGIN', false),

];
