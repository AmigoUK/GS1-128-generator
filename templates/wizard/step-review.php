<?php
$current = 4; include __DIR__ . '/_progress.php';
$defs = ai_definitions();
?>

<h1 class="h3 mb-3">Review your barcode</h1>
<p class="text-muted">Check everything looks right. Use the Edit links to change a field. The preview updates live.</p>

<div class="row g-4">
  <div class="col-lg-7">
    <table class="table table-sm align-middle" id="review-table"></table>
    <div id="review-warnings" class="alert alert-warning d-none" role="alert"></div>

    <div class="card mt-3">
      <div class="card-body">
        <h2 class="h6">Human-readable</h2>
        <code id="hri-string" class="machine-string"></code>
        <h2 class="h6 mt-3">Machine-readable</h2>
        <code id="machine-string" class="machine-string"></code>
        <p class="small text-muted mt-2 mb-0"><code>&lt;FNC1&gt;</code> markers represent the GS1 separator (ASCII 0xF1) inserted between variable-length AIs.</p>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="barcode-preview">
      <canvas id="preview-canvas" aria-label="GS1-128 barcode preview"></canvas>
      <div id="preview-error" class="text-danger small mt-2"></div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mt-4">
  <a href="<?= base_url() ?>wizard/select-ai" class="btn btn-link">&laquo; Back to selection</a>
  <a href="<?= base_url() ?>wizard/export" class="btn btn-primary" id="continue-btn">Continue to export &raquo;</a>
</div>

<script id="ai-defs" type="application/json"><?= json_encode($defs, JSON_UNESCAPED_SLASHES) ?></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const defs = JSON.parse(document.getElementById('ai-defs').textContent);
  const state = Wizard.load();
  if (!state.gtin14) { window.location.href = '<?= base_url() ?>wizard/input'; return; }

  function resolve() {
    const out = [['01', state.gtin14]];
    const errors = [];
    for (const code of (state.selected_ais || [])) {
      const def = defs[code];
      const raw = (state.ai_values || {})[code] || '';
      const r = GS1.validateAi(def, raw);
      if (!r.ok) { errors.push('(' + code + ') ' + def.name + ': ' + r.error); continue; }
      out.push([r.resolved_code, r.encoded]);
    }
    return { resolved: out, errors };
  }

  const PREDEF = ['00','01','02','03','04','11','12','13','14','15','16','17','18','19','20','31','32','33','34','35','36','41'];
  const isPredef = c => PREDEF.includes(String(c).slice(0, 2));

  function assemble(pairs) {
    const sorted = pairs.slice().sort((a, b) => (isPredef(a[0]) ? 0 : 1) - (isPredef(b[0]) ? 0 : 1));
    let hri = '', machine = '';
    sorted.forEach((p, i) => {
      const isLast = i === sorted.length - 1;
      const needsFnc1 = !isPredef(p[0]) && !isLast;
      hri += '(' + p[0] + ')' + p[1];
      machine += p[0] + p[1] + (needsFnc1 ? '<FNC1>' : '');
    });
    return { hri, machine };
  }

  function combinationCheck(pairs) {
    const codes = pairs.map(p => p[0]);
    const errs = [];
    if (codes.length !== new Set(codes).size) errs.push('Duplicate AIs are not allowed.');
    if (codes.includes('01') && codes.includes('02')) errs.push('AI (01) and AI (02) cannot appear together.');
    if (codes.includes('02') !== codes.includes('37')) errs.push('AI (02) and AI (37) must be used together.');
    const total = pairs.reduce((acc, p) => acc + p[0].length + p[1].length, 0);
    if (total > 48) errs.push('Total data characters (' + total + ') exceed the GS1-128 maximum of 48.');
    return errs;
  }

  function labelFor(code) {
    if (code === '01') return 'GTIN';
    if (code === '02') return 'Contained GTIN';
    const base = (code.length === 4 && (code.startsWith('31') || code.startsWith('32'))) ? code.slice(0,3) : code;
    return defs[base] ? defs[base].name : code;
  }

  function buildTable(resolved) {
    const tbl = document.getElementById('review-table');
    tbl.textContent = '';
    const thead = document.createElement('thead');
    thead.innerHTML = '<tr><th>AI</th><th>Field</th><th>Value</th><th></th></tr>';
    tbl.appendChild(thead);
    const tbody = document.createElement('tbody');
    resolved.forEach(([code, value]) => {
      const tr = document.createElement('tr');
      const tdCode = document.createElement('td');
      const codeEl = document.createElement('code'); codeEl.textContent = '(' + code + ')';
      tdCode.appendChild(codeEl);
      const tdName = document.createElement('td'); tdName.textContent = labelFor(code);
      const tdValue = document.createElement('td');
      const valEl = document.createElement('code'); valEl.textContent = value;
      tdValue.appendChild(valEl);
      const tdEdit = document.createElement('td'); tdEdit.className = 'text-end';
      const a = document.createElement('a');
      a.href = code === '01' ? '<?= base_url() ?>wizard/input' : '<?= base_url() ?>wizard/select-ai';
      a.textContent = 'Edit';
      tdEdit.appendChild(a);
      tr.appendChild(tdCode); tr.appendChild(tdName); tr.appendChild(tdValue); tr.appendChild(tdEdit);
      tbody.appendChild(tr);
    });
    tbl.appendChild(tbody);
  }

  function buildErrors(allErrors) {
    const warn = document.getElementById('review-warnings');
    warn.textContent = '';
    if (!allErrors.length) { warn.classList.add('d-none'); return; }
    warn.classList.remove('d-none');
    const heading = document.createElement('strong'); heading.textContent = 'Cannot generate yet:';
    warn.appendChild(heading);
    const ul = document.createElement('ul'); ul.className = 'mb-0';
    allErrors.forEach(e => { const li = document.createElement('li'); li.textContent = e; ul.appendChild(li); });
    warn.appendChild(ul);
  }

  function render() {
    const { resolved, errors: aiErrors } = resolve();
    buildTable(resolved);

    const combErrors = combinationCheck(resolved);
    const allErrors = aiErrors.concat(combErrors);
    buildErrors(allErrors);
    const cont = document.getElementById('continue-btn');
    if (allErrors.length) { cont.classList.add('disabled'); cont.setAttribute('aria-disabled', 'true'); cont.removeAttribute('href'); }
    else { cont.classList.remove('disabled'); cont.removeAttribute('aria-disabled'); cont.setAttribute('href', '<?= base_url() ?>wizard/export'); }

    const a = assemble(resolved);
    document.getElementById('hri-string').textContent = a.hri;
    document.getElementById('machine-string').textContent = a.machine;
    Wizard.patch({ resolved, hri: a.hri, machine_placeholder: a.machine });

    const errEl = document.getElementById('preview-error');
    errEl.textContent = '';
    if (allErrors.length) return;
    BarcodePreview.render(document.getElementById('preview-canvas'), a.hri);
    if (document.getElementById('preview-canvas').dataset.error) {
      errEl.textContent = 'Preview error: ' + document.getElementById('preview-canvas').dataset.error;
    }
  }

  render();
});
</script>
