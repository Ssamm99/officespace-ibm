<!DOCTYPE html>
<html lang="es" class="bg-slate-50">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Aplica el tema guardado antes de pintar (evita parpadeo) -->
  <script>try{if(localStorage.getItem('officespace_theme')==='dark')document.documentElement.classList.add('dark');}catch(e){}</script>
  <link rel="stylesheet" href="theme.css" />
  <script src="session.js"></script>
  <title>Analytics — OfficeSpace</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { 50:'#eef4ff',100:'#d9e6ff',200:'#b8d0ff',300:'#8bb2ff',400:'#5b8bff',500:'#3366ff',600:'#1f48db',700:'#1a3aad',800:'#1b3488',900:'#1c2f6b' } } } }
    };
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    body { font-family: 'Inter', system-ui, sans-serif; }
    .stat-card { transition: transform .2s ease, box-shadow .2s ease; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(31,72,219,.1); }
  </style>
  <script>
    const _u = JSON.parse(localStorage.getItem('officespace_user') || '{}');
    if (!localStorage.getItem('officespace_token') || _u.rol !== 'ADMINISTRADOR') {
        window.location.replace('login.php');
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

  <!-- HEADER -->
  <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-lg bg-brand-600 flex items-center justify-center text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
          </div>
          <span class="text-lg font-extrabold tracking-tight text-slate-900">OfficeSpace</span>
          <span class="rounded-md bg-violet-50 border border-violet-100 px-2 py-0.5 text-xs font-bold text-violet-700">ANALYTICS</span>
        </div>
        <div class="flex items-center gap-3">
          <a href="admin.php" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">← Panel Admin</a>
          <a href="index.php" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">Dashboard</a>
          <button data-theme-toggle class="theme-toggle-btn"></button>
          <div class="flex items-center gap-2 pl-2">
            <div class="h-9 w-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-bold" id="user-initials">AD</div>
            <div class="hidden sm:block leading-tight">
              <p class="text-sm font-semibold text-slate-800" id="user-email">Admin</p>
              <p class="text-xs text-brand-600 font-bold">ADMINISTRADOR</p>
            </div>
          </div>
          <button onclick="logout()" class="h-9 w-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-red-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- TÍTULO -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Analytics Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Métricas de uso y ocupación de espacios</p>
      </div>
      <button onclick="cargarAnalytics()" class="flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        Actualizar
      </button>
    </div>

    <!-- KPI CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="stat-card bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Reservas</p>
        <p class="text-3xl font-extrabold text-slate-900" id="kpi-total">—</p>
      </div>
      <div class="stat-card bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Activas</p>
        <p class="text-3xl font-extrabold text-emerald-600" id="kpi-activas">—</p>
      </div>
      <div class="stat-card bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Canceladas</p>
        <p class="text-3xl font-extrabold text-red-500" id="kpi-canceladas">—</p>
      </div>
      <div class="stat-card bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tasa Cancelación</p>
        <p class="text-3xl font-extrabold text-amber-500" id="kpi-tasa">—</p>
      </div>
      <div class="stat-card bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Espacios</p>
        <p class="text-3xl font-extrabold text-brand-600" id="kpi-espacios">—</p>
      </div>
      <div class="stat-card bg-white rounded-2xl border border-slate-200 shadow-sm p-4 text-center">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Usuarios</p>
        <p class="text-3xl font-extrabold text-violet-600" id="kpi-usuarios">—</p>
      </div>
    </div>

    <!-- FILA 1: Espacios más utilizados + Dona cancelaciones -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Barras — Espacios más utilizados -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 mb-1">Espacios más utilizados</h2>
        <p class="text-xs text-slate-400 mb-4">Total de reservas por espacio</p>
        <div style="position:relative; height:260px;">
          <canvas id="chart-espacios" role="img" aria-label="Gráfica de barras de reservas por espacio"></canvas>
        </div>
      </div>

      <!-- Dona — Distribución activas/canceladas -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
        <h2 class="text-base font-bold text-slate-900 mb-1">Distribución de reservas</h2>
        <p class="text-xs text-slate-400 mb-4">Activas vs Canceladas</p>
        <div class="flex-1 flex items-center justify-center">
          <div style="position:relative; width:200px; height:200px;">
            <canvas id="chart-dona" role="img" aria-label="Gráfica de dona con distribución de reservas activas y canceladas"></canvas>
          </div>
        </div>
        <div class="flex justify-center gap-6 mt-4">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-sm" style="background:#10b981;"></span>
            <span class="text-xs font-semibold text-slate-600">Activas</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-sm" style="background:#ef4444;"></span>
            <span class="text-xs font-semibold text-slate-600">Canceladas</span>
          </div>
        </div>
      </div>
    </div>

    <!-- FILA 2: Horarios pico + Días de la semana -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Línea — Horarios pico -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 mb-1">Horarios pico</h2>
        <p class="text-xs text-slate-400 mb-4">Reservas activas por hora de inicio (07:00 – 20:00)</p>
        <div style="position:relative; height:240px;">
          <canvas id="chart-horas" role="img" aria-label="Gráfica de línea de reservas por hora"></canvas>
        </div>
      </div>

      <!-- Barras — Días de la semana -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-900 mb-1">Actividad por día</h2>
        <p class="text-xs text-slate-400 mb-4">Reservas activas por día de la semana</p>
        <div style="position:relative; height:240px;">
          <canvas id="chart-dias" role="img" aria-label="Gráfica de barras de reservas por día de la semana"></canvas>
        </div>
      </div>
    </div>

  </div>

  <script>
    const userData = JSON.parse(localStorage.getItem('officespace_user') || '{}');
    const token    = localStorage.getItem('officespace_token');
    let charts     = {};

    // Colores de las gráficas según el tema activo (claro/oscuro)
    function isDark() { return document.documentElement.classList.contains('dark'); }
    function chartTheme() {
        return isDark()
            ? { text: '#cbd5e1', grid: 'rgba(148,163,184,0.18)' }
            : { text: '#475569', grid: '#f1f5f9' };
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('user-email').textContent    = userData.email || 'Admin';
        document.getElementById('user-initials').textContent = (userData.email || 'AD').substring(0, 2).toUpperCase();
        cargarAnalytics();

        // Redibuja las gráficas cuando se cambia el tema (claro/oscuro)
        new MutationObserver(() => {
            if (Object.keys(charts).length) cargarAnalytics();
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    });

    function destroyChart(id) {
        if (charts[id]) { charts[id].destroy(); delete charts[id]; }
    }

    async function cargarAnalytics() {
        try {
            const res    = await fetch('http://localhost:8002/get_analytics.php', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data   = await res.json();
            if (data.status !== 'success') throw new Error(data.message);

            const { resumen, espacios_uso, horarios_pico, dias_semana } = data;

            // Aplica los colores del tema actual a todas las gráficas
            const ct = chartTheme();
            Chart.defaults.color       = ct.text;
            Chart.defaults.borderColor = ct.grid;

            // KPIs
            document.getElementById('kpi-total').textContent      = resumen.total_reservas;
            document.getElementById('kpi-activas').textContent    = resumen.activas;
            document.getElementById('kpi-canceladas').textContent = resumen.canceladas;
            document.getElementById('kpi-tasa').textContent       = resumen.tasa_cancelacion + '%';
            document.getElementById('kpi-espacios').textContent   = resumen.total_espacios;
            document.getElementById('kpi-usuarios').textContent   = resumen.total_usuarios;

            // ── Chart 1: Barras — Espacios más utilizados ────────────────────
            destroyChart('espacios');
            charts['espacios'] = new Chart(document.getElementById('chart-espacios'), {
                type: 'bar',
                data: {
                    labels: espacios_uso.map(e => e.nombre.length > 16 ? e.nombre.substring(0,16)+'…' : e.nombre),
                    datasets: [
                        {
                            label: 'Activas',
                            data: espacios_uso.map(e => parseInt(e.activas)),
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                        },
                        {
                            label: 'Canceladas',
                            data: espacios_uso.map(e => parseInt(e.canceladas)),
                            backgroundColor: '#fca5a5',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
                    scales: {
                        x: { stacked: true, ticks: { font: { size: 11 } }, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: ct.grid } }
                    }
                }
            });

            // ── Chart 2: Dona — Activas vs Canceladas ───────────────────────
            destroyChart('dona');
            charts['dona'] = new Chart(document.getElementById('chart-dona'), {
                type: 'doughnut',
                data: {
                    labels: ['Activas', 'Canceladas'],
                    datasets: [{
                        data: [resumen.activas || 1, resumen.canceladas || 0],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });

            // ── Chart 3: Línea — Horarios pico ──────────────────────────────
            destroyChart('horas');
            charts['horas'] = new Chart(document.getElementById('chart-horas'), {
                type: 'line',
                data: {
                    labels: horarios_pico.map(h => h.hora),
                    datasets: [{
                        label: 'Reservas',
                        data: horarios_pico.map(h => h.total),
                        borderColor: '#1f48db',
                        backgroundColor: 'rgba(31,72,219,0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#1f48db',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 11 } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: ct.grid } }
                    }
                }
            });

            // ── Chart 4: Barras — Días de la semana ─────────────────────────
            destroyChart('dias');
            charts['dias'] = new Chart(document.getElementById('chart-dias'), {
                type: 'bar',
                data: {
                    labels: dias_semana.map(d => d.dia),
                    datasets: [{
                        label: 'Reservas',
                        data: dias_semana.map(d => d.total),
                        backgroundColor: dias_semana.map((d, i) =>
                            i === 2 ? '#1f48db' : 'rgba(31,72,219,0.25)'
                        ),
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false },
                        tooltip: { callbacks: { footer: () => 'Miércoles es el día pico' } }
                    },
                    scales: {
                        x: { ticks: { font: { size: 13, weight: '600' } }, grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: ct.grid } }
                    }
                }
            });

        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error al cargar métricas', text: err.message });
        }
    }

    function logout() {
        localStorage.removeItem('officespace_token');
        localStorage.removeItem('officespace_user');
        window.location.href = 'login.php';
    }
  </script>
  <script src="theme.js"></script>
</body>
</html>