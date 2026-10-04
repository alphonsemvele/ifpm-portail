<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISM NDAZOA</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .email-item { background-color: white; padding: 25px; margin: 15px; max-width: 650px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 48, 135, 0.1); transition: transform 0.2s, box-shadow 0.2s; }
        .email-item:hover { transform: translateY(-5px); box-shadow: 0 6px 15px rgba(0, 48, 135, 0.2); }
        .email-header { background-color: #003087; color: white; padding: 12px; text-align: center; border-radius: 8px 8px 0 0; margin: -25px -25px 15px -25px; }
        .email-header h2 { margin: 0; font-size: 1.6em; font-weight: 600; }
        .email-content h3 { color: #003087; font-size: 1.3em; margin-bottom: 12px; margin-top: 0; }
        .email-content p { color: #444; line-height: 1.7; margin-bottom: 10px; }
        .email-content strong { color: #D81E1E; font-weight: 500; }
        .footer { text-align: center; padding: 15px 0; }
        button { background-color: #D81E1E; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; transition: background-color 0.3s, transform 0.2s; font-size: 1em; }
        button:hover { background-color: #A51717; transform: scale(1.05); }
        .footer p { color: #666; font-size: 0.95em; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="email-content">
        <div class="email-item">
            <div class="email-header">
                <h2>ISM NDAZOA</h2>
            </div>
            <h3>{{$subject}}</h3>
            <p>{!! htmlspecialchars_decode($content) !!}</p>
            <p>Au plaisir de vous recevoir bientôt !</p>
            <p class="footer">L’Équipe Admissions<br>ISM NDAZOA</p>
        </div>
    </div>
</body>
</html>
