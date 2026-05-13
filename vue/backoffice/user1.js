// ── CONFIGURATION ─────────────────────────────────────────────────────────────
const API_URL = '../../controleur/backoffice/AdminControleur.php';
let allUsers = [];

// ── RÈGLES DE VALIDATION ─────────────────────────────────────────────────────
const VALIDATION_RULES = {
  nom: { minLength: 2, maxLength: 100, pattern: /^[a-zA-ZÀ-ÿ\s'-]+$/, message: "Le nom doit contenir uniquement des lettres (2-100 caractères)" },
  prenom: { minLength: 2, maxLength: 100, pattern: /^[a-zA-ZÀ-ÿ\s'-]+$/, message: "Le prénom doit contenir uniquement des lettres (2-100 caractères)" },
  email: { pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: "Format d'email invalide" },
  password: { minLength: 8, maxLength: 255, pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/, message: "Mot de passe minimum 8 caractères (majuscule, minuscule, chiffre)" },
  taille: { min: 100, max: 250, message: "La taille doit être entre 100 et 250 cm" },
  poids: { min: 30, max: 300, message: "Le poids doit être entre 30 et 300 kg" }
};

const OBJECTIFS = ['Perdre du poids', 'Prendre de la masse', 'Améliorer mes performances', 'Manger mieux & durablement', 'Réduire mon impact écologique', 'Perte de poids'];
const NIVEAUX = ['Débutant', 'Intermédiaire', 'Avancé', 'Athlète'];

// ── FONCTIONS DE VALIDATION (sur valeurs, sans DOM) ──────────────────────────
function validateTextField(fieldName, value) {
  const rule = VALIDATION_RULES[fieldName];
  if (!rule) return { isValid: true, message: "" };
  if (rule.minLength && value.length < rule.minLength) return { isValid: false, message: rule.message };
  if (rule.maxLength && value.length > rule.maxLength) return { isValid: false, message: rule.message };
  if (rule.pattern && !rule.pattern.test(value)) return { isValid: false, message: rule.message };
  return { isValid: true, message: "" };
}

function validateNumberField(fieldName, value) {
  const rule = VALIDATION_RULES[fieldName];
  const num = parseFloat(value);
  if (!rule) return { isValid: true, message: "" };
  if (isNaN(num)) return { isValid: false, message: `${fieldName} doit être un nombre` };
  if (rule.min !== undefined && num < rule.min) return { isValid: false, message: rule.message };
  if (rule.max !== undefined && num > rule.max) return { isValid: false, message: rule.message };
  return { isValid: true, message: "" };
}

function validateChoiceField(fieldName, value, validValues) {
  if (!value || !validValues.includes(value)) {
    return { isValid: false, message: `${fieldName.replace('_', ' ')} invalide` };
  }
  return { isValid: true, message: "" };
}

// Validation complète d'un objet utilisateur (pour création ou modification)
function validateUserData(data, isCreation = false) {
  const errors = {};
  let val = validateTextField('nom', data.nom); if (!val.isValid) errors.nom = val.message;
  val = validateTextField('prenom', data.prenom); if (!val.isValid) errors.prenom = val.message;
  val = validateTextField('email', data.email); if (!val.isValid) errors.email = val.message;
  if (isCreation || (data.password && data.password.trim() !== "")) {
    val = validateTextField('password', data.password); if (!val.isValid) errors.password = val.message;
  }
  val = validateNumberField('taille', data.taille); if (!val.isValid) errors.taille = val.message;
  val = validateNumberField('poids', data.poids); if (!val.isValid) errors.poids = val.message;
  val = validateChoiceField('objectif', data.objectif, OBJECTIFS); if (!val.isValid) errors.objectif = val.message;
  val = validateChoiceField('niveau_sport', data.niveau_sport, NIVEAUX); if (!val.isValid) errors.niveau_sport = val.message;
  return { isValid: Object.keys(errors).length === 0, errors };
}

// ── AFFICHAGE DES ERREURS DANS UN CONTENEUR SPÉCIFIQUE ───────────────────────
function showValidationFieldError(field, message) {
  if (!field) return;
  field.classList.add('error');
  field.style.borderColor = '#DC2626';
  let errorDiv = field.parentNode.querySelector('.error-message');
  if (!errorDiv) {
    errorDiv = document.createElement('div');
    errorDiv.classList.add('error-message');
    field.parentNode.insertBefore(errorDiv, field.nextSibling);
  }
  errorDiv.textContent = message;
  errorDiv.style.color = '#DC2626';
  errorDiv.style.fontSize = '12px';
  errorDiv.style.marginTop = '4px';
  errorDiv.style.display = 'block';
}

function clearFieldError(field) {
  if (!field) return;
  field.classList.remove('error');
  field.style.borderColor = '';
  const errorDiv = field.parentNode?.querySelector('.error-message');
  if (errorDiv) errorDiv.remove();
}

function displayFormErrors(errors, container) {
  // Nettoyer uniquement les erreurs à l'intérieur du conteneur
  container.querySelectorAll('.error-message').forEach(el => el.remove());
  container.querySelectorAll('input.error, select.error').forEach(el => el.classList.remove('error'));

  Object.entries(errors).forEach(([fieldName, message]) => {
    // Chercher le champ dans le conteneur : soit id="add-xxx", soit data-field="xxx"
    let field = container.querySelector(`#add-${fieldName}`) || container.querySelector(`[data-field="${fieldName}"]`);
    if (field) showValidationFieldError(field, message);
  });
}

// ── FONCTIONS UTILITAIRES ────────────────────────────────────────────────────
function escapeHtml(value) {
  return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function inputClass(width = 'w-full') {
  return `${width} rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-green-600 focus:ring-green-200`;
}

function selectOptions(options, selectedValue) {
  return options.map(opt => `<option value="${escapeHtml(opt)}" ${opt === selectedValue ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('');
}

function formatDate(dateValue) {
  if (!dateValue) return '';
  const date = new Date(dateValue);
  return isNaN(date.getTime()) ? escapeHtml(dateValue) : date.toLocaleString('fr-FR');
}

function getBadgeColor(cssClass) {
  const colors = {
    'badge-active': { bg: 'bg-green-100', text: 'text-green-700' },
    'badge-moderate': { bg: 'bg-yellow-100', text: 'text-yellow-700' },
    'badge-inactive': { bg: 'bg-slate-100', text: 'text-slate-700' }
  };
  return colors[cssClass] || colors['badge-inactive'];
}

function renderBadge(badge, score) {
  const colors = getBadgeColor(badge.css_class);
  return `<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg ${colors.bg} ${colors.text}" title="${escapeHtml(badge.description)}">
    <span class="text-lg">${escapeHtml(badge.emoji)}</span>
    <span class="text-xs font-semibold">${escapeHtml(badge.label)}</span>
    <span class="text-xs font-medium opacity-70">${score}/100</span>
  </div>`;
}

function showToast(message, type = 'success') {
  const t = document.getElementById('toast');
  if (t) {
    t.textContent = message;
    t.className = 'toast show ' + (type === 'error' ? 'toast-error' : '');
    setTimeout(() => { t.classList.remove('show'); t.className = 'toast'; }, 3000);
  } else {
    alert(message);
  }
}

// ── CHARGEMENT ET AFFICHAGE ──────────────────────────────────────────────────
async function loadUsers() {
  try {
    console.log('🔄 Chargement des utilisateurs...');
    const response = await fetch(API_URL + '?action=get-all', { credentials: 'same-origin' });
    console.log('📡 Réponse reçue:', response.status, response.statusText);

    const result = await response.json();
    console.log('📋 Données JSON:', result);

    if (result.success && Array.isArray(result.users)) {
      console.log('✅', result.users.length, 'utilisateurs chargés');
      // Vérifier les badges
      result.users.forEach((user, index) => {
        if (index < 3) { // Montrer seulement les 3 premiers
          console.log(`👤 User ${user.id_user}: score=${user.score}, badge=`, user.badge);
        }
      });

      allUsers = result.users;
      renderUsersTable();
      updateUserStats();
    } else if (result.code === 401 || result.code === 403) {
      console.log('🚫 Redirection vers login');
      window.location.href = '../frontoffice/login.php';
    } else {
      console.error('❌ Erreur get-all:', result.message);
      showToast('Erreur: ' + (result.message || 'Réponse invalide'), 'error');
    }
  } catch (error) {
    console.error('💥 Erreur réseau loadUsers:', error);
    showToast('Erreur réseau', 'error');
  }
}

function updateUserStats() {
  const totalUsers = allUsers.length;
  const totalUsersEl = document.getElementById('stat-total-users');
  const activeUsersEl = document.getElementById('stat-active-users');
  if (totalUsersEl) totalUsersEl.textContent = totalUsers;
  if (activeUsersEl) activeUsersEl.textContent = totalUsers;
}

function renderUsersTable() {
  const tbody = document.getElementById('users-table-body');
  if (!tbody) return;
  if (!Array.isArray(allUsers)) { allUsers = []; }

  // Ligne d'ajout (conteneur spécifique)
  const addRow = `
    <tr id="add-user-row" class="bg-green-50/60">
      <td class="py-4 px-4 text-sm font-semibold text-green-700">Nouveau</td>
      <td class="py-4 px-4"><input id="add-nom" class="${inputClass('min-w-[110px]')}" type="text" placeholder="Nom"></td>
      <td class="py-4 px-4"><input id="add-prenom" class="${inputClass('min-w-[110px]')}" type="text" placeholder="Prenom"></td>
      <td class="py-4 px-4"><input id="add-email" class="${inputClass('min-w-[180px]')}" type="email" placeholder="Email"></td>
      <td class="py-4 px-4"><input id="add-password" class="${inputClass('min-w-[140px]')}" type="password" placeholder="Mot de passe"></td>
      <td class="py-4 px-4"><input id="add-taille" class="${inputClass('w-24')}" type="number" placeholder="cm"></td>
      <td class="py-4 px-4"><input id="add-poids" class="${inputClass('w-24')}" type="number" step="0.1" placeholder="kg"></td>
      <td class="py-4 px-4"><select id="add-objectif" class="${inputClass('min-w-[180px]')}">${selectOptions(OBJECTIFS, 'Perdre du poids')}</select></td>
      <td class="py-4 px-4"><select id="add-niveau" class="${inputClass('min-w-[150px]')}">${selectOptions(NIVEAUX, 'Débutant')}</select></td>
      <td class="py-4 px-4 text-xs text-slate-400">Automatique</td>
      <td class="py-4 px-4 text-xs text-slate-400">Nouveau compte</td>
      <td class="py-4 px-4 text-right">
        <button class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-green-600 text-white hover:bg-green-700" title="Ajouter" type="button" onclick="createUserInline()">
          <span class="material-symbols-outlined text-lg">add</span>
        </button>
      </td>
    </tr>
  `;

  const rows = allUsers.map(user => `
    <tr class="user-row group hover:bg-surface-container-low/30 transition-colors" data-user-id="${user.id_user}">
      <td class="py-4 px-4"><div class="flex flex-col"><span class="font-bold">#${escapeHtml(user.id_user)}</span></div></td>
      <td class="py-4 px-4"><input class="${inputClass('min-w-[110px]')}" data-field="nom" type="text" value="${escapeHtml(user.nom)}"></td>
      <td class="py-4 px-4"><input class="${inputClass('min-w-[110px]')}" data-field="prenom" type="text" value="${escapeHtml(user.prenom)}"></td>
      <td class="py-4 px-4"><input class="${inputClass('min-w-[180px]')}" data-field="email" type="email" value="${escapeHtml(user.email)}"></td>
      <td class="py-4 px-4"><input class="${inputClass('min-w-[140px]')}" data-field="password" type="password" value="${escapeHtml(user.password)}"></td>
      <td class="py-4 px-4"><input class="${inputClass('w-24')}" data-field="taille" type="number" value="${escapeHtml(user.taille)}"></td>
      <td class="py-4 px-4"><input class="${inputClass('w-24')}" data-field="poids" type="number" step="0.1" value="${escapeHtml(user.poids)}"></td>
      <td class="py-4 px-4"><select class="${inputClass('min-w-[180px]')}" data-field="objectif">${selectOptions(OBJECTIFS, user.objectif)}</select></td>
      <td class="py-4 px-4"><select class="${inputClass('min-w-[150px]')}" data-field="niveau_sport">${selectOptions(NIVEAUX, user.niveau_sport)}</select></td>
      <td class="py-4 px-4 text-sm text-slate-500">${formatDate(user.date_inscription)}</td>
      <td class="py-4 px-4">${user.badge ? renderBadge(user.badge, user.score) : '<span class="text-xs text-slate-400">-</span>'}</td>
      <td class="py-4 px-4"><div class="flex justify-end gap-2">
        <button class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700" title="Modifier" type="button" onclick="saveUser(${user.id_user})">
          <span class="material-symbols-outlined text-lg">edit</span>
        </button>
        <button class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white hover:bg-red-700" title="Supprimer" type="button" onclick="deleteUser(${user.id_user})">
          <span class="material-symbols-outlined text-lg">delete</span>
        </button>
      </div></td>
    </tr>
  `).join('');

  tbody.innerHTML = addRow + rows;
}

// ── CRÉATION D'UN UTILISATEUR (AJOUT) ───────────────────────────────────────
async function createUserInline() {
  const addRow = document.getElementById('add-user-row');
  const data = {
    nom: document.getElementById('add-nom').value.trim(),
    prenom: document.getElementById('add-prenom').value.trim(),
    email: document.getElementById('add-email').value.trim(),
    password: document.getElementById('add-password').value,
    taille: document.getElementById('add-taille').value,
    poids: document.getElementById('add-poids').value,
    objectif: document.getElementById('add-objectif').value,
    niveau_sport: document.getElementById('add-niveau').value
  };

  const validation = validateUserData(data, true);
  if (!validation.isValid) {
    displayFormErrors(validation.errors, addRow);
    return;
  }

  try {
    const formData = new FormData();
    Object.entries(data).forEach(([k, v]) => formData.append(k, v));
    const response = await fetch(API_URL + '?action=create', { method: 'POST', body: formData, credentials: 'same-origin' });
    const result = await response.json();
    if (result.success) {
      showToast('Utilisateur ajouté avec succès', 'success');
      loadUsers();
      // Réinitialiser les champs
      document.getElementById('add-nom').value = '';
      document.getElementById('add-prenom').value = '';
      document.getElementById('add-email').value = '';
      document.getElementById('add-password').value = '';
      document.getElementById('add-taille').value = '';
      document.getElementById('add-poids').value = '';
      document.getElementById('add-objectif').value = 'Perdre du poids';
      document.getElementById('add-niveau').value = 'Débutant';
      // Nettoyer les éventuelles erreurs
      displayFormErrors({}, addRow);
    } else {
      if (result.errors) displayFormErrors(result.errors, addRow);
      else showToast(result.message || 'Erreur lors de l\'ajout', 'error');
    }
  } catch (error) { showToast('Erreur réseau', 'error'); }
}

// ── MODIFICATION D'UN UTILISATEUR (SAUVEGARDE) ──────────────────────────────
async function saveUser(userId) {
  const row = document.querySelector(`tr[data-user-id="${userId}"]`);
  if (!row) return;

  const data = {
    id_user: userId,
    nom: row.querySelector('input[data-field="nom"]').value.trim(),
    prenom: row.querySelector('input[data-field="prenom"]').value.trim(),
    email: row.querySelector('input[data-field="email"]').value.trim(),
    password: row.querySelector('input[data-field="password"]').value,
    taille: row.querySelector('input[data-field="taille"]').value,
    poids: row.querySelector('input[data-field="poids"]').value,
    objectif: row.querySelector('select[data-field="objectif"]').value,
    niveau_sport: row.querySelector('select[data-field="niveau_sport"]').value
  };

  // Validation des données (pour modification, password optionnel)
  const validation = validateUserData(data, false);
  if (!validation.isValid) {
    displayFormErrors(validation.errors, row);  // Afficher les erreurs dans la ligne concernée
    return;
  }

  try {
    const formData = new FormData();
    Object.entries(data).forEach(([k, v]) => formData.append(k, v));
    const response = await fetch(API_URL + '?action=update', { method: 'POST', body: formData, credentials: 'same-origin' });
    const result = await response.json();
    if (result.success) {
      showToast('Utilisateur modifié avec succès', 'success');
      loadUsers();
    } else {
      if (result.errors) displayFormErrors(result.errors, row);
      else showToast(result.message || 'Erreur lors de la modification', 'error');
    }
  } catch (error) { showToast('Erreur réseau', 'error'); }
}

// ── SUPPRESSION ──────────────────────────────────────────────────────────────
async function deleteUser(userId) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return;
  try {
    const formData = new FormData();
    formData.append('id_user', userId);
    const response = await fetch(API_URL + '?action=delete', { method: 'POST', body: formData, credentials: 'same-origin' });
    const result = await response.json();
    if (result.success) {
      showToast('Utilisateur supprimé avec succès', 'success');
      loadUsers();
    } else {
      showToast(result.message || 'Erreur lors de la suppression', 'error');
    }
  } catch (error) { showToast('Erreur réseau', 'error'); }
}

// ── RECHERCHE ────────────────────────────────────────────────────────────────
async function searchUsers() {
  const term = document.getElementById('search-input').value.trim();
  if (!term) { loadUsers(); return; }
  try {
    const formData = new FormData();
    formData.append('terme', term);
    const response = await fetch(API_URL + '?action=search', { method: 'POST', body: formData, credentials: 'same-origin' });
    const result = await response.json();
    if (!result.success) throw new Error(result.message || 'Erreur de recherche');
    allUsers = Array.isArray(result.users) ? result.users : [];
    renderUsersTable();
    updateUserStats();
  } catch (error) { alert(error.message); }
}

// ── TRI ALPHABÉTIQUE ─────────────────────────────────────────────────────────
function sortUsersByName() {
  if (!allUsers || allUsers.length === 0) {
    showToast('Aucun utilisateur à trier', 'error');
    return;
  }
  allUsers.sort((a, b) => {
    const nomA = (a.nom || '').toLowerCase();
    const nomB = (b.nom || '').toLowerCase();
    return nomA.localeCompare(nomB, 'fr');
  });
  renderUsersTable();
  showToast('Utilisateurs triés alphabétiquement', 'success');
}

// ── EXPORT CSV ───────────────────────────────────────────────────────────────
function exportUsers() {
  if (!allUsers.length) { alert('Aucune donnée à exporter'); return; }
  const headers = ['ID', 'Nom', 'Prenom', 'Email', 'Taille', 'Poids', 'Objectif', 'Niveau sportif', 'Date inscription'];
  const rows = allUsers.map(user => [user.id_user, user.nom, user.prenom, user.email, user.taille, user.poids, user.objectif, user.niveau_sport, user.date_inscription]);
  const csv = [headers, ...rows].map(row => row.map(cell => `"${String(cell ?? '').replace(/"/g, '""')}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'users.csv';
  link.click();
}

// ── INITIALISATION ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadUsers();
  document.getElementById('add-user-btn')?.addEventListener('click', () => document.getElementById('add-nom')?.focus());
  document.getElementById('reload-users-btn')?.addEventListener('click', sortUsersByName);
  document.getElementById('export-users-btn')?.addEventListener('click', exportUsers);
  document.getElementById('search-input')?.addEventListener('input', searchUsers);
});

// ════════════════════════════════════════════════
// ADMIN : NAVIGATION SECTIONS
// ════════════════════════════════════════════════
function showAdminSection(name) {
  ['users', 'forum', 'shop', 'nutrition'].forEach(s => {
    const el = document.getElementById('admin-section-' + s);
    if (el) el.style.display = s === name ? 'block' : 'none';
  });
  if (name === 'forum') loadForumPosts();
  if (name === 'shop') loadShopAdmin();
  if (name === 'nutrition') loadNutritionAdmin();
}

// ════════════════════════════════════════════════
// ADMIN FORUM
// ════════════════════════════════════════════════
const FORUM_ADMIN_URL = '../../controleur/backoffice/AdminForumControleur.php';

function adminFetch(action, options = {}) {
  const url = FORUM_ADMIN_URL + '?action=' + action;
  return fetch(url, { credentials: 'same-origin', ...options }).then(r => r.json());
}

function escHtml(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function loadForumPosts() {
  // Stats
  try {
    const stats = await adminFetch('get_stats');
    if (stats.success) {
      const ep = document.getElementById('forum-stat-posts');
      const em = document.getElementById('forum-stat-members');
      const ew = document.getElementById('forum-stat-week');
      if (ep) ep.textContent = stats.total_posts;
      if (em) em.textContent = stats.community_members ?? 0;
      if (ew) ew.textContent = stats.posts_this_week ?? 0;
    }
  } catch (e) {}

  // Liste des posts (avec commentaires)
  const tbody = document.getElementById('forum-posts-tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="7" class="py-6 px-4 text-sm text-slate-500">Chargement…</td></tr>';

  try {
    const result = await adminFetch('list_posts_with_comments');
    if (!result.success) {
      tbody.innerHTML = `<tr><td colspan="7" class="py-6 px-4 text-sm text-red-500">${escHtml(result.error || 'Erreur')}</td></tr>`;
      return;
    }
    if (!result.posts.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="py-6 px-4 text-sm text-slate-400">Aucun post</td></tr>';
      return;
    }
    let html = '';
    result.posts.forEach(post => {
      const date = post.created_at ? new Date(post.created_at).toLocaleString('en-US', { year:'numeric', month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false }) : '—';
      const titleSafe   = (post.titre_post   || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
      const contentSafe = (post.contenu_post || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
      const preview     = escHtml((post.contenu_post || '').substring(0, 50)) + (post.contenu_post && post.contenu_post.length > 50 ? '…' : '');
      const commentCount = (post.comments ?? []).length;

      html += `
        <tr class="border-b border-surface-variant hover:bg-surface-container transition-colors">
          <td class="px-4 py-3">
            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-semibold">${post.id}</span>
          </td>
          <td class="px-4 py-3"><p class="font-semibold text-on-surface max-w-xs truncate">${escHtml(post.titre_post)}</p></td>
          <td class="px-4 py-3"><p class="text-on-surface-variant">${escHtml(post.nom_auteur)}</p></td>
          <td class="px-4 py-3"><p class="text-on-surface-variant text-sm max-w-sm truncate">${preview}</p></td>
          <td class="px-4 py-3">
            <button onclick="toggleAdminComments(${post.id})" class="inline-flex items-center gap-2 px-3 py-1 bg-secondary/10 text-secondary rounded-full text-xs font-semibold hover:bg-secondary/20 transition-colors">
              <span class="material-symbols-outlined text-sm">chat</span>
              <span>${commentCount}</span>
            </button>
          </td>
          <td class="px-4 py-3"><p class="text-on-surface-variant text-sm">${date}</p></td>
          <td class="px-4 py-3">
            <div class="flex gap-2">
              <button onclick="openAdminEditPost(${post.id},'${titleSafe}','${contentSafe}')" class="p-2 hover:bg-blue-100 rounded-lg transition-colors" title="Edit">
                <span class="material-symbols-outlined text-blue-600">edit</span>
              </button>
              <button onclick="adminDeletePost(${post.id})" class="p-2 hover:bg-red-100 rounded-lg transition-colors" title="Delete">
                <span class="material-symbols-outlined text-red-600">delete</span>
              </button>
            </div>
          </td>
        </tr>`;

      // Expandable comments row
      html += `<tr id="admin-comments-row-${post.id}" class="hidden bg-surface-container border-b border-surface-variant">
        <td colspan="7" class="px-8 py-6">
          <div class="space-y-4">
            <div class="flex items-center gap-3 mb-4">
              <span class="material-symbols-outlined text-secondary text-lg">chat_bubble</span>
              <h4 class="font-semibold text-on-surface">Comments for: ${escHtml(post.titre_post)}</h4>
              <span class="text-xs text-on-surface-variant">(${commentCount} total)</span>
            </div>`;

      if (commentCount > 0) {
        html += '<div class="space-y-3">';
        post.comments.forEach(c => {
          const cDate = c.date_commentaire ? new Date(c.date_commentaire).toLocaleString('en-US', { year:'numeric', month:'short', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false }) : '';
          const cSafe = (c.contenu || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/\n/g,'\\n');
          const aSafe = (c.nom_auteur || '').replace(/'/g,"\\'");
          html += `
            <div class="bg-surface-container-lowest p-4 rounded-lg border border-surface-variant" id="admin-comment-${c.id_commentaire}">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <p class="font-semibold text-on-surface text-sm">${escHtml(c.nom_auteur)}</p>
                  <p class="text-xs text-on-surface-variant">${cDate}</p>
                </div>
                <div class="flex items-center gap-2">
                  <span class="inline-block px-2 py-1 bg-secondary/10 text-secondary rounded text-xs font-semibold">ID: ${c.id_commentaire}</span>
                  <button onclick="openAdminEditComment(${c.id_commentaire},${post.id},'${cSafe}','${aSafe}')" class="p-2 text-primary hover:bg-primary/10 rounded transition-colors" title="Edit comment">
                    <span class="material-symbols-outlined text-sm">edit</span>
                  </button>
                  <button onclick="adminDeleteComment(${c.id_commentaire},${post.id})" class="p-2 text-error hover:bg-error/10 rounded transition-colors" title="Delete comment">
                    <span class="material-symbols-outlined text-sm">delete</span>
                  </button>
                </div>
              </div>
              <p class="text-on-surface-variant text-sm break-words">${escHtml(c.contenu)}</p>
            </div>`;
        });
        html += '</div>';
      } else {
        html += `<div class="bg-surface-container-low rounded-lg p-6 text-center">
          <span class="material-symbols-outlined text-on-surface-variant text-2xl mb-2 block">chat_bubble_outline</span>
          <p class="text-on-surface-variant text-sm">No comments yet</p>
        </div>`;
      }

      html += '</div></td></tr>';
    });
    tbody.innerHTML = html;
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="7" class="py-6 px-4 text-sm text-red-500">Erreur réseau</td></tr>';
  }
}

function toggleAdminComments(postId) {
  const row = document.getElementById('admin-comments-row-' + postId);
  if (row) row.classList.toggle('hidden');
}

function openAdminEditPost(id, titre, contenu) {
  document.getElementById('admin-edit-post-id').value     = id;
  document.getElementById('admin-edit-post-title').value  = titre;
  document.getElementById('admin-edit-post-content').value = contenu;
  document.getElementById('admin-edit-post-modal').style.display = 'flex';
}

function closeAdminEditPost() {
  document.getElementById('admin-edit-post-modal').style.display = 'none';
}

async function saveAdminEditPost() {
  const id      = document.getElementById('admin-edit-post-id').value;
  const titre   = document.getElementById('admin-edit-post-title').value.trim();
  const contenu = document.getElementById('admin-edit-post-content').value.trim();

  if (!titre || !contenu) { showToast('Titre et contenu requis', 'error'); return; }

  try {
    const result = await adminFetch('update_post', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: parseInt(id), titre_post: titre, contenu_post: contenu }),
    });
    if (result.success) {
      showToast('Post mis à jour', 'success');
      closeAdminEditPost();
      loadForumPosts();
    } else {
      showToast(result.error || 'Erreur', 'error');
    }
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function adminDeletePost(postId) {
  if (!confirm('Supprimer ce post ? Cette action est irréversible.')) return;
  try {
    const result = await adminFetch('delete_post', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId }),
    });
    if (result.success) {
      showToast('Post supprimé', 'success');
      loadForumPosts();
    } else {
      showToast(result.error || 'Erreur', 'error');
    }
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

// ── ADMIN : EDIT COMMENT ────────────────────────────────────────────────────
function openAdminEditComment(commentId, postId, contenu, auteur) {
  document.getElementById('admin-edit-comment-id').value      = commentId;
  document.getElementById('admin-edit-comment-post-id').value = postId;
  document.getElementById('admin-edit-comment-author').value  = auteur;
  document.getElementById('admin-edit-comment-content').value = contenu;
  document.getElementById('admin-edit-comment-modal').style.display = 'flex';
}

function closeAdminEditComment() {
  document.getElementById('admin-edit-comment-modal').style.display = 'none';
}

async function saveAdminEditComment() {
  const commentId = document.getElementById('admin-edit-comment-id').value;
  const contenu   = document.getElementById('admin-edit-comment-content').value.trim();

  if (!contenu) { showToast('Contenu requis', 'error'); return; }

  try {
    const result = await adminFetch('update_comment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: parseInt(commentId), contenu: contenu }),
    });
    if (result.success) {
      showToast('Commentaire mis à jour', 'success');
      closeAdminEditComment();
      loadForumPosts();
    } else {
      showToast(result.error || 'Erreur', 'error');
    }
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function adminDeleteComment(commentId, postId) {
  if (!confirm('Supprimer ce commentaire ?')) return;
  try {
    const result = await adminFetch('delete_comment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: commentId }),
    });
    if (result.success) {
      showToast('Commentaire supprimé', 'success');
      loadForumPosts();
    } else {
      showToast(result.error || 'Erreur', 'error');
    }
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

// ════════════════════════════════════════════════
// ADMIN : SHOP / BOUTIQUE
// ════════════════════════════════════════════════
const SHOP_ADMIN_URL = '../../controleur/backoffice/AdminBoutiqueControleur.php';

// ── state ────────────────────────────────────────────────────
let _shopPopularChart = null;
let _shopCurrentOrderId = null;
let _shopSearchTimeout = null;
let _shopSearchReqId = 0;
let _shopSearchResults = [];

function shopFetch(action, options = {}) {
  return fetch(SHOP_ADMIN_URL + '?action=' + action, { credentials: 'same-origin', ...options }).then(r => r.json());
}

async function loadShopAdmin() {
  try {
    const data = await shopFetch('get_stats');
    if (!data.success) { console.warn('Shop admin: get_stats failed', data); return; }

    // ── Stats cards
    const rev = document.getElementById('shop-stat-revenue');
    const ord = document.getElementById('shop-stat-orders');
    const prd = document.getElementById('shop-stat-products');
    const itm = document.getElementById('shop-stat-items');
    if (rev) rev.textContent = Number(data.total_profit || 0).toFixed(2).replace('.', ',') + '€';
    if (ord) ord.textContent = data.total_orders || 0;
    if (prd) prd.textContent = data.total_products || 0;
    // count total items sold from popular_items
    const totalUnits = (data.popular_items || []).reduce((s, i) => s + Number(i.total_quantity || 0), 0);
    if (itm) itm.textContent = totalUnits;

    // ── Popular items chart
    _renderShopPopularChart(data.popular_items || []);

    // ── Products table
    const ptbody = document.getElementById('shop-products-tbody');
    if (ptbody && Array.isArray(data.products)) {
      if (data.products.length === 0) {
        ptbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-500">Aucun produit.</td></tr>';
      } else {
        ptbody.innerHTML = data.products.map(p => `<tr class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50">
          <td class="py-3 px-2 text-sm text-slate-500">${p.id}</td>
          <td class="py-3 px-2 font-bold text-slate-800 dark:text-white">${escHtml(p.nom)}</td>
          <td class="py-3 px-2"><span class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-xs font-bold rounded-full">${escHtml(p.categorie)}</span></td>
          <td class="py-3 px-2 text-right font-bold">${Number(p.prix).toFixed(2)}€</td>
          <td class="py-3 px-2 text-right">
            <button onclick="openAdminEditProduct(${p.id},'${escHtml(p.nom).replace(/'/g,"\\'")}',${p.prix},'${escHtml(p.description||'').replace(/'/g,"\\'")}','${p.categorie}')" class="text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 p-1 rounded"><span class="material-symbols-outlined text-sm">edit</span></button>
            <button onclick="adminDeleteProduct(${p.id})" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 p-1 rounded ml-1"><span class="material-symbols-outlined text-sm">delete</span></button>
          </td>
        </tr>`).join('');
      }
    }

    // ── Orders table
    _renderShopOrdersTable(data.recent_orders || []);

  } catch (e) { console.error('Shop admin load error', e); }
}

// ── Render orders table rows ─────────────────────────────────
function _renderShopOrdersTable(orders) {
  const otbody = document.getElementById('shop-orders-tbody');
  if (!otbody) return;
  if (orders.length === 0) {
    otbody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-slate-500">Aucune commande.</td></tr>';
    return;
  }
  otbody.innerHTML = orders.map(o => `<tr class="border-b border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50">
    <td class="py-3 px-2 text-sm text-slate-500">${o.id}</td>
    <td class="py-3 px-2 font-bold text-slate-800 dark:text-white">${escHtml(o.customer_name)}</td>
    <td class="py-3 px-2 text-sm">${escHtml(o.telephone)}</td>
    <td class="py-3 px-2 text-right font-bold">${Number(o.total_price).toFixed(2)}€</td>
    <td class="py-3 px-2 text-right text-sm text-slate-500">${(o.created_at||'').substring(0,10)}</td>
    <td class="py-3 px-2 text-right flex gap-1 justify-end">
      <button onclick="showShopOrderDetails(${o.id})" class="rounded-full border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-3 py-1 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-600 transition">Détails</button>
      <button onclick="adminDeleteOrder(${o.id})" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 p-1 rounded"><span class="material-symbols-outlined text-sm">delete</span></button>
    </td>
  </tr>`).join('');
}

// ── Popular items chart ──────────────────────────────────────
function _renderShopPopularChart(items) {
  const canvas = document.getElementById('shop-popular-chart');
  const empty = document.getElementById('shop-popular-empty');
  const unitsEl = document.getElementById('shop-popular-units');
  const revEl = document.getElementById('shop-popular-revenue');

  if (!canvas) return;

  if (!items || items.length === 0) {
    if (empty) empty.classList.remove('hidden');
    if (_shopPopularChart) { _shopPopularChart.destroy(); _shopPopularChart = null; }
    return;
  }
  if (empty) empty.classList.add('hidden');

  const totalUnits = items.reduce((s, i) => s + Number(i.total_quantity || 0), 0);
  const totalRev = items.reduce((s, i) => s + Number(i.total_revenue || 0), 0);
  if (unitsEl) unitsEl.textContent = totalUnits.toLocaleString();
  if (revEl) revEl.textContent = totalRev.toFixed(2).replace('.', ',') + '€';

  if (_shopPopularChart) { _shopPopularChart.destroy(); }

  _shopPopularChart = new Chart(canvas.getContext('2d'), {
    type: 'bar',
    data: {
      labels: items.map(i => i.product_name),
      datasets: [
        {
          label: 'Unités vendues',
          data: items.map(i => i.total_quantity),
          backgroundColor: '#006e1c',
          borderColor: '#004d12',
          borderWidth: 1,
          yAxisID: 'y'
        },
        {
          label: 'Acheteurs uniques',
          data: items.map(i => i.unique_customers),
          backgroundColor: '#0060a8',
          borderColor: '#003d6b',
          borderWidth: 1,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', labels: { font: { size: 12, weight: '600' }, padding: 15 } },
        tooltip: { backgroundColor: 'rgba(0,0,0,.8)', padding: 12 }
      },
      scales: {
        y: { type: 'linear', position: 'left', title: { display: true, text: 'Unités', font: { weight: 'bold' } }, grid: { color: 'rgba(0,0,0,.05)' } },
        y1: { type: 'linear', position: 'right', title: { display: true, text: 'Acheteurs', font: { weight: 'bold' } }, grid: { drawOnChartArea: false } }
      }
    }
  });
}

// ── Order details modal ──────────────────────────────────────
async function showShopOrderDetails(orderId) {
  _shopCurrentOrderId = orderId;
  const modal = document.getElementById('shop-order-details-modal');
  const itemsEl = document.getElementById('shop-od-items');
  const customerEl = document.getElementById('shop-od-customer');
  if (!modal || !itemsEl) return;

  modal.classList.remove('hidden');
  if (customerEl) customerEl.textContent = '';
  itemsEl.innerHTML = '<p class="text-slate-500 text-sm">Chargement…</p>';

  try {
    const data = await shopFetch('get_order_details&id=' + orderId);
    if (!data.success || !data.order) {
      itemsEl.innerHTML = '<p class="text-red-500 text-sm">Impossible de charger la commande.</p>';
      return;
    }
    const o = data.order;
    if (customerEl) customerEl.textContent = escHtml(o.customer_name || '');
    document.getElementById('shop-od-total').textContent = Number(o.total_price || 0).toFixed(2).replace('.', ',') + '€';
    document.getElementById('shop-od-phone').textContent = escHtml(o.telephone || '-');
    document.getElementById('shop-od-address').textContent = escHtml(o.address || '-');

    if (o.items && o.items.length > 0) {
      itemsEl.innerHTML = o.items.map(it => `
        <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-3">
          <div>
            <p class="font-semibold text-slate-800 dark:text-white text-sm">${escHtml(it.product_name)}</p>
            <p class="text-xs text-slate-500 mt-0.5">${it.quantity} × ${Number(it.unit_price).toFixed(2)}€</p>
          </div>
          <p class="font-bold text-slate-800 dark:text-white">${Number(it.total_price).toFixed(2)}€</p>
        </div>`).join('');
    } else {
      itemsEl.innerHTML = '<p class="text-slate-500 text-sm">Aucun article enregistré.</p>';
    }
  } catch (e) {
    itemsEl.innerHTML = '<p class="text-red-500 text-sm">Erreur réseau.</p>';
  }
}

function closeShopOrderDetails() {
  document.getElementById('shop-order-details-modal')?.classList.add('hidden');
  _shopCurrentOrderId = null;
}

async function shopConfirmDeleteOrder() {
  if (!_shopCurrentOrderId) return;
  if (!confirm('Supprimer cette commande et tous ses articles ?')) return;
  const fd = new FormData();
  fd.append('id', _shopCurrentOrderId);
  try {
    const res = await shopFetch('delete_order', { method: 'POST', body: fd });
    if (res.success) {
      showToast('Commande supprimée', 'success');
      closeShopOrderDetails();
      loadShopAdmin();
    } else {
      showToast(res.error || 'Erreur', 'error');
    }
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

// ── Dynamic order search ─────────────────────────────────────
function handleShopOrderSearch(value) {
  clearTimeout(_shopSearchTimeout);
  const term = value.trim();
  const msg = document.getElementById('shop-search-msg');

  if (term === '') {
    if (msg) msg.classList.add('hidden');
    _shopSearchResults = [];
    loadShopAdmin();
    return;
  }

  _shopSearchTimeout = setTimeout(async () => {
    const reqId = ++_shopSearchReqId;
    const fd = new FormData();
    fd.append('term', term);
    try {
      const data = await shopFetch('search_orders', { method: 'POST', body: fd });
      if (reqId !== _shopSearchReqId) return;
      if (!data.success) { if (msg) { msg.textContent = 'Erreur de recherche.'; msg.classList.remove('hidden'); } return; }
      _shopSearchResults = data.orders || [];
      if (msg) {
        msg.classList.remove('hidden', 'text-red-500', 'text-slate-500');
        if (_shopSearchResults.length > 0) {
          msg.textContent = _shopSearchResults.length + ' commande(s) trouvée(s). Appuyez sur Entrée pour ouvrir la première.';
          msg.classList.add('text-slate-500');
        } else {
          msg.textContent = 'Aucun client trouvé.';
          msg.classList.add('text-red-500');
        }
      }
      _renderShopOrdersTable(_shopSearchResults);
    } catch (e) { console.error(e); }
  }, 300);
}

function handleShopOrderSearchKeyDown(event) {
  if (event.key !== 'Enter') return;
  event.preventDefault();
  if (_shopSearchResults.length > 0) showShopOrderDetails(_shopSearchResults[0].id);
}

function openAdminAddProduct() {
  document.getElementById('admin-product-id').value = '';
  document.getElementById('admin-product-nom').value = '';
  document.getElementById('admin-product-prix').value = '';
  document.getElementById('admin-product-desc').value = '';
  document.getElementById('admin-product-cat').value = 'complement';
  document.getElementById('admin-product-modal-title').textContent = 'Ajouter un produit';
  document.getElementById('admin-product-modal').style.display = 'flex';
}

function openAdminEditProduct(id, nom, prix, desc, cat) {
  document.getElementById('admin-product-id').value = id;
  document.getElementById('admin-product-nom').value = nom;
  document.getElementById('admin-product-prix').value = prix;
  document.getElementById('admin-product-desc').value = desc;
  document.getElementById('admin-product-cat').value = cat;
  document.getElementById('admin-product-modal-title').textContent = 'Modifier le produit';
  document.getElementById('admin-product-modal').style.display = 'flex';
}

function closeAdminProductModal() {
  document.getElementById('admin-product-modal').style.display = 'none';
}

async function saveAdminProduct() {
  const id = document.getElementById('admin-product-id').value;
  const fd = new FormData();
  if (id) fd.append('id', id);
  fd.append('nom', document.getElementById('admin-product-nom').value);
  fd.append('prix', document.getElementById('admin-product-prix').value);
  fd.append('description', document.getElementById('admin-product-desc').value);
  fd.append('categorie', document.getElementById('admin-product-cat').value);

  const action = id ? 'update_product' : 'add_product';
  try {
    const res = await shopFetch(action, { method: 'POST', body: fd });
    if (res.success) {
      showToast(id ? 'Produit mis à jour' : 'Produit ajouté', 'success');
      closeAdminProductModal();
      loadShopAdmin();
    } else {
      showToast(res.error || 'Erreur', 'error');
    }
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function adminDeleteProduct(id) {
  if (!confirm('Supprimer ce produit ?')) return;
  const fd = new FormData();
  fd.append('id', id);
  try {
    const res = await shopFetch('delete_product', { method: 'POST', body: fd });
    if (res.success) { showToast('Produit supprimé', 'success'); loadShopAdmin(); }
    else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function adminDeleteOrder(id) {
  if (!confirm('Supprimer cette commande ?')) return;
  const fd = new FormData();
  fd.append('id', id);
  try {
    const res = await shopFetch('delete_order', { method: 'POST', body: fd });
    if (res.success) { showToast('Commande supprimée', 'success'); loadShopAdmin(); }
    else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

// ════════════════════════════════════════════════
// ADMIN NUTRITION
// ════════════════════════════════════════════════
const NUTRITION_ADMIN_URL = '../../controleur/backoffice/AdminNutritionControleur.php';
let _nutAllMeals = [], _nutAllIngredients = [], _nutMealSort = {col:'nom',asc:true}, _nutMealPage = 1, _nutIngPage = 1;

function nutFetch(action, opts = {}) {
  return fetch(NUTRITION_ADMIN_URL + '?action=' + action, { credentials: 'same-origin', ...opts }).then(r => r.json());
}

async function loadNutritionAdmin() {
  try {
    const data = await nutFetch('get_stats');
    if (!data.success) return;
    const s = id => document.getElementById(id);
    if (s('nut-stat-meals')) s('nut-stat-meals').textContent = data.total_meals ?? 0;
    if (s('nut-stat-breakfast')) s('nut-stat-breakfast').textContent = data.breakfast_count ?? 0;
    if (s('nut-stat-ingredients')) s('nut-stat-ingredients').textContent = data.total_ingredients ?? 0;
    if (s('nut-stat-rating')) s('nut-stat-rating').textContent = data.avg_rating ?? 0;
    _nutAllMeals = data.meals || [];
    _nutAllIngredients = data.ingredients || [];
    renderNutMealsTable();
    renderNutIngredientsTable();
    populateMealIngSelect();
  } catch (e) { console.error('loadNutritionAdmin error:', e); }
}

function showNutritionSubTab(tab) {
  document.getElementById('nut-sub-meals').style.display = tab === 'meals' ? '' : 'none';
  document.getElementById('nut-sub-ingredients').style.display = tab === 'ingredients' ? '' : 'none';
  document.getElementById('nut-tab-meals-btn').className = tab === 'meals'
    ? 'px-5 py-2 rounded-lg font-semibold text-sm bg-green-600 text-white'
    : 'px-5 py-2 rounded-lg font-semibold text-sm bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
  document.getElementById('nut-tab-ingredients-btn').className = tab === 'ingredients'
    ? 'px-5 py-2 rounded-lg font-semibold text-sm bg-green-600 text-white'
    : 'px-5 py-2 rounded-lg font-semibold text-sm bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
}

// ── Meals Table ───────────────────────────────────
function renderNutMealsTable() {
  const search = (document.getElementById('nut-meal-search')?.value || '').toLowerCase();
  let filtered = _nutAllMeals.filter(m => {
    if (!search) return true;
    return (m.nom||'').toLowerCase().includes(search) || (m.type||'').toLowerCase().includes(search);
  });
  filtered.sort((a, b) => {
    const col = _nutMealSort.col;
    let va = a[col] ?? '', vb = b[col] ?? '';
    if (['calories','protein','carb','fat'].includes(col)) { va = Number(va); vb = Number(vb); }
    else { va = String(va).toLowerCase(); vb = String(vb).toLowerCase(); }
    if (va < vb) return _nutMealSort.asc ? -1 : 1;
    if (va > vb) return _nutMealSort.asc ? 1 : -1;
    return 0;
  });
  const perPage = 10, totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  _nutMealPage = Math.min(_nutMealPage, totalPages);
  const start = (_nutMealPage - 1) * perPage;
  const paged = filtered.slice(start, start + perPage);
  const tbody = document.getElementById('nut-meals-tbody');
  tbody.innerHTML = paged.length === 0
    ? '<tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Aucun repas.</td></tr>'
    : paged.map(m => {
      const img = m.image ? `<img src="../../${escHtml(m.image)}" class="w-10 h-10 rounded-lg object-cover">` : '<span class="material-symbols-outlined text-slate-300">restaurant</span>';
      return `<tr class="border-t border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50">
        <td class="px-4 py-3">${img}</td>
        <td class="px-4 py-3 font-semibold">${escHtml(m.nom)}</td>
        <td class="px-4 py-3">${escHtml(m.type)}</td>
        <td class="px-4 py-3">${Number(m.calories).toFixed(0)}</td>
        <td class="px-4 py-3 text-xs">${Number(m.protein).toFixed(1)}g / ${Number(m.carb).toFixed(1)}g / ${Number(m.fat).toFixed(1)}g</td>
        <td class="px-4 py-3">
          <div class="flex gap-2">
            <button onclick="editNutMeal(${m.id_meal})" class="text-blue-600 hover:text-blue-800"><span class="material-symbols-outlined text-base">edit</span></button>
            <button onclick="deleteNutMeal(${m.id_meal})" class="text-red-600 hover:text-red-800"><span class="material-symbols-outlined text-base">delete</span></button>
          </div>
        </td>
      </tr>`;
    }).join('');

  const pag = document.getElementById('nut-meals-pagination');
  if (totalPages <= 1) { pag.innerHTML = ''; return; }
  let ph = '';
  for (let i = 1; i <= totalPages; i++) {
    ph += `<button onclick="_nutMealPage=${i};renderNutMealsTable()" class="px-3 py-1 rounded-lg text-sm ${i===_nutMealPage?'bg-green-600 text-white':'bg-slate-200 dark:bg-slate-700 text-slate-600'}">${i}</button>`;
  }
  pag.innerHTML = ph;
}

function sortAdminMeals(col) {
  if (_nutMealSort.col === col) _nutMealSort.asc = !_nutMealSort.asc;
  else { _nutMealSort.col = col; _nutMealSort.asc = true; }
  renderNutMealsTable();
}

function filterAdminMeals() { _nutMealPage = 1; renderNutMealsTable(); }

// ── Ingredients Table ─────────────────────────────
function renderNutIngredientsTable() {
  const search = (document.getElementById('nut-ing-search')?.value || '').toLowerCase();
  let filtered = _nutAllIngredients.filter(i => !search || (i.nom||'').toLowerCase().includes(search));
  const perPage = 10, totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
  _nutIngPage = Math.min(_nutIngPage, totalPages);
  const start = (_nutIngPage - 1) * perPage;
  const paged = filtered.slice(start, start + perPage);
  const tbody = document.getElementById('nut-ing-tbody');
  tbody.innerHTML = paged.length === 0
    ? '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucun ingrédient.</td></tr>'
    : paged.map(i => `<tr class="border-t border-slate-100 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50">
        <td class="px-4 py-3 font-semibold">${escHtml(i.nom)}</td>
        <td class="px-4 py-3">${Number(i.calories).toFixed(0)}</td>
        <td class="px-4 py-3 text-xs">${Number(i.protein).toFixed(1)}g / ${Number(i.carb).toFixed(1)}g / ${Number(i.fat).toFixed(1)}g</td>
        <td class="px-4 py-3">${escHtml(i.eco_score||'-')}</td>
        <td class="px-4 py-3">
          <div class="flex gap-2">
            <button onclick="editNutIngredient(${i.id_ingredient})" class="text-blue-600 hover:text-blue-800"><span class="material-symbols-outlined text-base">edit</span></button>
            <button onclick="deleteNutIngredient(${i.id_ingredient})" class="text-red-600 hover:text-red-800"><span class="material-symbols-outlined text-base">delete</span></button>
          </div>
        </td>
      </tr>`).join('');

  const pag = document.getElementById('nut-ing-pagination');
  if (totalPages <= 1) { pag.innerHTML = ''; return; }
  let ph = '';
  for (let i = 1; i <= totalPages; i++) {
    ph += `<button onclick="_nutIngPage=${i};renderNutIngredientsTable()" class="px-3 py-1 rounded-lg text-sm ${i===_nutIngPage?'bg-green-600 text-white':'bg-slate-200 dark:bg-slate-700 text-slate-600'}">${i}</button>`;
  }
  pag.innerHTML = ph;
}

function filterAdminIngredients() { _nutIngPage = 1; renderNutIngredientsTable(); }

function populateMealIngSelect() {
  const sel = document.getElementById('nut-meal-ing-select');
  if (!sel) return;
  sel.innerHTML = _nutAllIngredients.map(i => `<option value="${i.id_ingredient}">${escHtml(i.nom)} (${Number(i.calories).toFixed(0)} kcal)</option>`).join('');
}

// ── Meal Modal CRUD ───────────────────────────────
function openNutritionMealModal(meal) {
  document.getElementById('nut-meal-id').value = meal?.id_meal || '';
  document.getElementById('nut-meal-nom').value = meal?.nom || '';
  document.getElementById('nut-meal-type').value = meal?.type || 'petit déjeuner';
  document.getElementById('nut-meal-cal').value = meal?.calories || '';
  document.getElementById('nut-meal-prot').value = meal?.protein || '';
  document.getElementById('nut-meal-carb').value = meal?.carb || '';
  document.getElementById('nut-meal-fat').value = meal?.fat || '';
  document.getElementById('nut-meal-image').value = '';
  document.getElementById('nut-meal-modal-title').textContent = meal ? 'Modifier le repas' : 'Ajouter un repas';
  const ingPanel = document.getElementById('nut-meal-ingredients-panel');
  ingPanel.style.display = meal ? '' : 'none';
  if (meal && meal.ingredients) {
    renderMealIngredientsList(meal.ingredients);
  } else {
    document.getElementById('nut-meal-ing-list').innerHTML = '';
  }
  document.getElementById('nut-meal-modal').classList.remove('hidden');
}

function closeNutritionMealModal() {
  document.getElementById('nut-meal-modal').classList.add('hidden');
}

function renderMealIngredientsList(ingredients) {
  const list = document.getElementById('nut-meal-ing-list');
  if (!ingredients?.length) {
    list.innerHTML = '<p class="text-sm text-slate-400">Aucun ingrédient.</p>';
    return;
  }
  list.innerHTML = ingredients.map(i =>
    `<div class="flex items-center justify-between bg-slate-50 dark:bg-slate-700 rounded-lg px-3 py-2">
      <span class="text-sm font-semibold">${escHtml(i.nom)} <span class="text-slate-400">(${Number(i.quantity).toFixed(1)} u.)</span></span>
      <button onclick="removeIngFromMeal(${document.getElementById('nut-meal-id').value}, ${i.id_ingredient})" class="text-red-500 hover:text-red-700"><span class="material-symbols-outlined text-base">close</span></button>
    </div>`
  ).join('');
}

async function addIngredientToMeal() {
  const mealId = document.getElementById('nut-meal-id').value;
  if (!mealId) { showToast('Enregistrez d\'abord le repas', 'error'); return; }
  const ingId = document.getElementById('nut-meal-ing-select').value;
  const qty = document.getElementById('nut-meal-ing-qty').value || 1;
  const fd = new FormData();
  fd.append('meal_id', mealId);
  fd.append('ingredient_id', ingId);
  fd.append('quantity', qty);
  try {
    const res = await nutFetch('add_meal_ingredient', { method: 'POST', body: fd });
    if (res.success) { showToast('Ingrédient ajouté', 'success'); editNutMeal(mealId); }
    else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function removeIngFromMeal(mealId, ingId) {
  const fd = new FormData();
  fd.append('meal_id', mealId);
  fd.append('ingredient_id', ingId);
  try {
    const res = await nutFetch('remove_meal_ingredient', { method: 'POST', body: fd });
    if (res.success) { showToast('Ingrédient retiré', 'success'); editNutMeal(mealId); }
    else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function saveNutritionMeal() {
  const id = document.getElementById('nut-meal-id').value;
  const fd = new FormData();
  if (id) fd.append('id_meal', id);
  fd.append('nom', document.getElementById('nut-meal-nom').value);
  fd.append('type', document.getElementById('nut-meal-type').value);
  fd.append('calories', document.getElementById('nut-meal-cal').value || 0);
  fd.append('protein', document.getElementById('nut-meal-prot').value || 0);
  fd.append('carb', document.getElementById('nut-meal-carb').value || 0);
  fd.append('fat', document.getElementById('nut-meal-fat').value || 0);
  const imgFile = document.getElementById('nut-meal-image').files[0];
  if (imgFile) fd.append('image', imgFile);
  const action = id ? 'update_meal' : 'add_meal';
  try {
    const res = await nutFetch(action, { method: 'POST', body: fd });
    if (res.success) {
      showToast(id ? 'Repas mis à jour' : 'Repas créé', 'success');
      closeNutritionMealModal();
      loadNutritionAdmin();
    } else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function editNutMeal(id) {
  try {
    const res = await nutFetch('get_meal&id=' + id);
    if (res.success && res.meal) openNutritionMealModal(res.meal);
    else showToast('Repas non trouvé', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function deleteNutMeal(id) {
  if (!confirm('Supprimer ce repas ?')) return;
  const fd = new FormData();
  fd.append('id', id);
  try {
    const res = await nutFetch('delete_meal', { method: 'POST', body: fd });
    if (res.success) { showToast('Repas supprimé', 'success'); loadNutritionAdmin(); }
    else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

// ── Ingredient Modal CRUD ─────────────────────────
function openNutritionIngModal(ing) {
  document.getElementById('nut-ing-id').value = ing?.id_ingredient || '';
  document.getElementById('nut-ing-nom').value = ing?.nom || '';
  document.getElementById('nut-ing-cal').value = ing?.calories || '';
  document.getElementById('nut-ing-prot').value = ing?.protein || '';
  document.getElementById('nut-ing-carb').value = ing?.carb || '';
  document.getElementById('nut-ing-fat').value = ing?.fat || '';
  document.getElementById('nut-ing-eco').value = ing?.eco_score || '';
  document.getElementById('nut-ing-modal-title').textContent = ing ? 'Modifier l\'ingrédient' : 'Ajouter un ingrédient';
  document.getElementById('nut-ing-modal').classList.remove('hidden');
}

function closeNutritionIngModal() {
  document.getElementById('nut-ing-modal').classList.add('hidden');
}

async function saveNutritionIngredient() {
  const id = document.getElementById('nut-ing-id').value;
  const fd = new FormData();
  if (id) fd.append('id_ingredient', id);
  fd.append('nom', document.getElementById('nut-ing-nom').value);
  fd.append('calories', document.getElementById('nut-ing-cal').value || 0);
  fd.append('protein', document.getElementById('nut-ing-prot').value || 0);
  fd.append('carb', document.getElementById('nut-ing-carb').value || 0);
  fd.append('fat', document.getElementById('nut-ing-fat').value || 0);
  fd.append('eco_score', document.getElementById('nut-ing-eco').value);
  const action = id ? 'update_ingredient' : 'add_ingredient';
  try {
    const res = await nutFetch(action, { method: 'POST', body: fd });
    if (res.success) {
      showToast(id ? 'Ingrédient mis à jour' : 'Ingrédient créé', 'success');
      closeNutritionIngModal();
      loadNutritionAdmin();
    } else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}

async function editNutIngredient(id) {
  const ing = _nutAllIngredients.find(i => i.id_ingredient == id);
  if (ing) openNutritionIngModal(ing);
  else showToast('Ingrédient non trouvé', 'error');
}

async function deleteNutIngredient(id) {
  if (!confirm('Supprimer cet ingrédient ?')) return;
  const fd = new FormData();
  fd.append('id', id);
  try {
    const res = await nutFetch('delete_ingredient', { method: 'POST', body: fd });
    if (res.success) { showToast('Ingrédient supprimé', 'success'); loadNutritionAdmin(); }
    else showToast(res.error || 'Erreur', 'error');
  } catch (e) { showToast('Erreur réseau', 'error'); }
}