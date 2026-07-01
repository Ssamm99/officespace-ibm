/* theme.js — Gestión del tema claro/oscuro compartido por todas las páginas.
   - Guarda la preferencia en localStorage ('officespace_theme').
   - Se sincroniza entre pestañas abiertas mediante el evento 'storage'.
   - Cualquier botón con el atributo [data-theme-toggle] funciona como interruptor. */
(function () {
  const KEY = 'officespace_theme';

  const ICON_MOON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
  const ICON_SUN  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';

  function current() {
    try { return localStorage.getItem(KEY) === 'dark' ? 'dark' : 'light'; }
    catch (e) { return 'light'; }
  }

  function apply(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      // En modo oscuro mostramos el sol (para volver a claro), y viceversa.
      btn.innerHTML = theme === 'dark' ? ICON_SUN : ICON_MOON;
      btn.setAttribute('aria-label', theme === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro');
      btn.setAttribute('title', theme === 'dark' ? 'Modo claro' : 'Modo oscuro');
    });
  }

  // Interruptor global, invocable desde onclick o [data-theme-toggle].
  window.toggleTheme = function () {
    const next = current() === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem(KEY, next); } catch (e) {}
    apply(next);
  };

  // Sincroniza otras pestañas abiertas cuando cambia la preferencia.
  window.addEventListener('storage', function (e) {
    if (e.key === KEY) apply(current());
  });

  // Delegación: cualquier elemento [data-theme-toggle] alterna el tema.
  document.addEventListener('click', function (e) {
    const t = e.target.closest('[data-theme-toggle]');
    if (t) { e.preventDefault(); window.toggleTheme(); }
  });

  document.addEventListener('DOMContentLoaded', function () { apply(current()); });

  // Aplica de inmediato (la clase ya pudo ponerse en el <head> para evitar parpadeo).
  apply(current());
})();
