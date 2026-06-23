<!DOCTYPE html>
<html lang="es" class="bg-slate-50">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mi Perfil — OfficeSpace</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { 50: '#eef4ff', 100: '#d9e6ff', 200: '#b8d0ff', 300: '#8bb2ff', 400: '#5b8bff', 500: '#3366ff', 600: '#1f48db', 700: '#1a3aad', 800: '#1b3488', 900: '#1c2f6b' } } } }
    };
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style> body { font-family: 'Inter', system-ui, sans-serif; } </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    if (!localStorage.getItem('officespace_token')) {
        window.location.replace('login.php');
    }
  </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

  <!-- HEADER -->
  <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
          <div class="h-9 w-9 rounded-lg bg-brand-600 flex items-center justify-center text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
          </div>
          <span class="text-lg font-extrabold tracking-tight text-slate-900">OfficeSpace</span>
        </div>
        <div class="flex items-center gap-3">
          <a href="index.php" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
            ← Volver al Dashboard
          </a>
          <a id="btn-admin" href="admin.php" class="hidden rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition">Panel Admin</a>
          <div class="flex items-center gap-2.5 pl-2">
            <div class="h-9 w-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-bold" id="user-initials">--</div>
            <div class="hidden sm:block leading-tight">
              <p class="text-sm font-semibold text-slate-800" id="user-email">Cargando...</p>
              <p class="text-xs text-brand-600 font-bold" id="user-role">ROL</p>
            </div>
          </div>
          <button onclick="logout()" title="Sign out" class="h-9 w-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-red-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- TARJETA DE PERFIL -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col sm:flex-row items-center sm:items-start gap-6">
      <div class="h-20 w-20 rounded-2xl bg-brand-600 flex items-center justify-center text-white text-3xl font-extrabold shrink-0" id="perfil-avatar">--</div>
      <div class="flex-1 text-center sm:text-left">
        <h1 class="text-2xl font-bold text-slate-900" id="perfil-email">Cargando...</h1>
        <div class="flex items-center justify-center sm:justify-start gap-2 mt-1">
          <span class="inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-bold" id="perfil-rol-badge">—</span>
        </div>
        <p class="text-sm text-slate-400 mt-3">Corporativo Alpha · OfficeSpace</p>
      </div>
      <!-- Estadísticas rápidas -->
      <div class="flex gap-6 shrink-0">
        <div class="text-center">
          <p class="text-2xl font-extrabold text-brand-600" id="stat-activas">—</p>
          <p class="text-xs text-slate-500 mt-0.5">Activas</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-extrabold text-slate-700" id="stat-total-reservas">—</p>
          <p class="text-xs text-slate-500 mt-0.5">Total</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-extrabold text-red-500" id="stat-canceladas">—</p>
          <p class="text-xs text-slate-500 mt-0.5">Canceladas</p>
        </div>
      </div>
    </div>

    <!-- FILTRO DE HISTORIAL -->
    <div class="flex flex-wrap items-center gap-2">
      <span class="text-sm font-semibold text-slate-600">Filtrar:</span>
      <button onclick="filtrarReservas('todas')" id="tab-todas"
        class="tab-btn rounded-full px-4 py-1.5 text-xs font-semibold transition border border-brand-600 bg-brand-600 text-white">
        Todas
      </button>
      <button onclick="filtrarReservas('Activa')" id="tab-activa"
        class="tab-btn rounded-full px-4 py-1.5 text-xs font-semibold transition border border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700">
        Activas
      </button>
      <button onclick="filtrarReservas('Cancelada')" id="tab-cancelada"
        class="tab-btn rounded-full px-4 py-1.5 text-xs font-semibold transition border border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700">
        Canceladas
      </button>
    </div>

    <!-- HISTORIAL DE RESERVAS -->
    <div>
      <h2 class="text-lg font-bold text-slate-900 mb-4">Historial de Reservas</h2>
      <div id="historial-lista" class="space-y-3">
        <p class="text-slate-400 text-center py-10">Cargando historial...</p>
      </div>
    </div>

  </div>

  <script>
    const userData = JSON.parse(localStorage.getItem('officespace_user') || '{}');
    const token    = localStorage.getItem('officespace_token');
    let todasLasReservas = [];

    document.addEventListener('DOMContentLoaded', () => {
        // Datos del header
        document.getElementById('user-email').textContent    = userData.email || '';
        document.getElementById('user-role').textContent     = userData.rol   || '';
        document.getElementById('user-initials').textContent = (userData.email || '--').substring(0, 2).toUpperCase();
        if (userData.rol === 'ADMINISTRADOR') {
            document.getElementById('btn-admin').classList.remove('hidden');
        }

        // Tarjeta de perfil
        document.getElementById('perfil-avatar').textContent = (userData.email || '--').substring(0, 2).toUpperCase();
        document.getElementById('perfil-email').textContent  = userData.email || '—';

        const badge = document.getElementById('perfil-rol-badge');
        if (userData.rol === 'ADMINISTRADOR') {
            badge.textContent = '⚙️ Administrador';
            badge.classList.add('bg-brand-50', 'text-brand-700', 'border-brand-200');
        } else {
            badge.textContent = '👤 Colaborador';
            badge.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
        }

        cargarHistorial();
    });

    async function cargarHistorial() {
        try {
            const response = await fetch(`http://localhost:8002/get_all_user_bookings.php`, {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const result = await response.json();

            if (result.status === 'success') {
                todasLasReservas = result.data;
                actualizarEstadisticas(todasLasReservas);
                renderizarReservas(todasLasReservas);
            } else {
                document.getElementById('historial-lista').innerHTML =
                    '<p class="text-slate-400 text-center py-10">No se encontraron reservas.</p>';
            }
        } catch (e) {
            document.getElementById('historial-lista').innerHTML =
                '<p class="text-red-400 text-center py-10">Error al cargar el historial.</p>';
        }
    }

    function actualizarEstadisticas(reservas) {
        const activas    = reservas.filter(r => r.estatus === 'Activa').length;
        const canceladas = reservas.filter(r => r.estatus === 'Cancelada').length;
        document.getElementById('stat-activas').textContent        = activas;
        document.getElementById('stat-total-reservas').textContent = reservas.length;
        document.getElementById('stat-canceladas').textContent     = canceladas;
    }

    function renderizarReservas(reservas) {
        const lista = document.getElementById('historial-lista');
        if (reservas.length === 0) {
            lista.innerHTML = `
                <div class="text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-slate-400 font-semibold">No hay reservas en esta categoría.</p>
                </div>`;
            return;
        }

        lista.innerHTML = '';
        reservas.forEach(r => {
            const esActiva   = r.estatus === 'Activa';
            const isSala     = r.tipo === 'SALA';
            const colorDot   = isSala ? 'bg-brand-500' : 'bg-violet-500';
            const colorBadge = esActiva
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-slate-100 text-slate-500 border-slate-200';

            lista.innerHTML += `
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <!-- Dot tipo -->
                <div class="h-11 w-11 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                    <span class="h-3 w-3 rounded-full ${colorDot}"></span>
                </div>

                <!-- Info principal -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <p class="text-sm font-bold text-slate-900">${r.nombre}</p>
                        <span class="text-[11px] font-semibold text-slate-400">${r.tipo} · ${r.piso}</span>
                    </div>
                    <p class="text-xs text-slate-500">
                        📅 ${r.fecha} &nbsp;·&nbsp; 🕐 ${r.hora_inicio.substring(0,5)} – ${r.hora_fin.substring(0,5)} &nbsp;·&nbsp; 👥 ${r.asistentes} asistentes
                    </p>
                    ${r.notas ? `
                    <div class="mt-2 flex items-start gap-1.5">
                        <span class="text-xs">📝</span>
                        <p class="text-xs text-slate-500 italic">${r.notas}</p>
                    </div>` : ''}
                </div>

                <!-- Badge estatus + acción -->
                <div class="flex items-center gap-3 shrink-0">
                    <span class="inline-flex items-center rounded-full border px-3 py-0.5 text-[11px] font-semibold ${colorBadge}">
                        ${r.estatus}
                    </span>
                    ${esActiva ? `
                    <button onclick="cancelarReserva(${r.id_reserva})"
                        class="text-xs font-semibold text-red-500 hover:text-red-700 transition">
                        Cancelar
                    </button>` : ''}
                </div>
            </div>`;
        });
    }

    function filtrarReservas(estatus) {
        // Actualizar tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.className = 'tab-btn rounded-full px-4 py-1.5 text-xs font-semibold transition border border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700';
        });
        const tabActivo = estatus === 'todas' ? 'tab-todas' : estatus === 'Activa' ? 'tab-activa' : 'tab-cancelada';
        document.getElementById(tabActivo).className =
            'tab-btn rounded-full px-4 py-1.5 text-xs font-semibold transition border border-brand-600 bg-brand-600 text-white';

        const filtradas = estatus === 'todas'
            ? todasLasReservas
            : todasLasReservas.filter(r => r.estatus === estatus);
        renderizarReservas(filtradas);
    }

    async function cancelarReserva(idReserva) {
        const confirm = await Swal.fire({
            icon: 'warning', title: '¿Cancelar reserva?',
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true, confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Volver', confirmButtonColor: '#dc2626'
        });
        if (!confirm.isConfirmed) return;

        try {
            const response = await fetch('http://localhost:8002/cancel_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ id_reserva: idReserva })
            });
            const result = await response.json();
            if (response.ok) {
                Swal.fire({ icon: 'success', title: 'Reserva cancelada', timer: 1500, showConfirmButton: false });
                cargarHistorial();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        }
    }

    function logout() {
        localStorage.removeItem('officespace_token');
        localStorage.removeItem('officespace_user');
        window.location.href = 'login.php';
    }
  </script>
</body>
</html>