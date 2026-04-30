// PAGE NAVIGATION
console.log('script.js loaded');

function showPage(name) {
  console.log('showPage called with:', name);
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
  try {
    let cart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
    console.log('Cart items:', cart);
    
    const cartDiv = document.getElementById('cart-items');
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
  } catch (e) {
    console.error('Error loading cart:', e);
    document.getElementById('cart-items').innerHTML = '<p>❌ Erreur lors du chargement du panier.</p>';
  }
}

// REMOVE FROM CART
function removeFromCart(index) {
  let cart = JSON.parse(localStorage.getItem('nutrinova_cart') || '[]');
  const item = cart[index];
  const userEmail = localStorage.getItem('nutrinova_user') || '';
  if (item && item.dbId) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "index.php?action=remove_from_cart", true);
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
    xhr.open("POST", "index.php?action=remove_from_cart", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        console.log('⚠️ Suppression de secours DB tentée');
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
  
  // Also sync to database
  const userEmail = localStorage.getItem('nutrinova_user') || '';
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "index.php?action=add_to_cart", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      try {
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

// COMMUNITY POST MODAL
function showPostModal() {
  document.getElementById('post-content-input').value = '';
  document.getElementById('post-modal-overlay').classList.add('active');
}

function closePostModal() {
  document.getElementById('post-modal-overlay').classList.remove('active');
}

function submitPost() {
  const content = document.getElementById('post-content-input').value.trim();
  if (!content) {
    showToast('✏️ Écris quelque chose avant de poster.');
    return;
  }
  const postHtml = `
    <div class="forum-post reveal">
      <div class="post-header">
        <div class="avatar" style="background:linear-gradient(135deg,#34D399,#059669)">TU</div>
        <div class="post-meta">
          <strong>Vous</strong>
          <span>Aujourd'hui</span>
        </div>
      </div>
      <p class="post-content">${content}</p>
      <div class="post-actions">
        <span class="post-action" onclick="showToast('❤️ Aimé !')">❤️ 0</span>
        <span class="post-action">💬 0 réponses</span>
        <span class="post-action" onclick="showToast('🔁 Partage envoyé !')">🔁 Partager</span>
      </div>
    </div>
  `;
  const firstColumn = document.querySelector('.forum-layout > div');
  if (firstColumn) {
    firstColumn.insertAdjacentHTML('afterbegin', postHtml);
  }
  closePostModal();
  showToast('✅ Votre discussion a été postée !');
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
  showToast('✅ Connexion réussie ! Bienvenue !');
  setTimeout(() => showPage('home'), 1200);
}

// REGISTER
function handleRegister(e) {
  console.log('handleRegister called');
  if (e && typeof e.preventDefault === 'function') {
    e.preventDefault();
  }
  
  const form = document.getElementById('register-form');
  const data = new FormData(form);
  
  console.log('Submitting form to register_user.php');
  
  fetch('register_user.php', {
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
        showToast('Account created successfully!');
        form.reset();
        setTimeout(() => showPage('home'), 1200);
      } else {
        showToast(result.error || 'Registration failed');
      }
    } catch (err) {
      console.error('JSON parse error:', err);
      showToast('Server error: ' + text.substring(0, 100));
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    showToast('Network error');
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
});