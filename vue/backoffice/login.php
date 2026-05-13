<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>NutriNova Admin - Connexion</title>
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "primary-container": "#4caf50",
            "primary-fixed-dim": "#78dc77",
            "surface": "#f7f9fb",
            "surface-container": "#eceef0",
          }
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2 { font-family: 'Manrope', sans-serif; }

    .input-field {
      width: 100%; padding: 12px 16px;
      border: 1.5px solid #e0e3e5; border-radius: 10px;
      font-size: .95rem; outline: none; transition: border-color .2s;
      background: #f7f9fb;
    }
    .input-field:focus { border-color: #4caf50; background: white; }

    .btn-login {
      width: 100%; padding: 13px;
      background: #4caf50; color: white;
      border: none; border-radius: 10px;
      font-size: 1rem; font-weight: 700;
      cursor: pointer; transition: background .2s;
      font-family: 'Manrope', sans-serif;
    }
    .btn-login:hover { background: #388E3C; }
    .btn-login:disabled { background: #a5d6a7; cursor: not-allowed; }

    .alert {
      padding: 12px 16px; border-radius: 8px;
      font-size: .88rem; font-weight: 500;
      margin-bottom: 16px; display: none;
    }
    .alert-error   { background: #fdecea; border:1px solid #ef5350; color: #c62828; }
    .alert-success { background: #e8f5e9; border:1px solid #4caf50; color: #2e7d32; }
  </style>
</head>
<body style="background:#f7f9fb; min-height:100vh; display:flex; align-items:center; justify-content:center;">

  <div style="width:100%; max-width:420px; padding:24px;">

    <!-- Logo -->
    <div style="text-align:center; margin-bottom:32px;">
      <div style="display:inline-flex; align-items:center; gap:10px; margin-bottom:8px;">
        <div style="width:44px; height:44px; background:#4caf50; border-radius:12px;
                    display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
          🌿
        </div>
        <span style="font-family:'Manrope',sans-serif; font-size:1.5rem; font-weight:800; color:#1a1a1a;">
          NutriNova
        </span>
      </div>
      <p style="color:#6f7a6b; font-size:.85rem; letter-spacing:1.5px; text-transform:uppercase; font-weight:600;">
        Espace Administration
      </p>
    </div>

    <!-- Carte -->
    <div style="background:white; border-radius:16px; padding:32px;
                box-shadow:0 4px 24px rgba(0,0,0,.08); border:1px solid #e0e3e5;">

      <h2 style="font-size:1.4rem; font-weight:800; color:#1a1a1a; margin-bottom:4px;">
        Connexion administrateur
      </h2>
      <p style="color:#6f7a6b; font-size:.88rem; margin-bottom:24px;">
        Accès réservé aux administrateurs NutriNova
      </p>

      <!-- Message d'erreur JS -->
      <div id="alert-box" class="alert"></div>

      <!-- Formulaire -->
      <form id="admin-login-form">

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:.85rem; font-weight:600;
                        color:#3f4a3c; margin-bottom:6px;">
            Adresse email
          </label>
          <input type="email" id="a-email" class="input-field"
                 placeholder="admin@nutrinova.fr" required>
        </div>

        <div style="margin-bottom:24px;">
          <label style="display:block; font-size:.85rem; font-weight:600;
                        color:#3f4a3c; margin-bottom:6px;">
            Mot de passe
          </label>
          <input type="password" id="a-password" class="input-field"
                 placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-login" id="a-btn">
          Accéder au panneau →
        </button>

      </form>

      <!-- Lien retour -->
      <div style="text-align:center; margin-top:20px;">
        <a href="../frontoffice/user.html"
           style="font-size:.85rem; color:#6f7a6b; text-decoration:none;">
          ← Retour au site
        </a>
      </div>

    </div>

  </div>

  <script>
    // Contrôleur PHP — chemin depuis vue/backoffice/
    const CTRL = '../../controleur/frontoffice/UserControleur.php';

    // Afficher message si redirigé depuis user.html protégé
    const redirectMessage = sessionStorage.getItem('adminRedirectMessage');
    if (redirectMessage) {
      afficherAlerte(redirectMessage, 'error');
      sessionStorage.removeItem('adminRedirectMessage');
    }
    const params = new URLSearchParams(window.location.search);
    if (params.get('msg')) {
      afficherAlerte(decodeURIComponent(params.get('msg')), 'error');
      history.replaceState(null, '', 'login.php');
    }

    document.getElementById('admin-login-form').addEventListener('submit', function(e) {
      e.preventDefault();

      const email    = document.getElementById('a-email').value.trim();
      const password = document.getElementById('a-password').value;
      const btn      = document.getElementById('a-btn');

      if (!email || !password) {
        afficherAlerte('Veuillez remplir tous les champs', 'error');
        return;
      }

      btn.disabled    = true;
      btn.textContent = 'Connexion...';

      fetch(CTRL + '?action=login', {
        method:      'POST',
        headers:     { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body:        JSON.stringify({ email, password })
      })
      .then(r => r.json())
      .then(res => {
        btn.disabled    = false;
        btn.textContent = 'Accéder au panneau →';

        if (res.success) {
          // Vérifier que c'est bien un admin
          if (res.user.role !== 'admin') {
            afficherAlerte('Accès refusé : droits administrateur requis', 'error');
            return;
          }
          afficherAlerte('✅ Connexion réussie ! Redirection...', 'success');
          setTimeout(() => {
            window.location.href = 'user.html';
          }, 800);

        } else {
          afficherAlerte(res.message || 'Email ou mot de passe incorrect', 'error');
        }
      })
      .catch(err => {
        btn.disabled    = false;
        btn.textContent = 'Accéder au panneau →';
        afficherAlerte('Erreur réseau : ' + err.message, 'error');
      });
    });

    function afficherAlerte(message, type) {
      const box = document.getElementById('alert-box');
      box.className = 'alert alert-' + type;
      box.textContent = (type === 'error' ? '⚠️ ' : '✅ ') + message;
      box.style.display = 'block';
    }
  </script>

</body>
</html>
