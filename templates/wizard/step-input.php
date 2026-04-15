<?php $current = 1; include __DIR__ . '/_progress.php'; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <h1 class="h3 mb-3">Enter your EAN-13 or GTIN-14</h1>
    <p class="text-muted">Type the barcode digits from your product. We'll detect the format automatically and verify the check digit.</p>

    <div class="mb-3">
      <label for="base-id" class="form-label fw-semibold">Base barcode</label>
      <input type="text" inputmode="numeric" autocomplete="off" id="base-id" class="form-control form-control-lg" placeholder="13 or 14 digits" maxlength="14" aria-describedby="base-id-help base-id-feedback">
      <div id="base-id-help" class="form-text">Enter 13 digits for an EAN-13 or 14 digits for a GTIN-14.</div>
      <div id="base-id-feedback" class="mt-2" role="status" aria-live="polite"></div>
    </div>

    <div id="derive-block" class="card mb-3 d-none">
      <div class="card-body">
        <h2 class="h6">Derive a GTIN-14 (optional)</h2>
        <p class="small text-muted mb-2">Add a packaging indicator to convert your EAN-13 into a GTIN-14 for cases or pallets. We'll recalculate the check digit.</p>
        <div class="row g-2 align-items-end">
          <div class="col-sm-4">
            <label for="pi" class="form-label small">Packaging indicator</label>
            <select id="pi" class="form-select">
              <option value="">— none —</option>
              <?php for ($i=0; $i<=9; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-sm-8">
            <div class="form-text">Derived GTIN-14: <code id="derived-gtin14">—</code></div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between">
      <a href="<?= base_url() ?>" class="btn btn-link">Cancel</a>
      <button id="next-btn" class="btn btn-primary" disabled>Next: Choose data to add &raquo;</button>
    </div>
  </div>
</div>

<script>
(function () {
  const input = document.getElementById('base-id');
  const fb = document.getElementById('base-id-feedback');
  const help = document.getElementById('base-id-help');
  const next = document.getElementById('next-btn');
  const deriveBlock = document.getElementById('derive-block');
  const piSelect = document.getElementById('pi');
  const derivedOut = document.getElementById('derived-gtin14');

  let state = { base: '', format: '', gtin14: '', pi: '' };

  function showFeedback(text, ok) {
    fb.textContent = '';
    if (text) {
      const span = document.createElement('span');
      span.textContent = text;
      span.className = ok === true ? 'text-success' : (ok === false ? 'text-danger' : 'text-muted');
      fb.appendChild(span);
    }
    input.classList.toggle('field-ok', ok === true);
    input.classList.toggle('field-err', ok === false);
  }
  function setNext(enabled) { next.disabled = !enabled; }

  function evaluate() {
    const v = input.value.trim();
    deriveBlock.classList.add('d-none');
    state = { base: v, format: '', gtin14: '', pi: '' };

    if (v.length === 0) { showFeedback('', null); setNext(false); return; }
    if (v.length === 13) {
      help.textContent = 'Detected EAN-13.';
      const r = GS1.validateEan13(v);
      if (r.ok) { showFeedback('\u2713 Valid EAN-13 check digit.', true); state.format = 'ean13'; deriveBlock.classList.remove('d-none'); setNext(true); }
      else { showFeedback('\u2715 ' + r.error, false); setNext(false); }
    } else if (v.length === 14) {
      help.textContent = 'Detected GTIN-14.';
      const r = GS1.validateGtin14(v);
      if (r.ok) { showFeedback('\u2713 Valid GTIN-14 check digit.', true); state.format = 'gtin14'; state.gtin14 = v; setNext(true); }
      else { showFeedback('\u2715 ' + r.error, false); setNext(false); }
    } else {
      help.textContent = 'Enter 13 digits for an EAN-13 or 14 digits for a GTIN-14.';
      showFeedback('Need ' + Math.max(0, 13 - v.length) + ' more characters\u2026', null);
      setNext(false);
    }
  }

  function evaluatePi() {
    if (state.format !== 'ean13') return;
    const pi = piSelect.value;
    if (pi === '') { derivedOut.textContent = '—'; state.gtin14 = ''; state.pi = ''; return; }
    const r = GS1.deriveGtin14(state.base, pi);
    if (r.ok) { derivedOut.textContent = r.gtin14; state.gtin14 = r.gtin14; state.pi = pi; }
    else { derivedOut.textContent = '(error: ' + r.error + ')'; state.gtin14 = ''; }
  }

  input.addEventListener('input', () => { input.value = input.value.replace(/\D/g, ''); evaluate(); });
  piSelect.addEventListener('change', evaluatePi);

  next.addEventListener('click', (e) => {
    e.preventDefault();
    if (state.format === 'ean13' && !state.gtin14) {
      const r = GS1.deriveGtin14(state.base, '0');
      if (r.ok) { state.gtin14 = r.gtin14; state.pi = '0'; }
    }
    Wizard.reset();
    Wizard.patch({ base: state.base, format: state.format, pi: state.pi, gtin14: state.gtin14 });
    window.location.href = '<?= base_url() ?>wizard/select-ai';
  });

  const saved = Wizard.load();
  if (saved.base) { input.value = saved.base; evaluate(); if (saved.pi !== '' && saved.pi !== undefined) { piSelect.value = saved.pi; evaluatePi(); } }
})();
</script>
