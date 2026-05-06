// ════════════════════════════════════════════════════════════════
// app.js — NutriNova Frontoffice
// Un seul JS — liaison directe avec PHP, pas d'API séparée
// Login unique → redirige admin ou profil selon le rôle
// ════════════════════════════════════════════════════════════════

const CTRL       = '../../controleur/frontoffice/UserControleur.php';
const RESET_CTRL = '../../controleur/frontoffice/ResetControleur.php';
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
  if (page === 'profile') setTimeout(chargerProfil, 60);
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
  if (currentUser) {
    if (authBtns) authBtns.style.display = 'none';
    if (profNav)  profNav.style.display  = 'flex';
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

// ── Menu Nutrition ────────────────────────────────
const menus = {
  'Perte de poids':       {breakfast:'Porridge avoine + fruits rouges',bkcal:'340 kcal',lunch:'Salade quinoa, avocat, poulet',lkcal:'480 kcal',snack:'Pomme + amandes + yaourt',skcal:'190 kcal',dinner:'Saumon vapeur, brocoli, patate douce',dkcal:'420 kcal',prot:32,carb:40,fat:28},
  'Prendre de la masse':  {breakfast:'Omelette 4 œufs, avocat, pain seigle',bkcal:'620 kcal',lunch:'Riz brun, poulet 200g, légumes',lkcal:'780 kcal',snack:'Shake protéine + banane',skcal:'380 kcal',dinner:'Saumon, quinoa, épinards',dkcal:'620 kcal',prot:38,carb:42,fat:20},
  'Maintien':             {breakfast:'Granola + yaourt grec + miel',bkcal:'420 kcal',lunch:'Pâtes complètes, sauce tomate',lkcal:'560 kcal',snack:'Smoothie banane-épinards',skcal:'240 kcal',dinner:'Curry légumes, tofu, riz',dkcal:'480 kcal',prot:25,carb:50,fat:25},
  'Améliorer mes performances':{breakfast:'Gruau + whey + fruits tropicaux',bkcal:'580 kcal',lunch:'Bowl riz, poulet teriyaki, avocat',lkcal:'720 kcal',snack:'Dattes + fruits secs',skcal:'310 kcal',dinner:'Thon, lentilles, roquette',dkcal:'540 kcal',prot:35,carb:47,fat:18},
  'Perdre du poids':      {breakfast:'Toast avocat + œuf poché',bkcal:'390 kcal',lunch:'Soupe miso, tofu, légumes',lkcal:'340 kcal',snack:'Kéfir + baies + curcuma',skcal:'180 kcal',dinner:'Cabillaud, ratatouille',dkcal:'460 kcal',prot:28,carb:44,fat:28},
};
function generateMenu() {
  const obj=document.getElementById('objectif')?.value||'Maintien';
  const m=menus[obj]||menus['Maintien'];
  const s=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
  const sw=(id,v)=>{const el=document.getElementById(id);if(el)el.style.width=v+'%';};
  s('menu-label',obj);s('breakfast',m.breakfast);s('breakfast-kcal',m.bkcal);
  s('lunch',m.lunch);s('lunch-kcal',m.lkcal);s('snack',m.snack);s('snack-kcal',m.skcal);
  s('dinner',m.dinner);s('dinner-kcal',m.dkcal);
  sw('prot-bar',m.prot);sw('carb-bar',m.carb);sw('fat-bar',m.fat);
  s('prot-label',m.prot+'%');s('carb-label',m.carb+'%');s('fat-label',m.fat+'%');
  document.getElementById('menu-result').style.display='block';
  showToast('🧠 Menu généré !');
}

// ── Boutique ──────────────────────────────────────
function filterCategory(btn,cat) {
  document.querySelectorAll('#page-boutique .btn-ghost,#page-boutique .btn-primary').forEach(b=>b.className='btn-ghost');
  btn.className='btn-primary';
  document.querySelectorAll('.product-card').forEach(card=>{
    card.style.display=(cat==='all'||card.dataset.cat===cat)?'':'none';
  });
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
