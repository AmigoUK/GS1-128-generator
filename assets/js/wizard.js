// Wizard-wide helpers: state in sessionStorage, per-step validation hooks.
// Step-specific JS lives inline in each template to keep the surface small.

const Wizard = (function () {
  const KEY = 'gs1.wizard.state';

  function load() {
    try { return JSON.parse(sessionStorage.getItem(KEY)) || {}; }
    catch (e) { return {}; }
  }
  function save(state) { sessionStorage.setItem(KEY, JSON.stringify(state)); }
  function reset() { sessionStorage.removeItem(KEY); }
  function patch(partial) { const s = load(); Object.assign(s, partial); save(s); return s; }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  return { load, save, reset, patch, escapeHtml };
})();
