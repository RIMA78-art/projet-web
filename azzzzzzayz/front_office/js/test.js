// front_office/js/test.js
(function(){
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', function(e){
      const age = parseInt((registerForm.querySelector('[name="age"]') || {}).value || '0', 10);
      if (Number.isNaN(age) || age < 12) {
        e.preventDefault();
        alert('Age minimum 12 ans.');
      }
    });
  }

  const timerEl = document.getElementById('timer');
  const progressBar = document.getElementById('progressBar');
  const caloriesEl = document.getElementById('calories');
  const remainingEl = document.getElementById('remaining');
  let sec = 0;
  let paused = false;
  let interval = null;
  const maxSec = 1800;

  function render() {
    const min = String(Math.floor(sec / 60)).padStart(2, '0');
    const s = String(sec % 60).padStart(2, '0');
    if (timerEl) timerEl.textContent = `${min}:${s}`;

    const calories = Math.floor(sec * 0.12);
    const progression = Math.min(100, Math.floor((sec / maxSec) * 100));

    if (caloriesEl) caloriesEl.textContent = calories;
    if (progressBar) {
      progressBar.style.width = progression + '%';
      progressBar.textContent = progression + '%';
    }

    const remaining = Math.max(0, maxSec - sec);
    const rmin = String(Math.floor(remaining / 60)).padStart(2, '0');
    const rs = String(remaining % 60).padStart(2, '0');
    if (remainingEl) remainingEl.textContent = `Temps restant: ${rmin}:${rs}`;
  }

  const startBtn = document.getElementById('startBtn');
  if (startBtn) {
    startBtn.addEventListener('click', () => {
      if (interval) return;
      interval = setInterval(() => {
        if (!paused) {
          sec += 1;
          render();
        }
      }, 1000);
    });
  }

  const pauseBtn = document.getElementById('pauseBtn');
  if (pauseBtn) pauseBtn.addEventListener('click', () => { paused = !paused; });

  const finishBtn = document.getElementById('finishBtn');
  if (finishBtn) {
    finishBtn.addEventListener('click', () => {
      const calories = Math.floor(sec * 0.12);
      const progression = Math.min(100, Math.floor((sec / maxSec) * 100));
      document.getElementById('duree_effectuee').value = Math.floor(sec / 60);
      document.getElementById('calories_brulees').value = calories;
      document.getElementById('progression').value = progression;
      alert('Bravo seance terminee');
      document.getElementById('finishForm').submit();
    });
  }
})();
