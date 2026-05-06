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
    const response = await fetch(API_URL + '?action=get-all', { credentials: 'same-origin' });
    const result = await response.json();

    console.log('get-all response:', result); // DEBUG

    if (result.success && Array.isArray(result.users)) {
      allUsers = result.users;
      renderUsersTable();
      updateUserStats();
    } else if (result.code === 401 || result.code === 403) {
      window.location.href = '../frontoffice/login.php';
    } else {
      console.error('Erreur get-all:', result.message);
      showToast('Erreur: ' + (result.message || 'Réponse invalide'), 'error');
    }
  } catch (error) {
    console.error('Erreur réseau loadUsers:', error);
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