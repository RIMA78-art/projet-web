// back_office/test1.js
(function(){
  if (window.dashboardData) {
    const caloriesCtx = document.getElementById('chartCalories');
    const diffCtx = document.getElementById('chartDifficulty');
    const weekCtx = document.getElementById('chartWeekly');

    if (caloriesCtx) {
      new Chart(caloriesCtx, {
        type: 'line',
        data: {
          labels: window.dashboardData.caloriesLabels,
          datasets: [{ label: 'Calories', data: window.dashboardData.caloriesValues, borderColor: '#16a34a', tension: .3 }]
        }
      });
    }

    if (diffCtx) {
      new Chart(diffCtx, {
        type: 'doughnut',
        data: {
          labels: window.dashboardData.difficultyLabels,
          datasets: [{ data: window.dashboardData.difficultyValues, backgroundColor: ['#22c55e', '#38bdf8', '#f97316'] }]
        }
      });
    }

    if (weekCtx) {
      new Chart(weekCtx, {
        type: 'bar',
        data: {
          labels: window.dashboardData.weeklyLabels,
          datasets: [{ label: 'Seances', data: window.dashboardData.weeklyValues, backgroundColor: '#22d3ee' }]
        }
      });
    }

    const calendarEl = document.getElementById('calendar');
    if (calendarEl && window.FullCalendar) {
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [
          { title: 'Cardio', date: new Date().toISOString().slice(0,10) },
          { title: 'Musculation', date: new Date(Date.now() + 86400000).toISOString().slice(0,10) }
        ]
      });
      calendar.render();
    }
  }

  const progInput = document.querySelector('#tableProgrammeFilter');
  if (progInput) {
    progInput.addEventListener('input', function(){
      const q = this.value.toLowerCase();
      document.querySelectorAll('#tableProgramme tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  const coachInput = document.querySelector('#tableCoachFilter');
  if (coachInput) {
    coachInput.addEventListener('input', function(){
      const q = this.value.toLowerCase();
      document.querySelectorAll('#tableCoach tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
})();
