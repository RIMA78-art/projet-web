<?php
/**
 * VUE : frontoffice/reset.php
 * Page de réinitialisation du mot de passe
 * Vérifie le token et affiche le formulaire
 */
require_once __DIR__ . '/../../modele/User.php';
require_once __DIR__ . '/../../modele/Session.php';
Session::demarrer();

$token = $_GET['token'] ?? '';
$tokenValide = false;
$emailUser   = '';

if ($token) {
    $user = User::verifierTokenReset($token);
    if ($user) {
        $tokenValide = true;
        $emailUser   = $user['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouveau mot de passe — NutriNova</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --green: #4CAF50; --green-dark: #388E3C; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #f5f7fa;
           min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .card {
      background: white; border-radius: 16px; padding: 40px;
      width: 100%; max-width: 420px; box-shadow: 0 4px 24px rgba(0,0,0,.12);
    }
    .logo { display: flex; align-items: center; gap: 8px; justify-content: center;
            margin-bottom: 24px; font-size: 1.2rem; font-weight: 800; text-decoration: none; color: inherit; }
    .logo span { color: var(--green); }
    h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 6px; }
    p  { color: #666; margin-bottom: 24px; font-size: .9rem; }
    label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 6px; color: #333; }
    input {
      width: 100%; padding: 12px 14px; border: 1.5px solid #e0e0e0;
      border-radius: 8px; font-size: .95rem; margin-bottom: 16px; transition: border-color .2s;
    }
    input:focus { outline: none; border-color: var(--green); }
    button {
      width: 100%; padding: 13px; background: var(--green); color: white;
      border: none; border-radius: 8px; font-size: 1rem; font-weight: 600;
      cursor: pointer; transition: background .2s;
    }
    button:hover { background: var(--green-dark); }
    .alert { border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: .9rem; }
    .alert-error   { background: #fdecea; color: #c62828; border: 1px solid #ef9a9a; }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    a.back { display: block; text-align: center; margin-top: 16px; color: var(--green); font-size: .9rem; }
  </style>
</head>
<body>
<div class="card">
  <a class="logo" href="user.html">🌿 Nutri<span>Nova</span></a>

  <?php if (!$token || !$tokenValide): ?>
    <!-- Token manquant ou expiré -->
    <h2>Lien invalide</h2>
    <p>Ce lien de réinitialisation est invalide ou a expiré (durée : 1 heure).</p>
    <div class="alert alert-error">⚠️ Demandez un nouveau lien depuis la page de connexion.</div>
    <a class="back" href="user.html#page-login">← Retour à la connexion</a>

  <?php else: ?>
    <!-- Formulaire nouveau mot de passe -->
    <h2>Nouveau mot de passe</h2>
    <p>Choisissez un nouveau mot de passe pour <strong><?= htmlspecialchars($emailUser) ?></strong></p>

    <div id="msg"></div>

    <form id="reset-form">
      <input type="hidden" id="reset-token" value="<?= htmlspecialchars($token) ?>">
      <label>Nouveau mot de passe</label>
      <input type="password" id="new-password" placeholder="Minimum 6 caractères" required minlength="6">
      <label>Confirmer le mot de passe</label>
      <input type="password" id="confirm-password" placeholder="Répéter le mot de passe" required minlength="6">
      <button type="submit" id="reset-btn">Enregistrer →</button>
    </form>

    <a class="back" href="user.html#page-login">← Retour à la connexion</a>

    <script>
    document.getElementById('reset-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const token    = document.getElementById('reset-token').value;
      const password = document.getElementById('new-password').value;
      const confirm  = document.getElementById('confirm-password').value;
      const btn      = document.getElementById('reset-btn');
      const msgEl    = document.getElementById('msg');

      if (password !== confirm) {
        msgEl.innerHTML = '<div class="alert alert-error">❌ Les mots de passe ne correspondent pas</div>';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Enregistrement...';

      fetch('../../controleur/frontoffice/ResetControleur.php?action=nouveau-mdp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, password, password_confirm: confirm })
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled = false;
        if (res.success) {
          msgEl.innerHTML = '<div class="alert alert-success">✅ ' + res.message + '</div>';
          btn.style.display = 'none';
          setTimeout(() => window.location.href = 'user.html#page-login', 2000);
        } else {
          btn.textContent = 'Enregistrer →';
          msgEl.innerHTML = '<div class="alert alert-error">❌ ' + res.message + '</div>';
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Enregistrer →';
        msgEl.innerHTML = '<div class="alert alert-error">❌ Erreur : ' + err.message + '</div>';
      });
    });
    </script>
  <?php endif; ?>
</div>
</body>
</html>
