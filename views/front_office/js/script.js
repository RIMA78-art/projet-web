// PAGE NAVIGATION
console.log('script.js loaded');
const APP_FOLDER = 'Integration_FINAL_NEW_KN';
let API_ROOT = '../../index.php';
console.log('Cart API root:', API_ROOT);

// PAGES PROTÉGÉES - nécessite authentification
const PROTECTED_PAGES = ['sport', 'nutrition', 'boutique', 'community'];

// Vérifier si l'utilisateur est connecté
function isUserLoggedIn() {
  const user = localStorage.getItem('nutrinova_user');
  return user !== null;
}

// Obtenir les infos de l'utilisateur connecté
function getLoggedInUser() {
  const user = localStorage.getItem('nutrinova_user');
  return user ? JSON.parse(user) : null;
}

// Fonction de navigation avec vérification d'authentification
function showPage(name) {
  console.log('showPage called with:', name);
  
  // Vérifier si la page est protégée
  if (PROTECTED_PAGES.includes(name) && !isUserLoggedIn()) {
    showToast('❌ Veuillez d\'abord vous connecter');
    showPage('login');
    return;
  }
  
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.getElementById('page-' + name).classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  setTimeout(initReveal, 100);
  
  if (name === 'panier') {
    console.log('Loading cart...');
    loadCart();
  }
  if (name === 'register') {
    console.log('Register page shown, attaching form listener');
    const form = document.getElementById('register-form');
    if (form) {
      console.log('Form found, current onsubmit:', form.onsubmit);
    } else {
      console.log('ERROR: register-form not found!');
    }
  }
  if (name === 'login') {
    console.log('Login page shown');
    updateLoginUI();
  }
  if (name === 'community') {
    console.log('Community page shown, loading posts');
    loadPostsFromDB();
  }
  
  // Mettre à jour la navbar
  updateNavbar();
}

// Mettre à jour la navbar en fonction de l'état de connexion
function updateNavbar() {
  const navActions = document.querySelector('.nav-actions');
  const user = getLoggedInUser();
  
  if (user && navActions) {
    navActions.innerHTML = `
      <div style="display: flex; align-items: center; gap: 15px;">
        <span style="color: #666; font-size: 14px;">Bienvenue ${user.prenom} !</span>
        <button class="btn-ghost" onclick="logout()" style="background: #ff6b6b; color: white;">Déconnexion</button>
      </div>
    `;
  }
}

// Fonction de déconnexion
function logout() {
  localStorage.removeItem('nutrinova_user');
  localStorage.removeItem('nutrinova_cart');
  showToast('✅ Déconnecté');
  updateNavbar();
  showPage('home');
}

// TOAST
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// LOAD CART
function loadCart() {
  console.log('loadCart called');
  const user = getLoggedInUser();
  const cartDiv = document.getElementById('cart-items');
  const renderCart = (cart) => {
    console.log('Cart items:', cart);
    if (!cartDiv) return;
    if (cart.length > 0) {
      let html = '';
      cart.forEach((item, index) => {
        const productInfo = getProductInfo(item.Nom);
        html += `
          <div class="product-card">
            <div class="product-img-placeholder" style="background:${productInfo.bg}">${productInfo.emoji}</div>
            <div class="product-body">
              <h3>${item.Nom}</h3>
              <div class="product-footer">
                <span class="product-price">${item.Prix}€</span>
                <button class="btn-ghost" onclick="removeFromCart(${index})" style="padding:4px 8px; font-size:12px;">Supprimer</button>
              </div>
            </div>
          </div>
        `;
      });
      cartDiv.innerHTML = html;
    } else {
      cartDiv.innerHTML = '<p>Votre panier est vide.</p>';
    }
  };

  if (user && user.email) {
    fetch(`${API_ROOT}?action=get_cart&email=${encodeURIComponent(user.email)}`)
      .then(r => r.json())
      .then(data => {
        if (Array.isArray(data)) {
          const normalized = data.map(item => ({
            ...item,
            Prix: parseFloat(item.Prix),
            dbId: item.id || null
          }));
          localStorage.setItem('nutrinova_cart', JSON.stringify(normalized));
          renderCart(normalized);
        } else {
          console.warn('Cart fetch returned non-array:', data);
          const localCart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
          renderCart(localCart);
        }
      })
      .catch(err => {
        console.error('Error fetching cart from DB:', err);
        const localCart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
        renderCart(localCart);
      });
  } else {
    try {
      const localCart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
      renderCart(localCart);
    } catch (e) {
      console.error('Error loading cart:', e);
      if (cartDiv) cartDiv.innerHTML = '<p>❌ Erreur lors du chargement du panier.</p>';
    }
  }
}

// REMOVE FROM CART
function removeFromCart(index) {
  let cart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
  const item = cart[index];
  const userEmail = getLoggedInUser()?.email || '';
  if (item && item.dbId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", API_ROOT + "?action=remove_from_cart", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            console.log('✅ Supprimé en base de données');
          } else {
            console.log('⚠️ Échec suppression DB: ' + (response.error || 'Unknown'));
          }
        } catch (e) {
          console.log('⚠️ Réponse DB invalide');
        }
      }
    };
    xhr.send("email=" + encodeURIComponent(userEmail) + "&id=" + encodeURIComponent(item.dbId));
  } else if (item) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", API_ROOT + "?action=remove_from_cart", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        try {
          console.log('Remove cart response:', xhr.responseText);
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            console.log('✅ Supprimé en base de données');
          } else {
            console.log('⚠️ Échec suppression DB: ' + (response.error || 'Unknown'));
          }
        } catch (e) {
          console.log('⚠️ Réponse DB invalide');
        }
      }
    };
    xhr.send("email=" + encodeURIComponent(userEmail) + "&nom=" + encodeURIComponent(item.Nom) + "&prix=" + encodeURIComponent(item.Prix));
  }

  cart.splice(index, 1);
  localStorage.setItem('nutrinova_cart', JSON.stringify(cart));
  loadCart();
  showToast('✅ Produit supprimé du panier');
}

// GET PRODUCT INFO
function getProductInfo(nom) {
  const products = {
    'Kit Salad Detox': { emoji: '🥗', bg: 'linear-gradient(135deg,#E8F5E9,#C8E6C9)' },
    'Omega-3 Végétal': { emoji: '💊', bg: 'linear-gradient(135deg,#E3F2FD,#BBDEFB)' },
    'Protéine de Pois Bio': { emoji: '🥤', bg: 'linear-gradient(135deg,#FFF3E0,#FFE0B2)' },
    'Gourde Inox 750ml': { emoji: '🎽', bg: 'linear-gradient(135deg,#F3E5F5,#E1BEE7)' },
    'Mix Superfoods': { emoji: '🫐', bg: 'linear-gradient(135deg,#E0F7FA,#80DEEA)' },
    'Bandes Élastiques Pro': { emoji: '🏃', bg: 'linear-gradient(135deg,#FCE4EC,#F48FB1)' },
    'Magnésium Bisglycinate': { emoji: '🌿', bg: 'linear-gradient(135deg,#F1F8E9,#C5E1A5)' },
    'Carnet de suivi Sport': { emoji: '📔', bg: 'linear-gradient(135deg,#E8EAF6,#9FA8DA)' }
  };
  return products[nom] || { emoji: '❓', bg: 'linear-gradient(135deg,#f0f0f0,#e0e0e0)' };
}

// ADD TO CART
function addToCart(nom, prix) {
  // Save to localStorage (instant, local)
  let cart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
  const newItem = { Nom: nom, Prix: prix, dbId: null };
  cart.push(newItem);
  localStorage.setItem('nutrinova_cart', JSON.stringify(cart));
  
  // Also sync to database using new MVC routing
  const userEmail = getLoggedInUser()?.email || '';
  const xhr = new XMLHttpRequest();
  xhr.open("POST", API_ROOT + "?action=add_to_cart", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      try {
        console.log('Add cart response:', xhr.responseText);
        const response = JSON.parse(xhr.responseText);
        if (response.success && response.id) {
          cart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
          for (let i = cart.length - 1; i >= 0; i--) {
            if (cart[i].Nom === nom && cart[i].Prix === prix && !cart[i].dbId) {
              cart[i].dbId = response.id;
              break;
            }
          }
          localStorage.setItem('nutrinova_cart', JSON.stringify(cart));
          console.log('✅ Sauvegardé en base de données avec id', response.id);
          if (getLoggedInUser()) loadCart();
        } else {
          console.log('⚠️ Sauvegarde locale réussie (base de données indisponible)');
        }
      } catch (e) {
        console.log('⚠️ Sauvegarde locale réussie (base de données indisponible)');
      }
    }
  };
  xhr.send("email=" + encodeURIComponent(userEmail) + "&nom=" + encodeURIComponent(nom) + "&prix=" + encodeURIComponent(prix));
  
  showToast('✅ Produit ajouté avec succès au panier !');
  console.log('Product added to cart:', nom, prix);
}

// Fonction pour charger tous les posts depuis la BD
function loadPosts() {
  console.log('loadPosts called');
  
  fetch('../../index.php?action=get_all_posts')
    .then(r => r.json())
    .then(result => {
      if (result.success && result.posts) {
        const container = document.getElementById('forum-posts-container');
        if (container) {
          // Garder les posts déjà présents dans le HTML et ajouter les posts depuis la BD
          const existingPosts = container.querySelectorAll('.forum-post').length;
          console.log('Posts existants dans le HTML:', existingPosts);
          
          // On charge les posts depuis la BD mais on ne remplace pas les posts du HTML
          // car ils contiennent les posts en dur du site
        }
      }
    })
    .catch(err => console.error('Erreur lors du chargement des posts:', err));
}

// Fonction alternative : remplacer les posts du HTML par ceux de la BD
function loadPostsFromDB() {
  console.log('loadPostsFromDB called');
  
  fetch('../../index.php?action=get_all_posts')
    .then(r => r.json())
    .then(result => {
      if (result.success && result.posts) {
        const container = document.getElementById('forum-posts-container');
        if (container && result.posts.length > 0) {
          const user = getLoggedInUser();
          let postsHtml = '';
          
          result.posts.forEach(post => {
            const initials = post.nom_auteur.split(' ').map(n => n.charAt(0)).join('');
            const colors = ['#4CAF50', '#1E3A8A', '#F97316', '#8B5CF6', '#EC4899'];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            
            const date = new Date(post.created_at);
            const timeAgo = getTimeAgo(date);
            
            // Vérifier si l'utilisateur actuel peut modifier/supprimer ce post
            const isAuthor = user && (user.prenom + ' ' + user.nom) === post.nom_auteur;
            let actionButtons = '';
            if (isAuthor) {
              actionButtons = `
                <span class="post-action" onclick="openEditPostModal(${post.id}, '${post.titre_post.replace(/'/g, "\\'")}', '${post.contenu_post.replace(/'/g, "\\'")}')" style="cursor: pointer; color: #007bff;">✏️ Modifier</span>
                <span class="post-action" onclick="deletePost(${post.id})" style="cursor: pointer; color: #dc3545;">🗑️ Supprimer</span>
              `;
            }
            
            postsHtml += `
              <div class="forum-post reveal" id="post-${post.id}">
                <div class="post-header">
                  <div class="avatar" style="background:linear-gradient(135deg,${randomColor},${randomColor}dd)">${initials}</div>
                  <div class="post-meta">
                    <strong>${post.nom_auteur}</strong>
                    <span>${timeAgo}</span>
                  </div>
                </div>
                <h4 style="margin: 12px 0 8px 0; color: #333; font-weight: 600;">${post.titre_post}</h4>
                <p class="post-content">${post.contenu_post}</p>
                <div class="post-actions">
                  <span class="post-action" onclick="showToast('❤️ Aimé !')">❤️ 0</span>
                  <span class="post-action">💬 0 réponses</span>
                  <span class="post-action" onclick="showToast('🔁 Partage envoyé !')">🔁 Partager</span>
                  ${actionButtons}
                </div>
              </div>
            `;
          });
          
          container.innerHTML = postsHtml;
        }
      }
    })
    .catch(err => console.error('Erreur lors du chargement des posts:', err));
}

// Fonction pour calculer le temps écoulé
function getTimeAgo(date) {
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return 'À l\'instant';
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `Il y a ${minutes}m`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `Il y a ${hours}h`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `Il y a ${days}j`;
  return date.toLocaleDateString();
}
function showPostModal() {
  document.getElementById('post-title-input').value = '';
  document.getElementById('post-content-input').value = '';
  document.getElementById('title-counter').textContent = '0/50';
  document.getElementById('content-counter').textContent = '0/500';
  document.getElementById('post-modal-overlay').classList.add('active');
}

function closePostModal() {
  document.getElementById('post-modal-overlay').classList.remove('active');
}

// Mettre à jour les compteurs de caractères
function initPostModal() {
  const titleInput = document.getElementById('post-title-input');
  const contentInput = document.getElementById('post-content-input');
  const titleCounter = document.getElementById('title-counter');
  const contentCounter = document.getElementById('content-counter');
  
  if (titleInput && titleCounter) {
    titleInput.addEventListener('input', function() {
      titleCounter.textContent = this.value.length + '/50';
    });
  }
  
  if (contentInput && contentCounter) {
    contentInput.addEventListener('input', function() {
      contentCounter.textContent = this.value.length + '/500';
    });
  }
  
  // Pour le modal d'édition
  const editTitleInput = document.getElementById('edit-post-title-input');
  const editContentInput = document.getElementById('edit-post-content-input');
  const editTitleCounter = document.getElementById('edit-title-counter');
  const editContentCounter = document.getElementById('edit-content-counter');
  
  if (editTitleInput && editTitleCounter) {
    editTitleInput.addEventListener('input', function() {
      editTitleCounter.textContent = this.value.length + '/50';
    });
  }
  
  if (editContentInput && editContentCounter) {
    editContentInput.addEventListener('input', function() {
      editContentCounter.textContent = this.value.length + '/500';
    });
  }
}

function submitPost() {
  const user = getLoggedInUser();
  if (!user) {
    showToast('❌ Vous devez d\'abord vous connecter');
    return;
  }
  
  const titre = document.getElementById('post-title-input').value.trim();
  const contenu = document.getElementById('post-content-input').value.trim();
  
  // Validation
  if (!titre || !contenu) {
    showToast('❌ Titre et contenu requis');
    return;
  }
  
  if (titre.length > 50) {
    showToast('❌ Le titre ne doit pas dépasser 50 caractères');
    return;
  }
  
  if (contenu.length > 500) {
    showToast('❌ Le contenu ne doit pas dépasser 500 caractères');
    return;
  }
  
  // Préparer les données
  const data = new FormData();
  data.append('nom_auteur', user.prenom + ' ' + user.nom);
  data.append('titre_post', titre);
  data.append('contenu_post', contenu);
  
  // Envoyer au serveur
  fetch('../../index.php?action=create_post', {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      // Recharger les posts depuis la BD
      closePostModal();
      loadPostsFromDB();
      showToast('✅ Votre post a été publié !');
    } else {
      showToast('❌ ' + (result.error || 'Erreur lors de la publication'));
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    showToast('❌ Erreur réseau');
  });
}

// CONTACT FORM
function handleContact(e) {
  e.preventDefault();
  showToast('✅ Message envoyé ! Réponse sous 24h.');
  e.target.reset();
}

// LOGIN
function handleLogin(e) {
  e.preventDefault();
  
  const form = document.getElementById('login-form');
  const email = document.getElementById('login-email').value.trim();
  const mot_de_passe = document.getElementById('login-password').value;
  
  if (!email || !mot_de_passe) {
    showToast('❌ Email et mot de passe requis');
    return;
  }
  
  const data = new FormData();
  data.append('email', email);
  data.append('mot_de_passe', mot_de_passe);
  
  fetch('../../index.php?action=login', {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      // Sauvegarder l'utilisateur dans localStorage
      localStorage.setItem('nutrinova_user', JSON.stringify(result.user));
      showToast('✅ Connexion réussie !');
      form.reset();
      updateNavbar();
      setTimeout(() => showPage('home'), 1200);
    } else {
      showToast('❌ ' + (result.error || 'Connexion échouée'));
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    showToast('❌ Erreur réseau');
  });
}

// REGISTER
function handleRegister(e) {
  console.log('handleRegister called');
  if (e && typeof e.preventDefault === 'function') {
    e.preventDefault();
  }
  
  const form = document.getElementById('register-form');
  const data = new FormData(form);
  
  console.log('Submitting form to MVC register controller');
  
  fetch('../../index.php?action=register', {
    method: 'POST',
    body: data
  })
  .then(r => {
    console.log('Response received, status:', r.status);
    return r.text();
  })
  .then(text => {
    console.log('Raw response text:', text);
    try {
      const result = JSON.parse(text);
      console.log('Parsed JSON:', result);
      
      if (result.success) {
        // Récupérer les données du formulaire
        const email = document.getElementById('register-email').value;
        const nomination = document.getElementById('register-lastname').value;
        const prenom = document.getElementById('register-firstname').value;
        
        // Créer l'objet utilisateur
        const user = {
          id: result.id,
          email: email,
          nom: nomination,
          prenom: prenom
        };
        
        // Sauvegarder l'utilisateur dans localStorage
        localStorage.setItem('nutrinova_user', JSON.stringify(user));
        
        showToast('✅ Compte créé et connecté !');
        form.reset();
        updateNavbar();
        setTimeout(() => showPage('home'), 1200);
      } else {
        showToast('❌ ' + (result.error || 'Inscription échouée'));
      }
    } catch (err) {
      console.error('JSON parse error:', err);
      showToast('❌ Erreur serveur: ' + text.substring(0, 100));
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    showToast('❌ Erreur réseau');
  });
}

// Mettre à jour l'interface login si l'utilisateur est connecté
function updateLoginUI() {
  const user = getLoggedInUser();
  if (user) {
    const loginContainer = document.getElementById('page-login');
    if (loginContainer) {
      loginContainer.innerHTML = `
        <div style="text-align: center; padding: 100px 20px;">
          <h2>Vous êtes déjà connecté !</h2>
          <p>Bienvenue <strong>${user.prenom} ${user.nom}</strong></p>
          <p>Email : ${user.email}</p>
          <div style="margin-top: 30px;">
            <button class="btn-primary" onclick="showPage('home')">Retour à l'accueil</button>
            <button class="btn-outline" onclick="logout()" style="margin-left: 10px;">Se déconnecter</button>
          </div>
        </div>
      `;
    }
  }
}

// Store for edit modal
let currentEditingPostId = null;

// EDIT POST MODAL
function openEditPostModal(postId, titre, contenu) {
  currentEditingPostId = postId;
  document.getElementById('edit-post-title-input').value = titre;
  document.getElementById('edit-post-content-input').value = contenu;
  document.getElementById('edit-title-counter').textContent = titre.length + '/50';
  document.getElementById('edit-content-counter').textContent = contenu.length + '/500';
  document.getElementById('edit-post-modal-overlay').classList.add('active');
}

function closeEditPostModal() {
  document.getElementById('edit-post-modal-overlay').classList.remove('active');
  currentEditingPostId = null;
}

function submitEditPost() {
  if (!currentEditingPostId) {
    showToast('❌ Erreur : ID du post manquant');
    return;
  }
  
  const titre = document.getElementById('edit-post-title-input').value.trim();
  const contenu = document.getElementById('edit-post-content-input').value.trim();
  
  // Validation
  if (!titre || !contenu) {
    showToast('❌ Titre et contenu requis');
    return;
  }
  
  if (titre.length > 50) {
    showToast('❌ Le titre ne doit pas dépasser 50 caractères');
    return;
  }
  
  if (contenu.length > 500) {
    showToast('❌ Le contenu ne doit pas dépasser 500 caractères');
    return;
  }
  
  // Préparer les données
  const data = new FormData();
  data.append('post_id', currentEditingPostId);
  data.append('titre_post', titre);
  data.append('contenu_post', contenu);
  
  // Envoyer au serveur
  fetch('../../index.php?action=update_post_' + currentEditingPostId, {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      showToast('✅ Post modifié avec succès');
      closeEditPostModal();
      // Recharger les posts
      loadPostsFromDB();
    } else {
      showToast('❌ ' + (result.error || 'Erreur lors de la modification'));
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    showToast('❌ Erreur réseau');
  });
}

// DELETE POST
function deletePost(postId) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce post ? Cette action est irréversible.')) {
    return;
  }
  
  const data = new FormData();
  data.append('post_id', postId);
  
  fetch('../../index.php?action=delete_post_' + postId, {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(result => {
    if (result.success) {
      showToast('✅ Post supprimé avec succès');
      const postElement = document.getElementById('post-' + postId);
      if (postElement) {
        postElement.remove();
      }
      // Recharger les posts au cas où
      setTimeout(() => loadPostsFromDB(), 500);
    } else {
      showToast('❌ ' + (result.error || 'Erreur lors de la suppression'));
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    showToast('❌ Erreur réseau');
  });
}

function updateBMI() {
  const taille = parseFloat(document.getElementById('taille')?.value);
  const poids = parseFloat(document.getElementById('poids')?.value);
  const label = document.getElementById('bmi-value');
  if (!label) return;
  if (!taille || !poids || taille <= 0) {
    label.textContent = 'Entrez votre taille et poids';
    return;
  }
  const bmi = poids / ((taille / 100) ** 2);
  const classification = bmi < 18.5 ? 'Maigre' : bmi < 25 ? 'Normal' : bmi < 30 ? 'Surpoids' : 'Obésité';
  label.textContent = `${bmi.toFixed(1)} — ${classification}`;
}

document.getElementById('taille')?.addEventListener('input', updateBMI);
document.getElementById('poids')?.addEventListener('input', updateBMI);

// NUTRITION AI MENU GENERATOR
const menus = {
  'Perte de poids': {
    breakfast: 'Porridge avoine + fruits rouges + graines de chia', bkcal: '340 kcal',
    lunch: 'Salade quinoa, avocat, tomates cerises, poulet grillé', lkcal: '480 kcal',
    snack: 'Pomme + 10 amandes + yaourt grec nature', skcal: '190 kcal',
    dinner: 'Saumon vapeur, brocoli, patate douce rôtie', dkcal: '420 kcal',
    prot: 32, carb: 40, fat: 28
  },
  'Prise de masse': {
    breakfast: 'Omelette 4 œufs, avocat, pain de seigle, jus d\'orange', bkcal: '620 kcal',
    lunch: 'Riz brun 200g, blanc de poulet 200g, légumes vapeur, huile olive', lkcal: '780 kcal',
    snack: 'Shake protéine pois + banane + beurre amande', skcal: '380 kcal',
    dinner: 'Saumon 200g, quinoa 150g, épinards sautés à l\'ail', dkcal: '620 kcal',
    prot: 38, carb: 42, fat: 20
  },
  'Maintien': {
    breakfast: 'Granola maison + yaourt grec + miel + noix', bkcal: '420 kcal',
    lunch: 'Pâtes complètes, sauce tomate maison, parmesan, basilic', lkcal: '560 kcal',
    snack: 'Smoothie banane-épinards-gingembre + poignée noisettes', skcal: '240 kcal',
    dinner: 'Curry légumes, tofu, lait de coco, riz basmati', dkcal: '480 kcal',
    prot: 25, carb: 50, fat: 25
  },
  'Performance sportive': {
    breakfast: 'Gruau avoine + protéine whey + fruits tropicaux + miel', bkcal: '580 kcal',
    lunch: 'Bowl riz, poulet teriyaki, edamame, avocat, sésame', lkcal: '720 kcal',
    snack: 'Barres dattes + fruits secs maison + shake BCAA', skcal: '310 kcal',
    dinner: 'Thon mi-cuit, lentilles corail, salade roquette, citron', dkcal: '540 kcal',
    prot: 35, carb: 47, fat: 18
  },
  'Santé générale': {
    breakfast: 'Toast avocat + œuf poché + graines tournesol + thé vert', bkcal: '390 kcal',
    lunch: 'Soupe miso, tofu soyeux, légumes croquants, algues kombu', lkcal: '340 kcal',
    snack: 'Kéfir + baies fraîches + curcuma + poivre noir', skcal: '180 kcal',
    dinner: 'Cabillaud citronné, ratatouille méditerranéenne, farro', dkcal: '460 kcal',
    prot: 28, carb: 44, fat: 28
  }
};

function generateMenu() {
  const obj = document.getElementById('objectif').value;
  const m = menus[obj] || menus['Maintien'];
  document.getElementById('menu-label').textContent = obj;
  document.getElementById('breakfast').textContent = m.breakfast;
  document.getElementById('breakfast-kcal').textContent = m.bkcal;
  document.getElementById('lunch').textContent = m.lunch;
  document.getElementById('lunch-kcal').textContent = m.lkcal;
  document.getElementById('snack').textContent = m.snack;
  document.getElementById('snack-kcal').textContent = m.skcal;
  document.getElementById('dinner').textContent = m.dinner;
  document.getElementById('dinner-kcal').textContent = m.dkcal;
  document.getElementById('prot-bar').style.width = m.prot + '%';
  document.getElementById('carb-bar').style.width = m.carb + '%';
  document.getElementById('fat-bar').style.width = m.fat + '%';
  document.getElementById('prot-label').textContent = m.prot + '%';
  document.getElementById('carb-label').textContent = m.carb + '%';
  document.getElementById('fat-label').textContent = m.fat + '%';
  document.getElementById('menu-result').style.display = 'block';
  showToast('🧠 Menu généré par l\'IA !');
}

// SHOP FILTER
function filterCategory(btn, cat) {
  document.querySelectorAll('#page-boutique .btn-ghost, #page-boutique .btn-primary').forEach(b => {
    b.className = 'btn-ghost';
  });
  btn.className = 'btn-primary';
  document.querySelectorAll('.product-card').forEach(card => {
    if (cat === 'all' || card.dataset.cat === cat) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}

// MOBILE MENU
function toggleMobileMenu() {
  showToast('Menu mobile : utilisez les boutons de navigation !');
}

// SCROLL REVEAL
function initReveal() {
  const els = document.querySelectorAll('.reveal');
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) e.target.classList.add('visible');
    });
  }, { threshold: 0.1 });
  els.forEach(el => obs.observe(el));
}

// INIT
document.addEventListener('DOMContentLoaded', () => {
  initReveal();
  updateNavbar(); // Mettre à jour la navbar au chargement
  initPostModal(); // Initialiser le modal des posts
});
