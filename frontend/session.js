/* session.js — Manejo de sesión expirada o token inválido.
   Envuelve window.fetch: si cualquier llamada a los microservicios responde 401
   (token ausente, inválido o expirado), limpia la sesión y redirige al login,
   en lugar de dejar la página cargada con un token muerto. */
(function () {
  const API_RE = /localhost:800[1-3]/;      // catalog(8001), booking(8002), auth(8003)
  const nativeFetch = window.fetch.bind(window);

  window.fetch = async function (input, init) {
    const res = await nativeFetch(input, init);
    try {
      const url = typeof input === 'string' ? input : (input && input.url) || '';
      // No reaccionar al propio login (que puede devolver 401 por credenciales).
      const esLogin = /login\.php/.test(url);
      if (res.status === 401 && API_RE.test(url) && !esLogin) {
        localStorage.removeItem('officespace_token');
        localStorage.removeItem('officespace_user');
        if (!location.pathname.endsWith('login.php')) {
          location.replace('login.php');
        }
      }
    } catch (e) { /* nunca romper la petición original por el interceptor */ }
    return res;
  };
})();
