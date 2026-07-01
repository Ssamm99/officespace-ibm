<!DOCTYPE html>
<html lang="es" class="bg-slate-50">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Aplica el tema guardado antes de pintar (evita parpadeo) -->
  <script>try{if(localStorage.getItem('officespace_theme')==='dark')document.documentElement.classList.add('dark');}catch(e){}</script>
  <link rel="stylesheet" href="theme.css" />
  <script src="session.js"></script>
  <title>Admin — OfficeSpace</title>
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
    // Protección de ruta: solo ADMINISTRADOR
    const _u = JSON.parse(localStorage.getItem('officespace_user') || '{}');
    if (!localStorage.getItem('officespace_token') || _u.rol !== 'ADMINISTRADOR') {
        window.location.replace('login.php');
    }
  </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

  <!-- HEADER -->
  <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-lg bg-brand-600 flex items-center justify-center text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
          </div>
          <span class="text-lg font-extrabold tracking-tight text-slate-900">OfficeSpace</span>
          <span class="rounded-md bg-brand-50 border border-brand-100 px-2 py-0.5 text-xs font-bold text-brand-700">ADMIN</span>
        </div>
        <div class="flex items-center gap-3">
          <a href="index.php" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
            ← Volver al Dashboard
          </a>
          <button data-theme-toggle class="theme-toggle-btn"></button>
          <div class="flex items-center gap-2 pl-2">
            <div class="h-9 w-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-bold" id="user-initials">AD</div>
            <div class="hidden sm:block leading-tight">
              <p class="text-sm font-semibold text-slate-800" id="user-email">Admin</p>
              <p class="text-xs text-brand-600 font-bold">ADMINISTRADOR</p>
            </div>
          </div>
          <button onclick="logout()" title="Sign out" class="h-9 w-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-red-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- DASHBOARD DE OCUPACIÓN -->
    <section>
      <h2 class="text-xl font-bold text-slate-900 mb-4">Dashboard de Ocupación — Hoy</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Espacios</p>
          <p class="text-3xl font-extrabold text-slate-900" id="stat-total">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Ocupados Hoy</p>
          <p class="text-3xl font-extrabold text-brand-600" id="stat-ocupados">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Disponibles Hoy</p>
          <p class="text-3xl font-extrabold text-emerald-600" id="stat-disponibles">—</p>
        </div>
      </div>

      <!-- Tabla de reservas de hoy -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
          <h3 class="text-sm font-bold text-slate-700">Reservas activas hoy</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Espacio</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Horario</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asistentes</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acción</th>
              </tr>
            </thead>
            <tbody id="tabla-reservas-hoy" class="divide-y divide-slate-100">
              <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- GESTIÓN DE ESPACIOS -->
    <section>
      <div class="flex items-center gap-3">
  <a href="analytics.php" class="rounded-lg border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 hover:bg-violet-100 transition">
    📊 Ver Analytics
  </a>
  <button onclick="abrirModalEspacio()" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 transition">
    + Nuevo Espacio
  </button>
</div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
              <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Capacidad</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Piso</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Recursos</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
              </tr>
            </thead>
            <tbody id="tabla-espacios" class="divide-y divide-slate-100">
              <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">Cargando espacios...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <!-- MODAL CREAR / EDITAR ESPACIO -->
  <div id="modal-espacio" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
        <h3 class="text-lg font-bold text-slate-900" id="modal-espacio-titulo">Nuevo Espacio</h3>
        <button onclick="cerrarModalEspacio()" class="text-slate-400 hover:text-slate-600 transition">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="p-6 space-y-4">
        <input type="hidden" id="espacio-id">
        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">Nombre</label>
            <input type="text" id="espacio-nombre" placeholder="Ej. Sala Creativa"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">Tipo</label>
            <select id="espacio-tipo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500 bg-white">
              <option value="SALA">Sala de Juntas</option>
              <option value="DESK">Escritorio (DESK)</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">Capacidad</label>
            <input type="number" id="espacio-capacidad" min="1" placeholder="Ej. 8"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">Piso</label>
            <input type="text" id="espacio-piso" placeholder="Ej. Piso 2"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">Estado</label>
            <select id="espacio-activo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500 bg-white">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1">Recursos</label>
            <input type="text" id="espacio-recursos" placeholder="Ej. Proyector, AC, Pizarrón"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-brand-500" />
          </div>
        </div>
        <div class="pt-2 flex justify-end gap-3">
          <button onclick="cerrarModalEspacio()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Cancelar</button>
          <button onclick="guardarEspacio()" id="btn-guardar-espacio" class="bg-brand-600 text-white px-4 py-2 text-sm font-semibold rounded-lg hover:bg-brand-700 transition">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const userData = JSON.parse(localStorage.getItem('officespace_user') || '{}');
    const token    = localStorage.getItem('officespace_token');

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('user-email').textContent    = userData.email || 'Admin';
        document.getElementById('user-initials').textContent = (userData.email || 'AD').substring(0, 2).toUpperCase();
        cargarDashboard();
        cargarEspacios();
    });

    // ─── DASHBOARD ───────────────────────────────────────────────
    async function cargarDashboard() {
        try {
            const res    = await fetch('http://localhost:8002/get_reservas_hoy.php', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const result = await res.json();

            if (result.status === 'success') {
                const reservas = result.data;
                const totalEspacios = result.total_espacios;

                document.getElementById('stat-total').textContent       = totalEspacios;
                document.getElementById('stat-ocupados').textContent    = reservas.length;
                document.getElementById('stat-disponibles').textContent = totalEspacios - reservas.length;

                const tbody = document.getElementById('tabla-reservas-hoy');
                if (reservas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No hay reservas activas hoy.</td></tr>';
                    return;
                }
                tbody.innerHTML = '';
                reservas.forEach(r => {
                    tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-800">${r.nombre_espacio}</td>
                        <td class="px-5 py-3.5 text-slate-600">${r.email_usuario}</td>
                        <td class="px-5 py-3.5 text-slate-600">${r.hora_inicio.substring(0,5)} – ${r.hora_fin.substring(0,5)}</td>
                        <td class="px-5 py-3.5 text-slate-600">${r.asistentes}</td>
                        <td class="px-5 py-3.5">
                            <button onclick="cancelarReservaAdmin(${r.id_reserva})"
                                class="text-xs font-semibold text-red-600 hover:text-red-800 transition">
                                Cancelar
                            </button>
                        </td>
                    </tr>`;
                });
            }
        } catch (e) {
            document.getElementById('tabla-reservas-hoy').innerHTML =
                '<tr><td colspan="5" class="px-5 py-8 text-center text-red-400">Error al cargar reservas.</td></tr>';
        }
    }

    async function cancelarReservaAdmin(idReserva) {
        const confirm = await Swal.fire({
            icon: 'warning', title: '¿Cancelar esta reserva?',
            text: 'Se liberará el espacio inmediatamente.',
            showCancelButton: true, confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Volver', confirmButtonColor: '#dc2626'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('http://localhost:8002/cancel_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ id_reserva: idReserva })
            });
            const result = await res.json();
            if (res.ok) {
                Swal.fire({ icon: 'success', title: 'Reserva cancelada', timer: 1500, showConfirmButton: false });
                cargarDashboard();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        }
    }

    // ─── ESPACIOS ────────────────────────────────────────────────
    async function cargarEspacios() {
        const tbody = document.getElementById('tabla-espacios');
        try {
            const res    = await fetch('http://localhost:8001/get_spaces.php?mostrar_inactivos=1', {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const result = await res.json();

            if (result.status === 'success' && result.data.length > 0) {
                tbody.innerHTML = '';
                result.data.forEach(e => {
                    const activo = e.activo == 1;
                    tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3.5 font-semibold text-slate-800">${e.nombre}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold
                                ${e.tipo === 'SALA' ? 'bg-brand-50 text-brand-700 border-brand-100' : 'bg-violet-50 text-violet-700 border-violet-100'}">
                                ${e.tipo}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">${e.capacidad} personas</td>
                        <td class="px-5 py-3.5 text-slate-600">${e.piso}</td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs max-w-[180px] truncate">${e.recursos || '—'}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                                ${activo ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'}">
                                ${activo ? 'Activo' : 'Inactivo'}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 flex items-center gap-3">
                            <button onclick='editarEspacio(${JSON.stringify(e)})'
                                class="text-xs font-semibold text-brand-600 hover:text-brand-800 transition">Editar</button>
                            <button onclick="eliminarEspacio(${e.id_espacio}, '${e.nombre}')"
                                class="text-xs font-semibold text-red-500 hover:text-red-700 transition">Eliminar</button>
                        </td>
                    </tr>`;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">No hay espacios registrados.</td></tr>';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-red-400">Error al cargar espacios.</td></tr>';
        }
    }

    function abrirModalEspacio() {
        document.getElementById('modal-espacio-titulo').textContent = 'Nuevo Espacio';
        document.getElementById('espacio-id').value       = '';
        document.getElementById('espacio-nombre').value   = '';
        document.getElementById('espacio-tipo').value     = 'SALA';
        document.getElementById('espacio-capacidad').value = '';
        document.getElementById('espacio-piso').value     = '';
        document.getElementById('espacio-recursos').value = '';
        document.getElementById('espacio-activo').value   = '1';
        document.getElementById('modal-espacio').classList.remove('hidden');
    }

    function editarEspacio(e) {
        document.getElementById('modal-espacio-titulo').textContent = 'Editar Espacio';
        document.getElementById('espacio-id').value        = e.id_espacio;
        document.getElementById('espacio-nombre').value    = e.nombre;
        document.getElementById('espacio-tipo').value      = e.tipo;
        document.getElementById('espacio-capacidad').value = e.capacidad;
        document.getElementById('espacio-piso').value      = e.piso;
        document.getElementById('espacio-recursos').value  = e.recursos || '';
        document.getElementById('espacio-activo').value    = e.activo;
        document.getElementById('modal-espacio').classList.remove('hidden');
    }

    function cerrarModalEspacio() {
        document.getElementById('modal-espacio').classList.add('hidden');
    }

    async function guardarEspacio() {
        const id        = document.getElementById('espacio-id').value;
        const nombre    = document.getElementById('espacio-nombre').value.trim();
        const tipo      = document.getElementById('espacio-tipo').value;
        const capacidad = document.getElementById('espacio-capacidad').value;
        const piso      = document.getElementById('espacio-piso').value.trim();
        const recursos  = document.getElementById('espacio-recursos').value.trim();
        const activo    = document.getElementById('espacio-activo').value;

        if (!nombre || !capacidad || !piso) {
            Swal.fire({ icon: 'warning', title: 'Campos requeridos', text: 'Nombre, capacidad y piso son obligatorios.' });
            return;
        }

        const btn = document.getElementById('btn-guardar-espacio');
        btn.textContent = 'Guardando...';
        btn.disabled    = true;

        const payload = { nombre, tipo, capacidad: parseInt(capacidad), piso, recursos, activo: parseInt(activo) };
        if (id) payload.id_espacio = parseInt(id);

        try {
            const url = id
                ? 'http://localhost:8001/update_space.php'
                : 'http://localhost:8001/create_space.php';

            const res    = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (res.ok) {
                Swal.fire({ icon: 'success', title: id ? 'Espacio actualizado' : 'Espacio creado', timer: 1500, showConfirmButton: false });
                cerrarModalEspacio();
                cargarEspacios();
                cargarDashboard();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: result.message });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error de conexión' });
        } finally {
            btn.textContent = 'Guardar';
            btn.disabled    = false;
        }
    }

    async function eliminarEspacio(idEspacio, nombre) {
        const confirm = await Swal.fire({
            icon: 'warning', title: `¿Eliminar "${nombre}"?`,
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true, confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#dc2626'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res    = await fetch('http://localhost:8001/delete_space.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                body: JSON.stringify({ id_espacio: idEspacio })
            });
            const result = await res.json();
            if (res.ok) {
                Swal.fire({ icon: 'success', title: 'Espacio eliminado', timer: 1500, showConfirmButton: false });
                cargarEspacios();
                cargarDashboard();
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
  <script src="theme.js"></script>
</body>
</html>