// Drag-and-drop wiring + client-side row count preview.
// Server is the source of truth for validation and the 6-row demo cap.

const BulkImport = (function () {
  function wireDropZone(zoneEl, inputEl, onChange) {
    if (!zoneEl || !inputEl) return;
    ['dragenter','dragover'].forEach(evt => zoneEl.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation(); zoneEl.classList.add('border-primary');
    }));
    ['dragleave','drop'].forEach(evt => zoneEl.addEventListener(evt, e => {
      e.preventDefault(); e.stopPropagation(); zoneEl.classList.remove('border-primary');
    }));
    zoneEl.addEventListener('drop', e => {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        inputEl.files = e.dataTransfer.files;
        if (onChange) onChange();
      }
    });
    zoneEl.addEventListener('click', () => inputEl.click());
    inputEl.addEventListener('change', () => { if (onChange) onChange(); });
  }
  return { wireDropZone };
})();
