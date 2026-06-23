<!DOCTYPE html>
<html lang="en" class="bg-slate-50">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — OfficeSpace</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { brand: { 50: '#eef4ff', 100: '#d9e6ff', 200: '#b8d0ff', 300: '#8bb2ff', 400: '#5b8bff', 500: '#3366ff', 600: '#1f48db', 700: '#1a3aad', 800: '#1b3488', 900: '#1c2f6b', } } } }
    };
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style> body { font-family: 'Inter', system-ui, sans-serif; } </style>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

  <section class="min-h-screen w-full flex items-center justify-center px-4 bg-gradient-to-b from-slate-100 to-slate-50">
    <div class="w-full max-w-md">
      <div class="flex items-center justify-center gap-2 mb-8">
        <div class="h-10 w-10 rounded-xl bg-brand-600 flex items-center justify-center text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/></svg>
        </div>
        <span class="text-xl font-extrabold tracking-tight text-slate-900">OfficeSpace</span>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="text-sm text-slate-500 mt-1">Sign in to manage your hybrid workspace bookings.</p>

        <form class="mt-7 space-y-5" onsubmit="realizarLogin(event)">
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Work email</label>
            <input id="email" type="email" required autocomplete="off"
              class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition" placeholder="you@company.com" />
          </div>
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            </div>
            <input id="password" type="password" required 
              class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition" placeholder="••••••••" />
          </div>

          <button type="submit" id="btn-login"
            class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus:ring-2 focus:ring-brand-200 transition">
            Sign in
          </button>
        </form>
      </div>
    </div>
  </section>

  <script>
    // Si ya hay un token guardado, mandarlo directo al dashboard
    if (localStorage.getItem('officespace_token')) {
        window.location.href = 'index.php';
    }

    async function realizarLogin(e) {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const btn = document.getElementById('btn-login');

      btn.textContent = "Verificando...";
      btn.disabled = true;

      try {
        // Petición hacia tu Microservicio de Autenticación (Puerto 8003)
        const response = await fetch('http://localhost:8003/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: email, password: password })
        });

        const data = await response.json();

        if (data.status === 'success') {
          // Guardamos el token JWT y los datos en el navegador
          localStorage.setItem('officespace_token', data.token);
          localStorage.setItem('officespace_user', JSON.stringify(data.user));
          
          window.location.href = 'index.php';
        } else {
          Swal.fire({ icon: 'error', title: 'Acceso denegado', text: data.message });
          btn.textContent = "Sign in";
          btn.disabled = false;
        }
      } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error de Red', text: 'No se pudo conectar con el servidor de autenticación.' });
        btn.textContent = "Sign in";
        btn.disabled = false;
      }
    }
  </script>
</body>
</html>