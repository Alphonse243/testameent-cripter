<?php
$filename = __DIR__ . '/testament.txt';

// Fonction utilitaire pour chiffrer/déchiffrer une réponse
function encrypt_answer($answer, $secret) {
    $key = hash('sha256', $secret);
    $ivlen = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($answer, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}
function decrypt_answer($encrypted, $secret) {
    $key = hash('sha256', $secret);
    $data = base64_decode($encrypted);
    $ivlen = openssl_cipher_iv_length('AES-256-CBC');
    $iv = substr($data, 0, $ivlen);
    $ciphertext = substr($data, $ivlen);
    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
}

$answer_secret = 'questions-secret'; // À garder secret dans le code

if (isset($_POST['save'])) {
    // Récupérer les questions et le message
    $q1 = $_POST['q1'] ?? '';
    $q2 = $_POST['q2'] ?? '';
    $q3 = $_POST['q3'] ?? '';
    $message = $_POST['message'] ?? '';

    // Chiffrer les réponses
    $ea1 = encrypt_answer($_POST['a1'], $answer_secret);
    $ea2 = encrypt_answer($_POST['a2'], $answer_secret);
    $ea3 = encrypt_answer($_POST['a3'], $answer_secret);

    // Générer la clé à partir des réponses
    $key = hash('sha256', $_POST['a1'] . $_POST['a2'] . $_POST['a3']);

    // Chiffrer le message
    $ivlen = openssl_cipher_iv_length('AES-256-CBC');
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($message, 'AES-256-CBC', $key, 0, $iv);

    // Encoder IV + message chiffré pour stockage
    $output = base64_encode($iv . $encrypted);

    // Stocker dans un fichier (questions + réponses chiffrées + message chiffré)
    $data = [
        'q1' => $q1,
        'q2' => $q2,
        'q3' => $q3,
        'ea1' => $ea1,
        'ea2' => $ea2,
        'ea3' => $ea3,
        'encrypted' => $output
    ];
    $writeResult = @file_put_contents($filename, json_encode($data));
    if ($writeResult === false) {
        $error = "Erreur : Impossible d'écrire dans le fichier testament.txt. Vérifiez les permissions du dossier.";
    } else {
        $saved = true;
    }
}

// Lecture du fichier pour affichage/déchiffrement
$filedata = null;
if (file_exists($filename)) {
    $filedata = json_decode(file_get_contents($filename), true);
}

$decrypted = '';
if (isset($_POST['decrypt']) && $filedata) {
    $a1 = $_POST['a1'] ?? '';
    $a2 = $_POST['a2'] ?? '';
    $a3 = $_POST['a3'] ?? '';
    $key = hash('sha256', $a1 . $a2 . $a3);
    $data = base64_decode($filedata['encrypted']);
    $ivlen = openssl_cipher_iv_length('AES-256-CBC');
    $iv = substr($data, 0, $ivlen);
    $ciphertext = substr($data, $ivlen);
    $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Testament Crypté</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="form-section">
        <h5 class="mb-3">Déchiffrer un testament depuis le fichier</h5>
        <?php if ($decrypted !== ''): ?>
            <div class="mt-4">
                <h6>Message déchiffré :</h6>
                <textarea rows="5" class="form-control" readonly><?= htmlspecialchars($decrypted) ?></textarea>
                <?php if ($decrypted === false): ?>
                    <div class="alert alert-danger">Erreur : Mauvaises réponses ou texte corrompu.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($filedata): ?>
            <form method="post" autocomplete="off">
                <input type="hidden" name="decrypt" value="1">
                <div class="mb-3">
                    <label><?= htmlspecialchars($filedata['q1']) ?></label>
                    <input type="text" name="a1" class="form-control"  placeholder="En minuscule" required>
                </div>
                <div class="mb-3">
                    <label><?= htmlspecialchars($filedata['q2']) ?></label>
                    <input type="text" name="a2" class="form-control" placeholder="En minuscule" required>
                </div>
                <div class="mb-3">
                    <label><?= htmlspecialchars($filedata['q3']) ?></label>
                    <input type="text" name="a3" class="form-control" placeholder="En minuscule" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Déchiffrer</button>
            </form>
            
            <div class="mt-4">
                <h6>Testament crypté (copiez ce texte pour l'utiliser ailleurs) :</h6>
                <textarea rows="5" class="form-control" readonly><?= htmlspecialchars($filedata['encrypted']) ?></textarea>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Aucun testament sauvegardé pour le moment.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Animation sur focus input
    document.querySelectorAll('.form-control, input[type="text"], textarea').forEach(function(input) {
        input.addEventListener('focus', function() {
            var label = this.parentElement.querySelector('label');
            if(label) label.style.color = "#6366f1";
        });
        input.addEventListener('blur', function() {
            var label = this.parentElement.querySelector('label');
            if(label) label.style.color = "#34495e";
        });
    });
</script>
</body>
</html>