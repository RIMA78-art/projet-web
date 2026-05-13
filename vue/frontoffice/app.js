// ════════════════════════════════════════════════════════════════
// app.js — NutriNova Frontoffice
// Un seul JS — liaison directe avec PHP, pas d'API séparée
// Login unique → redirige admin ou profil selon le rôle
// ════════════════════════════════════════════════════════════════

const CTRL       = '../../controleur/frontoffice/UserControleur.php';
const RESET_CTRL = '../../controleur/frontoffice/ResetControleur.php';
const FORUM_CTRL = '../../controleur/frontoffice/ForumControleur.php';
const BOUTIQUE_CTRL = '../../controleur/frontoffice/BoutiqueControleur.php';
const NUTRITION_CTRL = '../../controleur/frontoffice/NutritionControleur.php';
const ADMIN_URL  = '../backoffice/user.html';

let currentUser = null;

// ════════════════════════════════════════════════
// INITIALISATION
// ════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  initReveal();
  // Vérifier session puis naviguer
  verifierSessionEtNaviguer();
});

window.addEventListener('hashchange', () => {
  const page = (window.location.hash || '#page-home').replace('#page-', '');
  _afficherPage(page);
});

function verifierSession() {
  fetch(CTRL + '?action=check-session', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      currentUser = res.success ? res.user : null;
      _majNavbar();
    })
    .catch(() => { currentUser = null; _majNavbar(); });
}

// Vérifie la session PUIS charge la page — évite le problème de timing async
function verifierSessionEtNaviguer() {
  fetch(CTRL + '?action=check-session', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      currentUser = res.success ? res.user : null;
      _majNavbar();
      loadPageFromHash();
      // Afficher une erreur Google OAuth si présente dans l'URL
      const params = new URLSearchParams(window.location.search);
      const googleErr = params.get('google_error');
      if (googleErr) {
        setTimeout(() => afficherBanner(decodeURIComponent(googleErr), 'error'), 300);
        history.replaceState(null, '', window.location.pathname + window.location.hash);
      }
    })
    .catch(() => {
      currentUser = null;
      _majNavbar();
      loadPageFromHash();
    });
}

// ════════════════════════════════════════════════
// NAVIGATION
// ════════════════════════════════════════════════
function showPage(name) { _afficherPage(name); }

function _afficherPage(name) {
  // Pages protégées — si pas connecté → login + message
  const pagesProtegees = ['profile'];
  if (pagesProtegees.includes(name) && !currentUser) {
    // Afficher la page login
    const existe = document.getElementById('page-login');
    if (existe) {
      document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
      existe.classList.add('active');
    }
    history.replaceState(null, '', '#page-login');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    // Message JS visible en haut
    setTimeout(() => afficherBanner('Veuillez vous connecter pour accéder à cette page', 'warning'), 100);
    return; // stopper ici
  }
  const existe = document.getElementById('page-' + name);
  const page   = existe ? name : 'home';
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + page)?.classList.add('active');
  if (window.location.hash !== '#page-' + page)
    history.replaceState(null, '', '#page-' + page);
  window.scrollTo({ top: 0, behavior: 'smooth' });
  setTimeout(initReveal, 100);
  if (page === 'profile')   setTimeout(chargerProfil, 60);
  if (page === 'community') setTimeout(loadForumCommunity, 60);
  if (page === 'boutique')  setTimeout(loadBoutiqueProducts, 60);
  if (page === 'panier')    setTimeout(loadCart, 60);
  if (page === 'nutrition') setTimeout(loadNutritionCatalogue, 60);
  // Réinitialiser le formulaire forgot à chaque visite
  if (page === 'forgot') {
    const f = document.getElementById('forgot-form');
    const m = document.getElementById('forgot-msg');
    if (f) { f.reset(); f.style.display = ''; }
    if (m) m.innerHTML = '';
  }
}

function loadPageFromHash() {
  const hash = window.location.hash || '#page-home';
  _afficherPage(hash.startsWith('#page-') ? hash.slice(6) : 'home');
}

// ════════════════════════════════════════════════
// BANNIÈRE JS (message en haut de page)
// ════════════════════════════════════════════════
function afficherBanner(message, type) {
  document.getElementById('auth-banner')?.remove();
  const c = {
    warning: ['#fff3cd','#ffc107','#856404','⚠️'],
    error:   ['#fdecea','#ef5350','#c62828','❌'],
    success: ['#e8f5e9','#4CAF50','#2e7d32','✅'],
  }[type] || ['#fff3cd','#ffc107','#856404','⚠️'];

  const div = document.createElement('div');
  div.id = 'auth-banner';
  div.style.cssText = `position:fixed;top:72px;left:50%;transform:translateX(-50%);
    background:${c[0]};border:1px solid ${c[1]};color:${c[2]};
    padding:13px 22px;border-radius:10px;font-size:.93rem;font-weight:600;
    box-shadow:0 4px 18px rgba(0,0,0,.13);z-index:9999;
    display:flex;align-items:center;gap:10px;max-width:90vw;`;
  div.innerHTML = `<span>${c[3]} ${message}</span>
    <button onclick="this.parentElement.remove()"
      style="background:none;border:none;cursor:pointer;font-size:1.1rem;margin-left:6px;opacity:.7;">✕</button>`;
  document.body.appendChild(div);
  setTimeout(() => div?.remove(), 4500);
}

// ════════════════════════════════════════════════
// NAVBAR
// ════════════════════════════════════════════════
function _majNavbar() {
  const authBtns = document.getElementById('auth-buttons');
  const profNav  = document.getElementById('user-profile-nav');
  // Mettre à jour la UI forum selon l'état de connexion
  const forumCreateRow = document.getElementById('forum-create-btn-row');
  const forumLoginNotice = document.getElementById('forum-login-notice');
  if (currentUser) {
    if (authBtns) authBtns.style.display = 'none';
    if (profNav)  profNav.style.display  = 'flex';
    if (forumCreateRow)   forumCreateRow.style.display   = 'block';
    if (forumLoginNotice) forumLoginNotice.style.display = 'none';
    const ini = ((currentUser.prenom||'?').charAt(0)+(currentUser.nom||'?').charAt(0)).toUpperCase();
    const av = document.getElementById('user-avatar-nav');
    const pn = document.getElementById('user-prenom-nav');
    const nn = document.getElementById('user-nom-nav');
    if (av) av.textContent = ini;
    if (pn) pn.textContent = currentUser.prenom || '';
    if (nn) nn.textContent = currentUser.nom    || '';
  } else {
    if (authBtns) authBtns.style.display = 'flex';
    if (profNav)  profNav.style.display  = 'none';
    if (forumCreateRow)   forumCreateRow.style.display   = 'none';
    if (forumLoginNotice) forumLoginNotice.style.display = 'block';
  }
}
function displayUserInNavbar() { _majNavbar(); }
function checkUserSession()    { verifierSession(); }
function toggleUserMenu(e)     { if(e)e.stopPropagation(); document.getElementById('user-dropdown-menu')?.classList.toggle('active'); }
document.addEventListener('click', () => document.getElementById('user-dropdown-menu')?.classList.remove('active'));

// ════════════════════════════════════════════════
// CONNEXION — un seul login, redirige selon rôle
// ════════════════════════════════════════════════
function handleLogin(e) {
  e.preventDefault();
  const form     = e.target;
  const email    = document.getElementById('login-email')?.value.trim()    || form.querySelector('input[type="email"]').value.trim();
  const password = document.getElementById('login-password')?.value        || form.querySelector('input[type="password"]').value;

  if (!email || !password) {
    afficherBanner('Veuillez remplir tous les champs', 'error'); return;
  }

  // ── Vérification reCAPTCHA ──
  const recaptchaResp = typeof grecaptcha !== 'undefined'
    ? grecaptcha.getResponse(window._loginRecaptchaId ?? undefined)
    : '';
  if (!recaptchaResp) {
    afficherBanner('Veuillez confirmer que vous n\'êtes pas un robot ✅', 'error');
    // Shake the recaptcha box
    const box = document.getElementById('login-recaptcha');
    if (box) { box.style.animation='shake 0.4s'; setTimeout(()=>box.style.animation='',400); }
    return;
  }

  const btn = form.querySelector('button[type="submit"]');
  const txt = btn.textContent;
  btn.disabled = true; btn.textContent = 'Connexion...';

  fetch(CTRL + '?action=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({ email, password })
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false; btn.textContent = txt;
    if (res.success) {
      currentUser = res.user;
      _majNavbar();
      afficherBanner('Bienvenue ' + currentUser.prenom + ' !', 'success');
      setTimeout(() => {
        if (res.user.role === 'admin') window.location.href = ADMIN_URL;
        else _afficherPage('profile');
      }, 900);
    } else {
      afficherBanner(res.message || 'Email ou mot de passe incorrect', 'error');
      // Reset reCAPTCHA après échec
      if (typeof grecaptcha !== 'undefined') grecaptcha.reset();
    }
  })
  .catch(err => { btn.disabled=false; btn.textContent=txt; afficherBanner('Erreur réseau : '+err.message,'error'); });
}

// ════════════════════════════════════════════════
// INSCRIPTION
// ════════════════════════════════════════════════
function handleRegister(e) {
  e.preventDefault();
  const nom          = document.getElementById('fname')?.value.trim()         || '';
  const prenom       = document.getElementById('prenom')?.value.trim()        || '';
  const email        = document.getElementById('email')?.value.trim()         || '';
  const password     = document.getElementById('password')?.value             || '';
  const taille       = parseInt(document.getElementById('taille')?.value)     || 0;
  const poids        = parseFloat(document.getElementById('poids')?.value)    || 0;
  const objectif     = document.getElementById('objectif')?.value             || '';
  const niveau_sport = document.getElementById('niveau-sport')?.value         || '';

  if (!nom || !prenom || !email || !password) {
    afficherBanner('Nom, prénom, email et mot de passe sont obligatoires', 'error'); return;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    afficherBanner('Adresse email invalide', 'error'); return;
  }
  if (password.length < 6) {
    afficherBanner('Le mot de passe doit faire au moins 6 caractères', 'error'); return;
  }

  const btn = e.target.querySelector('button[type="submit"]');
  const txt = btn.textContent;
  btn.disabled = true; btn.textContent = 'Inscription...';

  fetch(CTRL + '?action=register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({ nom, prenom, email, password, taille, poids, objectif, niveau_sport })
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false; btn.textContent = txt;
    if (res.success) {
      currentUser = res.user;
      _majNavbar();
      afficherBanner('Inscription réussie ! Bienvenue ' + currentUser.prenom + ' 🎉', 'success');
      setTimeout(() => _afficherPage('profile'), 1000);
    } else {
      afficherBanner(res.message || "Erreur lors de l'inscription", 'error');
    }
  })
  .catch(err => { btn.disabled=false; btn.textContent=txt; afficherBanner('Erreur réseau : '+err.message,'error'); });
}

// ════════════════════════════════════════════════
// DÉCONNEXION
// ════════════════════════════════════════════════
function logout() {
  if (!confirm('Voulez-vous vous déconnecter ?')) return;

  fetch(CTRL + '?action=logout', { method:'POST', credentials:'same-origin' })
    .finally(() => {
      currentUser = null; _majNavbar();
      _afficherPage('home'); showToast('🚪 Vous avez été déconnecté');
    });
}

// ════════════════════════════════════════════════
// PROFIL
// ════════════════════════════════════════════════
function chargerProfil() {
  if (!currentUser) { afficherBanner('Veuillez vous connecter','warning'); _afficherPage('login'); return; }
  fetch(CTRL + '?action=get-user', { credentials:'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (res.success) { currentUser = {...currentUser, ...res.user}; _remplirProfil(); }
      else if (res.code === 401) { currentUser=null; _majNavbar(); _afficherPage('login'); }
    })
    .catch(() => _remplirProfil());
}
function loadUserProfile() { chargerProfil(); }

function _remplirProfil() {
  const u = currentUser; if (!u) return;
  const s   = (id,v) => { const el=document.getElementById(id); if(el) el.textContent=v??'—'; };
  const val = (id,v) => { const el=document.getElementById(id); if(el) el.value=v??''; };
  const ini = ((u.prenom||'?').charAt(0)+(u.nom||'?').charAt(0)).toUpperCase();
  const d   = new Date(u.date_inscription);
  const ds  = isNaN(d)?'—':d.toLocaleDateString('fr-FR',{year:'numeric',month:'long',day:'numeric'});
  s('profile-avatar',ini); s('profile-fullname',(u.prenom||'')+' '+(u.nom||''));
  s('profile-email',u.email); s('profile-date',ds);
  s('display-nom',u.nom); s('display-prenom',u.prenom); s('display-email',u.email);
  s('display-taille',u.taille+' cm'); s('display-poids',u.poids+' kg');
  s('display-objectif',u.objectif); s('display-niveau-sport',u.niveau_sport);
  s('display-date-full',ds);
  if (u.taille>0&&u.poids>0) {
    const imc=(u.poids/Math.pow(u.taille/100,2)).toFixed(1);
    s('display-imc',imc+' ('+_statutIMC(imc)+')');
  }
  val('edit-nom',u.nom); val('edit-prenom',u.prenom); val('edit-email',u.email);
  val('edit-taille',u.taille); val('edit-poids',u.poids);
  val('edit-objectif',u.objectif); val('edit-niveau-sport',u.niveau_sport);
  document.getElementById('view-mode')?.classList.remove('hidden');
  document.getElementById('edit-mode')?.classList.remove('active');
  chargerActivites();
}

function updateProfile(e) {
  e.preventDefault();
  if (!currentUser) { _afficherPage('login'); return; }
  const data = {
    nom:          document.getElementById('edit-nom')?.value.trim()         || '',
    prenom:       document.getElementById('edit-prenom')?.value.trim()      || '',
    taille:       parseFloat(document.getElementById('edit-taille')?.value) || 0,
    poids:        parseFloat(document.getElementById('edit-poids')?.value)  || 0,
    objectif:     document.getElementById('edit-objectif')?.value           || '',
    niveau_sport: document.getElementById('edit-niveau-sport')?.value       || '',
  };
  fetch(CTRL+'?action=update-user', {
    method:'POST', headers:{'Content-Type':'application/json'},
    credentials:'same-origin', body: JSON.stringify(data)
  })
  .then(r=>r.json())
  .then(res => {
    if (res.success) { Object.assign(currentUser,data); _majNavbar(); _remplirProfil(); toggleEditMode(); afficherBanner('Profil mis à jour','success'); }
    else afficherBanner(res.message,'error');
  })
  .catch(err => afficherBanner('Erreur : '+err.message,'error'));
}

function confirmDeleteAccount() {
  if (!currentUser) return;
  if (!confirm('⚠️ ATTENTION : action IRRÉVERSIBLE.\nToutes vos données seront supprimées.\n\nÊtes-vous sûr(e) ?')) return;
  fetch(CTRL+'?action=delete-user', {
    method:'POST', headers:{'Content-Type':'application/json'},
    credentials:'same-origin', body:JSON.stringify({})
  })
  .then(r=>r.json())
  .then(res => {
    if (res.success) { currentUser=null; _majNavbar(); _afficherPage('home'); showToast('🗑️ Compte supprimé'); }
    else afficherBanner(res.message,'error');
  });
}

function toggleEditMode() {
  document.getElementById('view-mode')?.classList.toggle('hidden');
  document.getElementById('edit-mode')?.classList.toggle('active');
}
function switchProfileTab(tab, button) {
  document.querySelectorAll('.profile-content').forEach(el=>el.classList.remove('active'));
  document.querySelectorAll('.profile-tab').forEach(el=>el.classList.remove('active'));
  document.getElementById('tab-'+tab)?.classList.add('active');
  if (button) button.classList.add('active');
  if (tab==='activites') chargerActivites();
}

// ════════════════════════════════════════════════
// ACTIVITÉS
// ════════════════════════════════════════════════
function chargerActivites() {
  if (!currentUser) return;
  fetch(CTRL+'?action=get-activities', {credentials:'same-origin'})
    .then(r=>r.json())
    .then(res => {
      const list = document.getElementById('activities-list'); if (!list) return;
      if (res.success && res.activities.length>0) {
        list.innerHTML = res.activities.map(a => {
          const d = new Date(a.date_activite);
          const ds = d.toLocaleDateString('fr-FR')+' à '+d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
          return `<div class="activity-item"><div class="activity-date">${ds}</div>
            <span class="activity-type">${a.type_activite}</span>
            <div class="activity-description">${a.description}</div></div>`;
        }).join('');
      } else {
        list.innerHTML = '<p style="text-align:center;color:var(--gray-600);padding:20px;">Aucune activité enregistrée</p>';
      }
    }).catch(()=>{});
}
function loadUserActivities() { chargerActivites(); }
function logActivity(type, description, details=null) {
  if (!currentUser) return;
  fetch(CTRL+'?action=add-activity',{method:'POST',headers:{'Content-Type':'application/json'},
    credentials:'same-origin',body:JSON.stringify({type_activite:type,description,details})}).catch(()=>{});
}

// ════════════════════════════════════════════════
// RESET MOT DE PASSE
// ════════════════════════════════════════════════
function handleForgotPassword(e) {
  e.preventDefault();
  const email  = document.getElementById('forgot-email')?.value.trim();
  const msgEl  = document.getElementById('forgot-msg');
  const form   = document.getElementById('forgot-form');

  if (!email) {
    if (msgEl) msgEl.innerHTML = _forgotBox('error', '❌ Entrez votre adresse email.');
    return;
  }

  const btn = e.target.querySelector('button[type="submit"]');
  btn.disabled    = true;
  btn.textContent = 'Envoi en cours...';
  if (msgEl) msgEl.innerHTML = '';

  fetch(RESET_CTRL + '?action=demander-reset', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ email })
  })
  .then(r => {
    // Lire le texte brut d'abord pour diagnostiquer si ce n'est pas du JSON valide
    return r.text();
  })
  .then(rawText => {
    btn.disabled    = false;
    btn.textContent = 'Envoyer le lien de réinitialisation →';

    let res;
    try {
      res = JSON.parse(rawText);
    } catch(parseErr) {
      // PHP a retourné du HTML/texte au lieu de JSON → afficher le détail
      if (msgEl) msgEl.innerHTML = _forgotBox('error',
        '❌ Réponse serveur invalide (non-JSON).<br>' +
        '<details style="margin-top:8px;font-size:11px;"><summary>Détail technique</summary>' +
        '<pre style="overflow:auto;max-height:120px;background:#f5f5f5;padding:8px;border-radius:4px;">' +
        rawText.replace(/</g,'&lt;').substring(0, 800) + '</pre></details>');
      return;
    }

    if (!msgEl) return;

    if (res.success) {
      if (form) form.style.display = 'none';
      msgEl.innerHTML = _forgotBox('success',
        '<div style="font-size:2rem;margin-bottom:8px;">📬</div>' +
        '<strong>Email envoyé !</strong><br>' + res.message +
        '<br><br><a href="#page-login" style="color:#4CAF50;font-size:13px;">← Retour à la connexion</a>');
    } else {
      // Erreur métier (email inconnu, SMTP en panne, etc.)
      let html = '❌ ' + (res.message || 'Erreur inconnue.');

      // En dev : si le serveur retourne un lien de debug, l'afficher
      if (res.debug_lien) {
        html += '<br><br><small style="color:#555;">🛠 <strong>Mode développement</strong> — lien de reset :<br>' +
                '<a href="' + res.debug_lien + '" style="color:#1976d2;word-break:break-all;">' +
                res.debug_lien + '</a></small>';
      }
      if (res.debug_err) {
        html += '<br><small style="color:#999;font-size:11px;">SMTP : ' + res.debug_err + '</small>';
      }
      msgEl.innerHTML = _forgotBox('error', html);
    }
  })
  .catch(netErr => {
    btn.disabled    = false;
    btn.textContent = 'Envoyer le lien de réinitialisation →';
    if (msgEl) msgEl.innerHTML = _forgotBox('error',
      '❌ Erreur réseau — le serveur PHP est-il lancé ?<br>' +
      '<small style="color:#999;">' + netErr.message + '</small>');
  });
}

// Helper : boîte de message stylée
function _forgotBox(type, html) {
  const styles = {
    success: 'background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;',
    error:   'background:#fdecea;color:#c62828;border:1px solid #ef9a9a;'
  };
  return '<div style="' + (styles[type]||styles.error) +
         'border-radius:8px;padding:14px 16px;margin-bottom:16px;font-size:.9rem;text-align:center;">' +
         html + '</div>';
}

// ════════════════════════════════════════════════
// UI UTILITAIRES(donction aussi de affichage de message )
// ════════════════════════════════════════════════
function showToast(msg) {
  const t = document.getElementById('toast'); if (!t) return;
  t.textContent=msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}
function initReveal() {
  const obs = new IntersectionObserver(entries=>{
    entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('visible'); });
  },{threshold:0.1});
  document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
}
function toggleMobileMenu() { showToast('Utilisez les boutons de navigation !'); }

// ── IMC ──────────────────────────────────────────
function _statutIMC(imc) {
  if(imc<18.5) return 'Insuffisance pondérale';
  if(imc<25)   return 'Poids normal';
  if(imc<30)   return 'Surpoids';
  return 'Obésité';
}
function calculateIMC() {
  const t=parseFloat(document.getElementById('taille')?.value);
  const p=parseFloat(document.getElementById('poids')?.value);
  if(!t||!p){showToast('❌ Entrez taille et poids');return;}
  const imc=(p/Math.pow(t/100,2)).toFixed(1);
  document.getElementById('imc-value').textContent='IMC: '+imc;
  document.getElementById('imc-status').textContent=_statutIMC(imc);
  document.getElementById('imc-result').style.display='block';
  showToast('✅ IMC: '+imc);
}
function calculateIMCAutomatique() {
  const t=parseFloat(document.getElementById('taille')?.value);
  const p=parseFloat(document.getElementById('poids')?.value);
  if(t>0&&p>0){
    const imc=(p/Math.pow(t/100,2)).toFixed(1);
    const v=document.getElementById('imc-value'); if(v) v.textContent='IMC: '+imc;
    const s=document.getElementById('imc-status'); if(s) s.textContent=_statutIMC(imc);
    const r=document.getElementById('imc-result'); if(r) r.style.display='block';
  }
}
function calculateProfileIMC() {
  const t=parseFloat(document.getElementById('edit-taille')?.value);
  const p=parseFloat(document.getElementById('edit-poids')?.value);
  if(t>0&&p>0){
    const imc=(p/Math.pow(t/100,2)).toFixed(1);
    const v=document.getElementById('imc-profile-value'); if(v) v.textContent='IMC: '+imc;
    const s=document.getElementById('imc-profile-status'); if(s) s.textContent=_statutIMC(imc);
    const r=document.getElementById('imc-profile'); if(r) r.style.display='block';
  }
}

// ════════════════════════════════════════════════
// FORUM — Communauté NutriNova
// ════════════════════════════════════════════════
const AVATAR_COLORS = ['#4CAF50','#1E3A8A','#F97316','#8B5CF6','#EC4899','#0891B2','#D97706'];

function _forumAvatarColor(name) {
  let hash = 0;
  for (let i = 0; i < name.length; i++) hash = (hash + name.charCodeAt(i)) % AVATAR_COLORS.length;
  return AVATAR_COLORS[hash];
}

function _forumInitials(name) {
  return (name || '?').split(' ').map(n => n.charAt(0).toUpperCase()).slice(0, 2).join('');
}

function forumTimeAgo(date) {
  const now = new Date();
  const sec = Math.floor((now - new Date(date)) / 1000);
  if (sec < 60) return 'À l\'instant';
  const min = Math.floor(sec / 60);
  if (min < 60) return `Il y a ${min}min`;
  const h = Math.floor(min / 60);
  if (h < 24) return `Il y a ${h}h`;
  const d = Math.floor(h / 24);
  if (d < 7) return `Il y a ${d}j`;
  return new Date(date).toLocaleDateString('fr-FR');
}

// Charge les posts ET les top contributeurs
function loadForumCommunity() {
  loadPostsFromDB();
  loadTopContributors();
}

function loadPostsFromDB() {
  const container = document.getElementById('forum-posts-container');
  if (!container) return;
  container.innerHTML = '<div class="forum-post-loading">Chargement des discussions…</div>';

  fetch(FORUM_CTRL + '?action=get_all_posts&limit=50')
    .then(r => r.json())
    .then(result => {
      if (!result.success || !result.posts) {
        container.innerHTML = '<div class="forum-post-loading">Impossible de charger les posts.</div>';
        return;
      }
      if (result.posts.length === 0) {
        container.innerHTML = '<div class="forum-post-loading">Soyez le premier à poster ! 🚀</div>';
        return;
      }
      const authorName = currentUser ? (currentUser.prenom + ' ' + currentUser.nom).trim() : null;
      let html = '';
      result.posts.forEach(post => {
        const color    = _forumAvatarColor(post.nom_auteur);
        const initials = _forumInitials(post.nom_auteur);
        const timeAgo  = forumTimeAgo(post.created_at);
        const isAuthor = authorName && post.nom_auteur === authorName;
        const isAdmin  = currentUser && currentUser.role === 'admin';

        let actionBtns = `<span class="post-action" id="comment-count-${post.id}">💬 ${post.comment_count || 0} commentaire${(post.comment_count||0)!==1?'s':''}</span>`;
        actionBtns += `<span class="post-action" style="cursor:pointer" onclick="showToast('🔁 Lien copié !')">🔁 Partager</span>`;

        if (isAuthor || isAdmin) {
          const titleSafe   = (post.titre_post   || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
          const contenSafe  = (post.contenu_post || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
          actionBtns += `<span class="post-action" style="cursor:pointer;color:#007bff" onclick="openEditPostModal(${post.id},'${titleSafe}','${contenSafe}')">✏️ Modifier</span>`;
          actionBtns += `<span class="post-action" style="cursor:pointer;color:#dc3545" onclick="deletePost(${post.id})">🗑️ Supprimer</span>`;
        }

        let imgHtml = '';
        if (post.fichier) {
          imgHtml = `<img src="../../uploads/images/${post.fichier}" alt="Image du post" class="forum-post-img" loading="lazy">`;
        }

        html += `
          <div class="forum-post reveal" id="post-${post.id}">
            <div class="post-header">
              <div class="avatar" style="background:linear-gradient(135deg,${color},${color}dd)">${initials}</div>
              <div class="post-meta">
                <strong>${_esc(post.nom_auteur)}</strong>
                <span>${timeAgo}</span>
              </div>
            </div>
            <h4 class="forum-post-title">${_esc(post.titre_post)}</h4>
            <p class="post-content">${_esc(post.contenu_post)}</p>
            ${imgHtml}
            <div class="post-actions">${actionBtns}</div>
          </div>`;
      });
      container.innerHTML = html;
      initReveal();
      initPostModalCounters();
      // Charger les commentaires pour chaque post
      result.posts.forEach(post => loadCommentsForPost(post.id));
    })
    .catch(() => {
      if (container) container.innerHTML = '<div class="forum-post-loading">Erreur réseau. Réessayez plus tard.</div>';
    });
}

function loadTopContributors() {
  const container = document.getElementById('top-contributors-list');
  if (!container) return;

  fetch(FORUM_CTRL + '?action=get_top_contributors&limit=3')
    .then(r => r.json())
    .then(result => {
      if (!result.success || !result.contributors.length) {
        container.innerHTML = '<div style="color:#999;font-size:.9rem">Aucun contributeur encore.</div>';
        return;
      }
      const medals = ['🥇','🥈','🥉'];
      container.innerHTML = result.contributors.map((c, i) => {
        const color    = _forumAvatarColor(c.nom_auteur);
        const initials = _forumInitials(c.nom_auteur);
        return `
          <div style="display:flex;align-items:center;gap:12px">
            <div class="avatar" style="width:36px;height:36px;font-size:12px;background:linear-gradient(135deg,${color},${color}dd)">${initials}</div>
            <div style="flex:1">
              <strong style="font-size:14px">${_esc(c.nom_auteur)}</strong><br>
              <span style="font-size:12px;color:var(--gray-400)">${c.post_count} post${c.post_count>1?'s':''}</span>
            </div>
            <span style="font-size:18px">${medals[i]||'•'}</span>
          </div>`;
      }).join('');
    })
    .catch(() => {});
}

// ── MODAL CRÉER POST ─────────────────────────────────────────────────────────
function showPostModal() {
  if (!currentUser) { showToast('❌ Connectez-vous pour publier'); return; }
  const overlay = document.getElementById('post-modal-overlay');
  if (!overlay) return;
  overlay.querySelector('#post-title-input').value   = '';
  overlay.querySelector('#post-content-input').value = '';
  overlay.querySelector('#post-file-input').value    = '';
  overlay.querySelector('#title-counter').textContent   = '0';
  overlay.querySelector('#content-counter').textContent = '0';
  overlay.querySelector('#file-info').textContent       = '';
  overlay.querySelector('#file-error-message').style.display = 'none';
  overlay.classList.add('active');
}

function closePostModal() {
  document.getElementById('post-modal-overlay')?.classList.remove('active');
}

function initPostModalCounters() {
  [
    ['post-title-input',   'title-counter'],
    ['post-content-input', 'content-counter'],
    ['edit-post-title-input',   'edit-title-counter'],
    ['edit-post-content-input', 'edit-content-counter'],
  ].forEach(([inputId, counterId]) => {
    const el = document.getElementById(inputId);
    const ct = document.getElementById(counterId);
    if (el && ct) {
      el.addEventListener('input', () => { ct.textContent = el.value.length; });
    }
  });

  const fileInput = document.getElementById('post-file-input');
  if (fileInput) fileInput.addEventListener('change', () => forumValidateFileInput(fileInput));
}

function forumValidateFileInput(input) {
  const fileInfo = document.getElementById('file-info');
  const errorMsg = document.getElementById('file-error-message');
  if (!input.files || !input.files.length) {
    if (fileInfo) fileInfo.textContent = '';
    if (errorMsg) errorMsg.style.display = 'none';
    return true;
  }
  const file = input.files[0];
  const maxSize = 5 * 1024 * 1024;
  const allowed = ['jpg','jpeg','png','gif','webp'];
  const ext = file.name.split('.').pop().toLowerCase();

  if (file.size > maxSize) {
    if (errorMsg) { errorMsg.textContent = '❌ Image trop volumineuse (max 5MB)'; errorMsg.style.display = 'block'; }
    if (fileInfo) fileInfo.textContent = '';
    input.value = '';
    return false;
  }
  if (!allowed.includes(ext)) {
    if (errorMsg) { errorMsg.textContent = '❌ Format non autorisé (JPG, PNG, GIF, WEBP)'; errorMsg.style.display = 'block'; }
    if (fileInfo) fileInfo.textContent = '';
    input.value = '';
    return false;
  }
  if (errorMsg) errorMsg.style.display = 'none';
  if (fileInfo) fileInfo.textContent = `✅ ${file.name} (${(file.size/1024).toFixed(1)}KB)`;
  return true;
}

function submitPost() {
  if (!currentUser) { showToast('❌ Connectez-vous pour publier'); return; }

  const titre   = document.getElementById('post-title-input')?.value.trim();
  const contenu = document.getElementById('post-content-input')?.value.trim();
  const errorT  = document.getElementById('post-title-error');
  const errorC  = document.getElementById('post-content-error');

  if (errorT) errorT.style.display = 'none';
  if (errorC) errorC.style.display = 'none';

  let ok = true;
  if (!titre || titre.length < 3) {
    if (errorT) { errorT.textContent = 'Titre requis (min 3 caractères)'; errorT.style.display = 'block'; }
    ok = false;
  }
  if (!contenu || contenu.length < 5) {
    if (errorC) { errorC.textContent = 'Contenu requis (min 5 caractères)'; errorC.style.display = 'block'; }
    ok = false;
  }
  const fileInput = document.getElementById('post-file-input');
  if (fileInput && fileInput.files.length > 0 && !forumValidateFileInput(fileInput)) ok = false;
  if (!ok) return;

  const data = new FormData();
  data.append('titre_post',   titre);
  data.append('contenu_post', contenu);
  if (fileInput && fileInput.files.length > 0) data.append('fichier', fileInput.files[0]);

  fetch(FORUM_CTRL + '?action=create_post', { method: 'POST', body: data, credentials: 'same-origin' })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        closePostModal();
        showToast('✅ Post publié !');
        loadForumCommunity();
      } else {
        showToast('❌ ' + (result.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

// ── MODAL MODIFIER POST ───────────────────────────────────────────────────────
let _editingPostId = null;

function openEditPostModal(postId, titre, contenu) {
  _editingPostId = postId;
  const overlay = document.getElementById('edit-post-modal-overlay');
  if (!overlay) return;
  overlay.querySelector('#edit-post-title-input').value   = titre;
  overlay.querySelector('#edit-post-content-input').value = contenu;
  overlay.querySelector('#edit-title-counter').textContent   = titre.length;
  overlay.querySelector('#edit-content-counter').textContent = contenu.length;
  overlay.querySelector('#edit-post-title-error').style.display   = 'none';
  overlay.querySelector('#edit-post-content-error').style.display = 'none';
  overlay.classList.add('active');
}

function closeEditPostModal() {
  document.getElementById('edit-post-modal-overlay')?.classList.remove('active');
  _editingPostId = null;
}

function submitEditPost() {
  if (!_editingPostId) return;
  const titre   = document.getElementById('edit-post-title-input')?.value.trim();
  const contenu = document.getElementById('edit-post-content-input')?.value.trim();
  const errorT  = document.getElementById('edit-post-title-error');
  const errorC  = document.getElementById('edit-post-content-error');

  if (errorT) errorT.style.display = 'none';
  if (errorC) errorC.style.display = 'none';

  let ok = true;
  if (!titre || titre.length < 3) {
    if (errorT) { errorT.textContent = 'Titre requis (min 3 caractères)'; errorT.style.display = 'block'; }
    ok = false;
  }
  if (!contenu || contenu.length < 5) {
    if (errorC) { errorC.textContent = 'Contenu requis (min 5 caractères)'; errorC.style.display = 'block'; }
    ok = false;
  }
  if (!ok) return;

  const data = new FormData();
  data.append('post_id',      _editingPostId);
  data.append('titre_post',   titre);
  data.append('contenu_post', contenu);

  fetch(FORUM_CTRL + '?action=update_post', { method: 'POST', body: data, credentials: 'same-origin' })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        closeEditPostModal();
        showToast('✅ Post mis à jour');
        loadForumCommunity();
      } else {
        showToast('❌ ' + (result.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

function deletePost(postId) {
  if (!confirm('Supprimer ce post ? Cette action est irréversible.')) return;

  const data = new FormData();
  data.append('post_id', postId);

  fetch(FORUM_CTRL + '?action=delete_post', { method: 'POST', body: data, credentials: 'same-origin' })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        showToast('✅ Post supprimé');
        document.getElementById('post-' + postId)?.remove();
        loadTopContributors();
      } else {
        showToast('❌ ' + (result.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

// ── COMMENTAIRES (modal-based comme gestion forum) ───────────────────────────
let _currentCommentingPostId = null;

function loadCommentsForPost(postId) {
  const postEl = document.getElementById('post-' + postId);
  if (!postEl) return;

  // Supprimer la section commentaires existante
  const existing = postEl.querySelector('.comments-section');
  if (existing) existing.remove();

  fetch(FORUM_CTRL + '?action=get_comments&id_post=' + postId)
    .then(r => r.json())
    .then(result => {
      if (!result.success) return;

      const comments = result.comments || [];
      const authorName = currentUser ? (currentUser.prenom + ' ' + currentUser.nom).trim() : null;
      const isAdmin = currentUser && currentUser.role === 'admin';

      let html = `<div class="comments-section">`;

      // Bouton "Ajouter un commentaire"
      if (currentUser) {
        html += `<div style="margin-bottom:12px;"><button class="btn-primary" style="width:100%;padding:8px;font-size:14px;" onclick="openCommentModal(${postId})">💬 Ajouter un commentaire</button></div>`;
      }

      if (comments.length > 0) {
        html += `<div class="forum-comment-count">${comments.length} commentaire${comments.length > 1 ? 's' : ''}</div>`;
        comments.forEach(c => {
          const color    = _forumAvatarColor(c.nom_auteur);
          const initials = _forumInitials(c.nom_auteur);
          const timeAgo  = forumTimeAgo(c.date_commentaire);
          const canDelete = authorName && (c.nom_auteur.trim().toLowerCase() === authorName.toLowerCase() || isAdmin);

          let actionBtns = '';
          if (canDelete) {
            const cSafe = (c.contenu || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/\n/g,'\\n');
            actionBtns = `<span onclick="openEditCommentModal(${c.id_commentaire},${postId},'${cSafe}')" style="cursor:pointer;color:#007bff;font-size:12px;margin-right:8px;">✏️</span>`;
            actionBtns += `<span onclick="deleteComment(${c.id_commentaire},${postId})" style="cursor:pointer;color:#dc3545;font-size:12px;">🗑️</span>`;
          }

          html += `
            <div class="forum-comment-item" id="comment-item-${c.id_commentaire}">
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                <div class="avatar" style="width:28px;height:28px;font-size:11px;background:linear-gradient(135deg,${color},${color}dd)">${initials}</div>
                <div style="flex:1;">
                  <strong class="forum-comment-author">${_esc(c.nom_auteur)}</strong>
                  <span class="forum-comment-date">${timeAgo}</span>
                </div>
                ${actionBtns}
              </div>
              <p class="forum-comment-text">${_esc(c.contenu)}</p>
            </div>`;
        });
      } else {
        html += `<p class="forum-no-comments">Aucun commentaire pour le moment. Soyez le premier à commenter !</p>`;
      }

      html += `</div>`;

      // Insérer après post-actions
      const postActions = postEl.querySelector('.post-actions');
      if (postActions) postActions.insertAdjacentHTML('afterend', html);

      // Mettre à jour le compteur
      const countEl = document.getElementById('comment-count-' + postId);
      if (countEl) countEl.textContent = `💬 ${comments.length} commentaire${comments.length !== 1 ? 's' : ''}`;
    })
    .catch(() => {});
}

function openCommentModal(postId) {
  if (!currentUser) { showToast('❌ Connectez-vous pour commenter'); return; }
  _currentCommentingPostId = postId;
  const textarea = document.getElementById('comment-content-input');
  const counter  = document.getElementById('comment-counter');
  const errorEl  = document.getElementById('comment-error-message');
  if (textarea) textarea.value = '';
  if (counter)  counter.textContent = '0/2000';
  if (errorEl)  errorEl.style.display = 'none';
  document.getElementById('comment-modal-overlay')?.classList.add('active');

  // Compteur de caractères
  if (textarea) {
    textarea.oninput = () => { if (counter) counter.textContent = textarea.value.length + '/2000'; };
  }
}

function closeCommentModal() {
  document.getElementById('comment-modal-overlay')?.classList.remove('active');
  _currentCommentingPostId = null;
}

function submitComment() {
  if (!_currentCommentingPostId) { showToast('❌ Erreur : ID du post manquant'); return; }
  if (!currentUser) { showToast('❌ Connectez-vous pour commenter'); return; }

  const textarea = document.getElementById('comment-content-input');
  const errorEl  = document.getElementById('comment-error-message');
  const contenu  = textarea?.value.trim();

  if (errorEl) errorEl.style.display = 'none';

  if (!contenu || contenu.length < 2) {
    if (errorEl) { errorEl.textContent = 'Commentaire trop court (min 2 caractères)'; errorEl.style.display = 'block'; }
    return;
  }
  if (contenu.length > 2000) {
    if (errorEl) { errorEl.textContent = 'Commentaire trop long (max 2000 caractères)'; errorEl.style.display = 'block'; }
    return;
  }

  const data = new FormData();
  data.append('id_post', _currentCommentingPostId);
  data.append('contenu', contenu);

  const postId = _currentCommentingPostId;

  fetch(FORUM_CTRL + '?action=create_comment', { method: 'POST', body: data, credentials: 'same-origin' })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        closeCommentModal();
        showToast('✅ Commentaire publié');
        loadCommentsForPost(postId);
      } else {
        if (errorEl) { errorEl.textContent = result.error || 'Erreur'; errorEl.style.display = 'block'; }
        showToast('❌ ' + (result.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

// ── MODAL MODIFIER COMMENTAIRE ────────────────────────────────────────────────
let _editingCommentId = null;
let _editingCommentPostId = null;

function openEditCommentModal(commentId, postId, contenu) {
  _editingCommentId = commentId;
  _editingCommentPostId = postId;
  const textarea = document.getElementById('edit-comment-content-input');
  const counter  = document.getElementById('edit-comment-counter');
  const errorEl  = document.getElementById('edit-comment-error-message');
  if (textarea) { textarea.value = contenu; }
  if (counter)  counter.textContent = (contenu||'').length + '/2000';
  if (errorEl)  errorEl.style.display = 'none';
  document.getElementById('edit-comment-modal-overlay')?.classList.add('active');
  if (textarea) {
    textarea.oninput = () => { if (counter) counter.textContent = textarea.value.length + '/2000'; };
  }
}

function closeEditCommentModal() {
  document.getElementById('edit-comment-modal-overlay')?.classList.remove('active');
  _editingCommentId = null;
  _editingCommentPostId = null;
}

function submitEditComment() {
  if (!_editingCommentId) return;
  if (!currentUser) { showToast('❌ Connectez-vous'); return; }

  const textarea = document.getElementById('edit-comment-content-input');
  const errorEl  = document.getElementById('edit-comment-error-message');
  const contenu  = textarea?.value.trim();

  if (errorEl) errorEl.style.display = 'none';

  if (!contenu || contenu.length < 2) {
    if (errorEl) { errorEl.textContent = 'Commentaire trop court (min 2 caractères)'; errorEl.style.display = 'block'; }
    return;
  }
  if (contenu.length > 2000) {
    if (errorEl) { errorEl.textContent = 'Commentaire trop long (max 2000 caractères)'; errorEl.style.display = 'block'; }
    return;
  }

  const data = new FormData();
  data.append('comment_id', _editingCommentId);
  data.append('contenu', contenu);

  const postId = _editingCommentPostId;

  fetch(FORUM_CTRL + '?action=update_comment', { method: 'POST', body: data, credentials: 'same-origin' })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        closeEditCommentModal();
        showToast('✅ Commentaire modifié');
        loadCommentsForPost(postId);
      } else {
        if (errorEl) { errorEl.textContent = result.error || 'Erreur'; errorEl.style.display = 'block'; }
        showToast('❌ ' + (result.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

function deleteComment(commentId, postId) {
  if (!confirm('Supprimer ce commentaire ?')) return;

  const data = new FormData();
  data.append('comment_id', commentId);

  fetch(FORUM_CTRL + '?action=delete_comment', { method: 'POST', body: data, credentials: 'same-origin' })
    .then(r => r.json())
    .then(result => {
      if (result.success) {
        document.getElementById('comment-item-' + commentId)?.remove();
        showToast('✅ Commentaire supprimé');
        loadCommentsForPost(postId);
      } else {
        showToast('❌ ' + (result.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

// Utilitaire échappement HTML
function _esc(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init compteurs si déjà dans le DOM au chargement
document.addEventListener('DOMContentLoaded', () => { initPostModalCounters(); });

// ── Nutrition (catalogue + plan + top + détail + rating) ──────
let _allNutritionMeals = [];
let _nutritionFilter = 'all';

function switchNutritionTab(tab) {
  ['catalogue','plan','top'].forEach(t => {
    const sec = document.getElementById('nutrition-tab-' + t);
    if (sec) sec.style.display = (t === tab) ? '' : 'none';
  });
  document.querySelectorAll('.nutrition-tab').forEach(b => {
    b.className = b.dataset.tab === tab ? 'btn-primary nutrition-tab' : 'btn-ghost nutrition-tab';
  });
  if (tab === 'catalogue') loadNutritionCatalogue();
  if (tab === 'top') loadTopMeals();
}

function loadNutritionCatalogue() {
  const grid = document.getElementById('nutrition-meals-grid');
  if (!grid) return;
  grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Chargement…</p>';
  fetch(NUTRITION_CTRL + '?action=get_meals', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.meals?.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Aucun repas disponible.</p>';
        _allNutritionMeals = [];
        return;
      }
      _allNutritionMeals = res.meals;
      _renderNutritionGrid();
    })
    .catch(() => { grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#dc3545;">Erreur de chargement.</p>'; });
}

function _renderNutritionGrid() {
  const grid = document.getElementById('nutrition-meals-grid');
  const search = (document.getElementById('nutrition-search')?.value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  let count = 0;
  grid.innerHTML = '';
  _allNutritionMeals.forEach(m => {
    const normName = (m.nom||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    const normType = (m.type||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    if (search && !normName.includes(search) && !normType.includes(search)) return;
    if (_nutritionFilter !== 'all' && (m.type||'').toLowerCase() !== _nutritionFilter) return;
    count++;
    const cal = Number(m.calories||0), prot = Number(m.protein||0), carb = Number(m.carb||0), fat = Number(m.fat||0);
    const imgHtml = m.image
      ? `<img src="../../${escH(m.image)}" alt="${escH(m.nom)}" style="width:100%;height:100%;object-fit:cover;">`
      : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--gray-300);">🍽️</div>`;
    const card = document.createElement('div');
    card.className = 'product-card';
    card.style.cursor = 'pointer';
    card.innerHTML = `
      <div class="product-img-placeholder" style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9);overflow:hidden;">${imgHtml}</div>
      <div class="product-body">
        <span class="product-tag">${escH(m.type||'')}</span>
        <h3>${escH(m.nom)}</h3>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin:8px 0;">
          <span style="background:var(--green-light,#E8F5E9);color:var(--green,#2E7D32);padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:600;">🔥 ${cal.toFixed(0)} kcal</span>
          <span style="background:#E3F2FD;color:#1565C0;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:600;">P ${prot.toFixed(1)}g</span>
          <span style="background:#FFF3E0;color:#E65100;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:600;">G ${carb.toFixed(1)}g</span>
          <span style="background:#FCE4EC;color:#C62828;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:600;">L ${fat.toFixed(1)}g</span>
        </div>
        <div class="product-footer">
          <span class="product-price">${cal.toFixed(0)} kcal</span>
          <button class="btn-primary" type="button">Voir détails</button>
        </div>
      </div>`;
    card.querySelector('button').addEventListener('click', (e) => { e.stopPropagation(); showMealDetail(m.id_meal); });
    card.addEventListener('click', () => showMealDetail(m.id_meal));
    grid.appendChild(card);
  });
  const vc = document.getElementById('nutrition-visible-count');
  if (vc) vc.textContent = count;
  if (count === 0 && _allNutritionMeals.length > 0) {
    grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Aucun repas ne correspond à votre recherche.</p>';
  }
}

function filterNutritionMeals() { _renderNutritionGrid(); }

function setNutritionFilter(btn, filter) {
  _nutritionFilter = filter;
  document.querySelectorAll('.nutrition-filter-btn').forEach(b => b.className = 'btn-ghost nutrition-filter-btn');
  btn.className = 'btn-primary nutrition-filter-btn';
  _renderNutritionGrid();
}

function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ── Meal Detail Modal ─────────────────────────────
function showMealDetail(id) {
  const modal = document.getElementById('meal-detail-modal');
  const content = document.getElementById('meal-detail-content');
  if (!modal || !content) return;
  modal.style.display = '';
  document.body.style.overflow = 'hidden';
  content.innerHTML = '<p style="text-align:center;padding:80px;">Chargement…</p>';
  fetch(NUTRITION_CTRL + '?action=get_meal&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.meal) { content.innerHTML = '<p style="text-align:center;padding:40px;color:#dc3545;">Repas non trouvé.</p>'; return; }
      const m = res.meal;
      const cal = Number(m.calories||0), prot = Number(m.protein||0), carb = Number(m.carb||0), fat = Number(m.fat||0);
      const totalMacro = Math.max(0.1, prot + carb + fat);
      const pp = ((prot/totalMacro)*100).toFixed(1), cp = ((carb/totalMacro)*100).toFixed(1), fp = ((fat/totalMacro)*100).toFixed(1);
      const imgHtml = m.image
        ? `<img src="../../${escH(m.image)}" style="width:100%;max-height:350px;object-fit:cover;">`
        : `<div style="height:200px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;font-size:5rem;">🍽️</div>`;

      const avgR = Number(m.rating_info?.avg_rating || 0);
      const totalR = Number(m.rating_info?.total_ratings || 0);
      const stars = (score) => {
        let s = '';
        for (let i = 1; i <= 5; i++) s += `<span style="color:${i <= Math.round(score) ? '#FBBF24' : '#D1D5DB'};font-size:1.3rem;">★</span>`;
        return s;
      };

      let ingredientsHtml = '';
      if (m.ingredients?.length) {
        ingredientsHtml = `<h3 style="font-size:1.1rem;font-weight:700;margin:24px 0 12px;">Ingrédients (${m.ingredients.length})</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          ${m.ingredients.map(ing => `
            <div style="padding:12px;background:#f9f9f9;border-radius:10px;border:1px solid #eee;">
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <strong>${escH(ing.nom)}</strong>
                <span style="font-size:.8rem;background:#E8F5E9;color:#2E7D32;padding:2px 8px;border-radius:6px;">${Number(ing.quantity).toFixed(1)} u.</span>
              </div>
              <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;font-size:.78rem;">
                <div><span style="color:var(--gray-500);">Cal</span><br><strong>${(ing.calories*ing.quantity).toFixed(0)}</strong></div>
                <div><span style="color:var(--gray-500);">Prot</span><br><strong>${(ing.protein*ing.quantity).toFixed(1)}g</strong></div>
                <div><span style="color:var(--gray-500);">Gluc</span><br><strong>${(ing.carb*ing.quantity).toFixed(1)}g</strong></div>
                <div><span style="color:var(--gray-500);">Lip</span><br><strong>${(ing.fat*ing.quantity).toFixed(1)}g</strong></div>
              </div>
              ${ing.eco_score ? `<div style="margin-top:6px;font-size:.75rem;color:var(--gray-500);">Eco-score: <strong>${escH(ing.eco_score)}</strong></div>` : ''}
            </div>`).join('')}
          </div>`;
      }

      let ratingsHtml = '';
      if (m.ratings?.length) {
        ratingsHtml = `<h3 style="font-size:1.1rem;font-weight:700;margin:24px 0 12px;">Avis des visiteurs</h3>
          <div style="display:flex;flex-direction:column;gap:12px;">
          ${m.ratings.map(r => `
            <div style="padding:12px;background:#fafafa;border-radius:10px;border:1px solid #eee;">
              <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <strong>${escH(r.visitor_name||'Anonyme')}</strong>
                <span>${stars(r.rating)}</span>
              </div>
              ${r.comment ? `<p style="font-size:.9rem;color:var(--gray-600);margin:0;">${escH(r.comment)}</p>` : ''}
              <span style="font-size:.75rem;color:var(--gray-400);">${new Date(r.created_at).toLocaleDateString('fr-FR')}</span>
            </div>`).join('')}
          </div>`;
      }

      content.innerHTML = `
        ${imgHtml}
        <div style="padding:32px;">
          <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:4px;">${escH(m.nom)}</h2>
          <p style="color:var(--gray-500);margin-bottom:16px;">Type : <strong style="color:var(--primary);">${escH(m.type)}</strong></p>
          <!-- Macro stats -->
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
            <div style="text-align:center;padding:16px;background:#FFF3E0;border-radius:12px;">
              <div style="font-size:1.6rem;font-weight:800;">${cal.toFixed(0)}</div><div style="font-size:.75rem;color:var(--gray-500);">Calories</div>
            </div>
            <div style="text-align:center;padding:16px;background:#E8F5E9;border-radius:12px;">
              <div style="font-size:1.6rem;font-weight:800;">${prot.toFixed(1)}g</div><div style="font-size:.75rem;color:var(--gray-500);">Protéines</div>
            </div>
            <div style="text-align:center;padding:16px;background:#E3F2FD;border-radius:12px;">
              <div style="font-size:1.6rem;font-weight:800;">${carb.toFixed(1)}g</div><div style="font-size:.75rem;color:var(--gray-500);">Glucides</div>
            </div>
            <div style="text-align:center;padding:16px;background:#FCE4EC;border-radius:12px;">
              <div style="font-size:1.6rem;font-weight:800;">${fat.toFixed(1)}g</div><div style="font-size:.75rem;color:var(--gray-500);">Lipides</div>
            </div>
          </div>
          <!-- Macro bars -->
          <div style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;margin-bottom:4px;"><span>Protéines</span><span>${pp}%</span></div>
            <div style="height:8px;background:#eee;border-radius:4px;overflow:hidden;margin-bottom:8px;"><div style="height:100%;width:${pp}%;background:var(--green,#4CAF50);border-radius:4px;transition:width .5s;"></div></div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;margin-bottom:4px;"><span>Glucides</span><span>${cp}%</span></div>
            <div style="height:8px;background:#eee;border-radius:4px;overflow:hidden;margin-bottom:8px;"><div style="height:100%;width:${cp}%;background:#1976D2;border-radius:4px;transition:width .5s;"></div></div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;margin-bottom:4px;"><span>Lipides</span><span>${fp}%</span></div>
            <div style="height:8px;background:#eee;border-radius:4px;overflow:hidden;"><div style="height:100%;width:${fp}%;background:#E65100;border-radius:4px;transition:width .5s;"></div></div>
          </div>
          <!-- Rating summary -->
          <div style="background:linear-gradient(135deg,#FFFDE7,#FFF8E1);border:1px solid #FFF176;border-radius:12px;padding:20px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <div><h3 style="margin:0 0 4px;font-size:1.1rem;">Évaluation communautaire</h3><p style="margin:0;font-size:.85rem;color:var(--gray-500);">Partagez votre avis sur ce repas</p></div>
              <div style="text-align:right;">${stars(avgR)}<div style="font-size:1.2rem;font-weight:800;">${avgR.toFixed(1)}/5</div><div style="font-size:.8rem;color:var(--gray-500);">${totalR} évaluation${totalR!==1?'s':''}</div></div>
            </div>
          </div>
          <!-- Rating form -->
          <div style="background:#fff;border:1px solid #eee;border-radius:12px;padding:20px;margin-bottom:20px;">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:12px;">✍️ Laisser une évaluation</h3>
            <div id="rating-stars-input" style="display:flex;gap:4px;margin-bottom:12px;">
              ${[1,2,3,4,5].map(i => `<span onclick="selectRatingStar(${i})" style="cursor:pointer;font-size:1.8rem;color:#D1D5DB;" data-star="${i}">★</span>`).join('')}
            </div>
            <input type="hidden" id="rating-value" value="0">
            <input type="hidden" id="rating-meal-id" value="${m.id_meal}">
            <input type="text" id="rating-name" class="form-control" placeholder="Votre nom (optionnel)" style="margin-bottom:8px;">
            <input type="email" id="rating-email" class="form-control" placeholder="Email (optionnel)" style="margin-bottom:8px;">
            <textarea id="rating-comment" class="form-control" placeholder="Votre commentaire…" rows="3" style="margin-bottom:12px;resize:none;"></textarea>
            <button class="btn-primary" style="width:100%;" onclick="submitMealRating()">Soumettre</button>
          </div>
          ${ingredientsHtml}
          ${ratingsHtml}
        </div>`;
    })
    .catch(() => { content.innerHTML = '<p style="text-align:center;padding:40px;color:#dc3545;">Erreur réseau.</p>'; });
}

function closeMealDetail() {
  const modal = document.getElementById('meal-detail-modal');
  if (modal) modal.style.display = 'none';
  document.body.style.overflow = '';
}

function selectRatingStar(n) {
  document.getElementById('rating-value').value = n;
  document.querySelectorAll('#rating-stars-input span').forEach(s => {
    s.style.color = Number(s.dataset.star) <= n ? '#FBBF24' : '#D1D5DB';
  });
}

function submitMealRating() {
  const mealId = document.getElementById('rating-meal-id')?.value;
  const rating = document.getElementById('rating-value')?.value;
  if (!rating || rating === '0') { showToast('Veuillez sélectionner une note'); return; }
  const fd = new FormData();
  fd.append('meal_id', mealId);
  fd.append('rating', rating);
  fd.append('comment', document.getElementById('rating-comment')?.value || '');
  fd.append('name', document.getElementById('rating-name')?.value || '');
  fd.append('email', document.getElementById('rating-email')?.value || '');
  fetch(NUTRITION_CTRL + '?action=add_rating', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast('⭐ Merci pour votre évaluation !'); showMealDetail(mealId); }
      else showToast('❌ ' + (res.error || 'Erreur'));
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

// ── Nutrition Plan ────────────────────────────────
function generateNutritionPlan() {
  const w = document.getElementById('plan-weight')?.value;
  const h = document.getElementById('plan-height')?.value;
  const a = document.getElementById('plan-age')?.value;
  if (!w || !h || !a) { showToast('Veuillez remplir poids, taille et âge'); return; }
  const fd = new FormData();
  fd.append('weight', w);
  fd.append('height', h);
  fd.append('age', a);
  fd.append('gender', document.getElementById('plan-gender')?.value || 'male');
  fd.append('activity', document.getElementById('plan-activity')?.value || '1.55');
  fd.append('goal', document.getElementById('plan-goal')?.value || 'maintain');
  const prios = [];
  if (document.getElementById('prio-muscle')?.checked) prios.push('maintain_muscle');
  if (document.getElementById('prio-sugar')?.checked) prios.push('reduce_sugar_sodium');
  prios.forEach(p => fd.append('priorities[]', p));

  const box = document.getElementById('nutrition-plan-results');
  box.innerHTML = '<p style="text-align:center;padding:60px;color:var(--gray-400);">Calcul en cours…</p>';
  fetch(NUTRITION_CTRL + '?action=generate_plan', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.plan) { box.innerHTML = `<p style="text-align:center;padding:40px;color:#dc3545;">${escH(res.error||'Erreur')}</p>`; return; }
      const p = res.plan;
      const slots = [
        { key: 'breakfast', label: 'Petit-déjeuner', emoji: '🌅', pct: 25 },
        { key: 'lunch', label: 'Déjeuner', emoji: '🌞', pct: 40 },
        { key: 'dinner', label: 'Dîner', emoji: '🌙', pct: 35 },
      ];
      let slotsHtml = '';
      let totalCal = 0, totalProt = 0, totalCarb = 0, totalFat = 0;
      slots.forEach(sl => {
        const meal = p[sl.key];
        const target = p.targets[sl.key];
        if (meal) {
          totalCal += Number(meal.calories||0);
          totalProt += Number(meal.protein||0);
          totalCarb += Number(meal.carb||0);
          totalFat += Number(meal.fat||0);
          const diff = Number(meal.calories) - target;
          const diffLabel = (diff >= 0 ? '+' : '') + diff.toFixed(0) + ' kcal vs cible';
          const fill = Math.min(100, Math.round((meal.calories / Math.max(1, target)) * 100));
          const fillColor = (fill >= 85 && fill <= 115) ? 'var(--green,#4CAF50)' : '#FFA726';
          const mealImg = meal.image
            ? `<img src="../../${escH(meal.image)}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`
            : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;background:#f5f5f5;border-radius:10px;">🍽️</div>`;
          slotsHtml += `
            <div style="padding:20px;border:1px solid #eee;border-radius:12px;margin-bottom:16px;">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <span style="font-size:1.5rem;">${sl.emoji}</span>
                <div><strong>${sl.label}</strong><br><span style="font-size:.8rem;color:var(--gray-500);">Cible : ${target} kcal (${sl.pct}%)</span></div>
              </div>
              <div style="display:flex;gap:16px;align-items:start;">
                <div style="width:100px;height:100px;flex-shrink:0;overflow:hidden;">${mealImg}</div>
                <div style="flex:1;">
                  <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                    <strong style="font-size:1.1rem;">${escH(meal.nom)}</strong>
                    <span style="font-size:.75rem;padding:2px 8px;border-radius:8px;background:${Math.abs(diff)<=50?'#E8F5E9':'#f5f5f5'};color:${Math.abs(diff)<=50?'#2E7D32':'var(--gray-500)'};">${diffLabel}</span>
                  </div>
                  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;font-size:.78rem;margin-bottom:8px;">
                    <div style="text-align:center;background:#FFF3E0;padding:6px;border-radius:8px;"><strong>${Number(meal.calories).toFixed(0)}</strong><br>kcal</div>
                    <div style="text-align:center;background:#E8F5E9;padding:6px;border-radius:8px;"><strong>${Number(meal.protein).toFixed(1)}g</strong><br>Prot</div>
                    <div style="text-align:center;background:#E3F2FD;padding:6px;border-radius:8px;"><strong>${Number(meal.carb).toFixed(1)}g</strong><br>Gluc</div>
                    <div style="text-align:center;background:#FCE4EC;padding:6px;border-radius:8px;"><strong>${Number(meal.fat).toFixed(1)}g</strong><br>Lip</div>
                  </div>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;height:6px;background:#eee;border-radius:3px;overflow:hidden;"><div style="height:100%;width:${fill}%;background:${fillColor};border-radius:3px;"></div></div>
                    <span style="font-size:.75rem;color:var(--gray-400);font-weight:600;">${fill}%</span>
                  </div>
                  <button class="btn-ghost" style="margin-top:8px;font-size:.8rem;" onclick="showMealDetail(${meal.id_meal})">Voir détails →</button>
                </div>
              </div>
            </div>`;
        } else {
          slotsHtml += `<div style="padding:20px;border:1px solid #eee;border-radius:12px;margin-bottom:16px;color:var(--gray-500);"><span style="font-size:1.3rem;">${sl.emoji}</span> ${sl.label} — <em>Aucun repas de ce type dans le catalogue.</em></div>`;
        }
      });

      let prioHtml = '';
      if (p.priorities?.length) {
        const labels = { maintain_muscle: 'Maintien musculaire', reduce_sugar_sodium: 'Réduction sucre/sodium' };
        prioHtml = `<div style="margin-top:16px;padding:12px;background:#EDE7F6;border:1px solid #D1C4E9;border-radius:10px;">
          <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#5E35B1;margin-bottom:6px;">Profil multi-objectifs actif</div>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">${p.priorities.map(pr => `<span style="background:#fff;border:1px solid #D1C4E9;color:#5E35B1;padding:2px 10px;border-radius:12px;font-size:.8rem;font-weight:600;">${labels[pr]||pr}</span>`).join('')}</div>
          ${p.macro_targets ? `<div style="font-size:.8rem;color:#5E35B1;margin-top:8px;">Cibles : ${p.macro_targets.protein}g prot, ${p.macro_targets.carb}g gluc, ${p.macro_targets.fat}g lip</div>` : ''}
        </div>`;
      }

      // QR code via external API
      const qrData = encodeURIComponent(window.location.href);
      const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${qrData}`;

      box.innerHTML = `
        <!-- Summary -->
        <div style="background:#fff;border-radius:14px;border:1px solid #eee;padding:24px;margin-bottom:20px;">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">📊 Résumé de vos besoins</h3>
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
            <div style="text-align:center;padding:16px;background:#E8F5E9;border-radius:12px;"><div style="font-size:1.8rem;font-weight:800;color:var(--green,#2E7D32);">${p.bmr}</div><div style="font-size:.7rem;color:var(--gray-500);text-transform:uppercase;font-weight:600;">BMR (kcal)</div></div>
            <div style="text-align:center;padding:16px;background:#E8F5E9;border-radius:12px;"><div style="font-size:1.8rem;font-weight:800;color:var(--green,#2E7D32);">${p.tdee}</div><div style="font-size:.7rem;color:var(--gray-500);text-transform:uppercase;font-weight:600;">TDEE (kcal/j)</div></div>
            <div style="text-align:center;padding:16px;background:#FFF3E0;border-radius:12px;"><div style="font-size:1.8rem;font-weight:800;color:#E65100;">${p.targets.breakfast}</div><div style="font-size:.7rem;color:var(--gray-500);text-transform:uppercase;font-weight:600;">Matin 25%</div></div>
            <div style="text-align:center;padding:16px;background:#E3F2FD;border-radius:12px;"><div style="font-size:1.8rem;font-weight:800;color:#1565C0;">${p.targets.lunch}</div><div style="font-size:.7rem;color:var(--gray-500);text-transform:uppercase;font-weight:600;">Midi 40%</div></div>
          </div>
          ${prioHtml}
        </div>
        <!-- Meal slots -->
        <div style="background:#fff;border-radius:14px;border:1px solid #eee;padding:24px;margin-bottom:20px;">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">🍽️ Plan de la journée</h3>
          ${slotsHtml}
        </div>
        <!-- Daily total -->
        <div style="background:#fff;border-radius:14px;border:1px solid #eee;padding:24px;margin-bottom:20px;">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:16px;">📋 Total journée</h3>
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
            <div style="text-align:center;padding:14px;background:#FFF3E0;border-radius:12px;"><div style="font-size:1.5rem;font-weight:800;color:#E65100;">${totalCal.toFixed(0)}</div><div style="font-size:.7rem;color:var(--gray-500);">kcal total</div><div style="font-size:.7rem;color:var(--gray-400);">Obj: ${p.tdee}</div></div>
            <div style="text-align:center;padding:14px;background:#E8F5E9;border-radius:12px;"><div style="font-size:1.5rem;font-weight:800;color:var(--green,#2E7D32);">${totalProt.toFixed(1)}g</div><div style="font-size:.7rem;color:var(--gray-500);">Protéines</div></div>
            <div style="text-align:center;padding:14px;background:#E3F2FD;border-radius:12px;"><div style="font-size:1.5rem;font-weight:800;color:#1565C0;">${totalCarb.toFixed(1)}g</div><div style="font-size:.7rem;color:var(--gray-500);">Glucides</div></div>
            <div style="text-align:center;padding:14px;background:#FCE4EC;border-radius:12px;"><div style="font-size:1.5rem;font-weight:800;color:#C62828;">${totalFat.toFixed(1)}g</div><div style="font-size:.7rem;color:var(--gray-500);">Lipides</div></div>
          </div>
        </div>
        <!-- Share & Export -->
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
          <div style="background:#fff;border-radius:14px;border:1px solid #eee;padding:20px;text-align:center;">
            <h4 style="font-size:.95rem;font-weight:700;margin-bottom:8px;">📱 Partager</h4>
            <img src="${qrUrl}" alt="QR Code" style="width:160px;height:160px;margin:0 auto;border-radius:8px;border:2px solid #eee;">
          </div>
          <div style="display:flex;flex-direction:column;gap:10px;justify-content:center;">
            <button class="btn-primary btn-lg" onclick="window.print()" style="width:100%;">📄 Exporter PDF (Imprimer)</button>
            <a href="https://wa.me/?text=${encodeURIComponent('Mon plan nutritionnel NutriNova: BMR '+p.bmr+' kcal, TDEE '+p.tdee+' kcal/jour')}" target="_blank" rel="noopener" class="btn-primary btn-lg" style="width:100%;background:#25D366;text-align:center;text-decoration:none;">💬 Partager sur WhatsApp</a>
          </div>
        </div>`;
      showToast('📊 Plan nutritionnel généré !');
    })
    .catch(() => { box.innerHTML = '<p style="text-align:center;padding:40px;color:#dc3545;">Erreur réseau.</p>'; });
}

// ── Top Meals ─────────────────────────────────────
function loadTopMeals() {
  const grid = document.getElementById('nutrition-top-grid');
  if (!grid) return;
  grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Chargement…</p>';
  fetch(NUTRITION_CTRL + '?action=top_meals', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (!res.success || !res.meals?.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Aucun repas noté pour le moment.</p>';
        return;
      }
      grid.innerHTML = '';
      res.meals.forEach((m, idx) => {
        const avg = Number(m.avg_rating||0);
        const total = Number(m.total_ratings||0);
        const medal = idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : `#${idx+1}`;
        const stars = [];
        for (let i = 1; i <= 5; i++) stars.push(`<span style="color:${i <= Math.round(avg) ? '#FBBF24' : '#D1D5DB'};">★</span>`);
        const imgHtml = m.image
          ? `<img src="../../${escH(m.image)}" style="width:100%;height:100%;object-fit:cover;">`
          : `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;background:#f5f5f5;">🍽️</div>`;
        const card = document.createElement('div');
        card.className = 'product-card';
        card.style.cursor = 'pointer';
        card.innerHTML = `
          <div class="product-img-placeholder" style="overflow:hidden;position:relative;">
            ${imgHtml}
            <span style="position:absolute;top:8px;left:8px;background:#fff;padding:4px 10px;border-radius:8px;font-size:1.1rem;font-weight:800;box-shadow:0 2px 6px rgba(0,0,0,.1);">${medal}</span>
          </div>
          <div class="product-body">
            <span class="product-tag">${escH(m.type||'')}</span>
            <h3>${escH(m.nom)}</h3>
            <div style="margin:8px 0;">${stars.join('')} <span style="font-size:.85rem;font-weight:700;margin-left:4px;">${avg.toFixed(1)}</span> <span style="font-size:.75rem;color:var(--gray-400);">(${total})</span></div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;font-size:.75rem;">
              <span style="background:#FFF3E0;color:#E65100;padding:2px 8px;border-radius:6px;">🔥 ${Number(m.calories||0).toFixed(0)} kcal</span>
              <span style="background:#E8F5E9;color:#2E7D32;padding:2px 8px;border-radius:6px;">P ${Number(m.protein||0).toFixed(1)}g</span>
            </div>
            <div class="product-footer" style="margin-top:12px;">
              <span></span>
              <button class="btn-primary" type="button">Voir détails</button>
            </div>
          </div>`;
        card.querySelector('button').addEventListener('click', (e) => { e.stopPropagation(); showMealDetail(m.id_meal); });
        card.addEventListener('click', () => showMealDetail(m.id_meal));
        grid.appendChild(card);
      });
    })
    .catch(() => { grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#dc3545;">Erreur réseau.</p>'; });
}

// Legacy wrapper
function generateMenu() { switchNutritionTab('plan'); }

// ── Boutique ──────────────────────────────────────
let currentCartTotal = 0;

function getCategoryMeta(cat) {
  const map = {
    bio:         { label: 'Bio & Local',  emoji: '🥗', bg: 'linear-gradient(135deg,#E8F5E9,#C8E6C9)' },
    complement:  { label: 'Complément',   emoji: '💊', bg: 'linear-gradient(135deg,#E3F2FD,#BBDEFB)' },
    sport:       { label: 'Sport',        emoji: '🏃', bg: 'linear-gradient(135deg,#FCE4EC,#F48FB1)' },
    accessoire:  { label: 'Accessoire',   emoji: '🎽', bg: 'linear-gradient(135deg,#F3E5F5,#E1BEE7)' }
  };
  return map[cat] || map.complement;
}

function escHtmlShop(v) {
  return String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function loadBoutiqueProducts() {
  const grid = document.getElementById('boutique-product-grid');
  if (!grid) return;
  grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Chargement…</p>';

  fetch(BOUTIQUE_CTRL + '?action=get_products', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (!res || !res.success || !Array.isArray(res.products) || res.products.length === 0) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Aucun produit disponible.</p>';
        return;
      }
      grid.innerHTML = '';
      res.products.forEach(p => {
        const prix = Number(p.prix || 0);
        if (!p.nom || prix <= 0) return;
        const cat = p.categorie || 'complement';
        const meta = getCategoryMeta(cat);
        const desc = escHtmlShop(p.description || 'Produit de qualité.');
        const nom = escHtmlShop(p.nom);
        const card = document.createElement('div');
        card.className = 'product-card';
        card.setAttribute('data-cat', cat);
        card.innerHTML = `
          <div class="product-img-placeholder" style="background:${meta.bg}">${meta.emoji}</div>
          <div class="product-body">
            <span class="product-tag">${meta.label}</span>
            <h3>${nom}</h3>
            <p>${desc}</p>
            <div class="product-footer">
              <span class="product-price">${prix.toFixed(2).replace('.',',')}€</span>
              <button class="btn-primary" type="button">Ajouter</button>
            </div>
          </div>`;
        card.querySelector('button').addEventListener('click', () => addToCart(p.nom, prix, p.description || ''));
        grid.appendChild(card);
      });
    })
    .catch(() => {
      grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#dc3545;">Erreur de chargement.</p>';
    });
}

function filterCategory(btn, cat) {
  document.querySelectorAll('#page-boutique .btn-ghost,#page-boutique .btn-primary').forEach(b=>b.className='btn-ghost');
  btn.className='btn-primary';
  document.querySelectorAll('#boutique-product-grid .product-card').forEach(card=>{
    card.style.display=(cat==='all'||card.dataset.cat===cat)?'':'none';
  });
}

// ── Cart ──────────────────────────────────────────
function addToCart(nom, prix, description) {
  if (!currentUser) {
    showToast('Connectez-vous pour ajouter au panier');
    _afficherPage('login');
    return;
  }
  const fd = new FormData();
  fd.append('email', currentUser.email);
  fd.append('nom', nom);
  fd.append('prix', prix);
  fd.append('description', description || '');
  fetch(BOUTIQUE_CTRL + '?action=add_to_cart', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (res.success) showToast('✅ Ajouté au panier !');
      else showToast('❌ ' + (res.error || 'Erreur'));
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

function loadCart() {
  const cartDiv = document.getElementById('cart-items');
  if (!cartDiv) return;
  if (!currentUser) {
    cartDiv.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Connectez-vous pour voir votre panier.</p>';
    currentCartTotal = 0;
    updateCartSummary();
    return;
  }
  cartDiv.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Chargement…</p>';
  fetch(BOUTIQUE_CTRL + '?action=get_cart&email=' + encodeURIComponent(currentUser.email), { credentials: 'same-origin' })
    .then(r => r.json())
    .then(items => {
      if (!Array.isArray(items) || items.length === 0) {
        cartDiv.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--gray-500);">Votre panier est vide.</p>';
        currentCartTotal = 0;
        updateCartSummary();
        return;
      }
      let html = '', total = 0;
      items.forEach(item => {
        const price = Number(item.Prix) || 0;
        total += price;
        const meta = getCategoryMeta('complement');
        html += `<div class="product-card">
          <div class="product-img-placeholder" style="background:${meta.bg}">${meta.emoji}</div>
          <div class="product-body">
            <h3>${escHtmlShop(item.Nom)}</h3>
            <div class="product-footer">
              <span class="product-price">${price.toFixed(2).replace('.',',')}€</span>
              <button class="btn-ghost" onclick="removeFromCart(${item.id})" style="padding:4px 8px;font-size:12px;">Supprimer</button>
            </div>
          </div>
        </div>`;
      });
      currentCartTotal = total;
      cartDiv.innerHTML = html;
      updateCartSummary();
    })
    .catch(() => {
      cartDiv.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#dc3545;">Erreur de chargement.</p>';
    });
}

function updateCartSummary() {
  const el = document.getElementById('cart-total-value');
  const btn = document.getElementById('confirm-order-btn');
  if (el) el.textContent = currentCartTotal.toFixed(2).replace('.',',') + '€';
  if (btn) {
    btn.disabled = currentCartTotal <= 0;
    btn.style.opacity = currentCartTotal <= 0 ? '.6' : '1';
    btn.style.cursor = currentCartTotal <= 0 ? 'not-allowed' : 'pointer';
  }
}

function removeFromCart(id) {
  const fd = new FormData();
  fd.append('id', id);
  fd.append('email', currentUser ? currentUser.email : '');
  fetch(BOUTIQUE_CTRL + '?action=remove_from_cart', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast('✅ Produit retiré'); loadCart(); }
      else showToast('❌ ' + (res.error || 'Erreur'));
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

function goToOrderForm() {
  if (currentCartTotal <= 0) { showToast('❌ Panier vide'); return; }
  const totalField = document.getElementById('order-total');
  if (totalField) totalField.value = currentCartTotal.toFixed(2).replace('.',',') + '€';
  _afficherPage('order');
}

function submitOrder(e) {
  e.preventDefault();
  const name = (document.getElementById('order-name')?.value || '').trim();
  const address = (document.getElementById('order-address')?.value || '').trim();
  const phone = (document.getElementById('order-phone')?.value || '').trim();
  if (!name || !address) { showToast('❌ Nom et adresse requis'); return; }
  if (!/^\d{8}$/.test(phone)) { showToast('❌ Téléphone : exactement 8 chiffres'); return; }
  if (currentCartTotal <= 0) { showToast('❌ Panier vide'); return; }

  const fd = new FormData();
  fd.append('customer_name', name);
  fd.append('address', address);
  fd.append('telephone', phone);
  fd.append('total_price', currentCartTotal.toFixed(2));
  if (currentUser) fd.append('user_email', currentUser.email);

  fetch(BOUTIQUE_CTRL + '?action=create_order', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('✅ Commande confirmée !');
        document.getElementById('order-form')?.reset();
        currentCartTotal = 0;
        _afficherPage('boutique');
      } else {
        showToast('❌ ' + (res.error || 'Erreur'));
      }
    })
    .catch(() => showToast('❌ Erreur réseau'));
}

// ── Contact ───────────────────────────────────────
function handleContact(e) {
  e.preventDefault();
  showToast('✅ Message envoyé ! Réponse sous 24h.');
  e.target.reset();
}

// Google OAuth — chemin dynamique
function redirectToGoogle() {
  var base = window.location.pathname.replace(/\/vue\/frontoffice\/.*$/, '');
  window.location.href = base + '/controleur/frontoffice/GoogleControleur.php?action=redirect';
}
