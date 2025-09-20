document.addEventListener("DOMContentLoaded", () => {
  // --- Sidebar & Overlay ---
  const sidebar = document.getElementById("sidebar");
  let overlay = document.getElementById("overlay");

  // Create overlay  
  if (!overlay) {
    overlay = document.createElement("div");
    overlay.id = "overlay";
    overlay.classList.add("overlay");
    document.body.appendChild(overlay);
  }

  const sidebarToggle = document.getElementById("sidebarToggle");

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", () => {
      document.body.classList.toggle("sidebar-open");
      overlay.classList.toggle("active");
    });
  }

  if (overlay) {
    overlay.addEventListener("click", () => {
      document.body.classList.remove("sidebar-open");
      overlay.classList.remove("active");
    });
  }

  // --- Sidebar Dropdowns ---
  document.querySelectorAll('.sidebar .has-dropdown > a').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();

        const dropdown = link.nextElementSibling;
        const isOpen = dropdown.classList.contains('dropdown-open');

        // Toggle the clicked dropdown
        if (isOpen) {
            dropdown.classList.remove('dropdown-open');
            link.classList.remove('open');
        } else {
            dropdown.classList.add('dropdown-open');
            link.classList.add('open');
        }
    });
});

  // --- Profile Dropdown ---
  const profileDropdown = document.querySelector(".profile-dropdown");
  if (profileDropdown) {
    profileDropdown.addEventListener("click", () => {
      profileDropdown.classList.toggle("open");
    });
  }
});

   // Charts  
(function(){
  if(typeof Chart === 'undefined'){ return; }
  const data = window.dashboardData || {};
  const specialtyLabels = Array.isArray(data.specialtyLabels) ? data.specialtyLabels : [];
  const specialtyCounts = Array.isArray(data.specialtyCounts) ? data.specialtyCounts : [];
  const monthlyAppointments = Array.isArray(data.monthlyAppointments) ? data.monthlyAppointments : [];

  // Doctors per Specialty (Bar)
  const docSpecEl = document.getElementById('doctorSpecialtyChart');
  if(docSpecEl){
    new Chart(docSpecEl.getContext('2d'), {
      type: 'bar',
      data: {
        labels: specialtyLabels,
        datasets: [{
          label: 'Doctors per Specialty',
          data: specialtyCounts,
          backgroundColor: '#127137',
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  // Appointments per Month (Line)
  const apptEl = document.getElementById('appointmentsChart');
  if(apptEl){
    const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    new Chart(apptEl.getContext('2d'), {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Appointments per Month',
          data: monthlyAppointments.length === 12 ? monthlyAppointments : new Array(12).fill(0),
          backgroundColor: 'rgba(18,113,55,0.2)',
          borderColor: '#127137',
          borderWidth: 2,
          fill: true,
          tension: 0.3,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    });
  }

  // Mini Trend Charts
  function createTrendChart(canvasId, points, color){
    const el = document.getElementById(canvasId);
    if(!el) return;
    const ctx = el.getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: points.map((_, i) => i + 1),
        datasets: [{
          data: points,
          borderColor: color,
          backgroundColor: color + '33',
          fill: true,
          tension: 0.3,
          pointRadius: 0
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { display: false }, y: { display: false } }
      }
    });
  }

  const trends = data.trends || {};
  createTrendChart('avgAppointmentsTrend', trends.avgAppointmentsTrend || [2,3,4,5,4,6,5,7,6,8,7,9], '#ffffff');
  createTrendChart('patientGrowthTrend', trends.patientGrowthTrend || [10,12,15,18,20,22,25,28,30,32,35,40], '#ffffff');
  createTrendChart('activeDoctorsTrend', trends.activeDoctorsTrend || [5,6,5,7,6,7,8,9,10,9,11,12], '#ffffff');
  createTrendChart('pendingAppointmentsTrend', trends.pendingAppointmentsTrend || [3,2,4,5,6,5,4,5,6,5,4,3], '#ffffff');
  createTrendChart('completedAppointmentsTrend', trends.completedAppointmentsTrend || [8,9,10,12,11,13,14,15,16,18,19,20], '#ffffff');
})();