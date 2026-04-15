<?php
$current = 3; include __DIR__ . '/_progress.php';
$defs = ai_definitions();
?>

<div id="ai-data-screen" class="row justify-content-center">
  <div class="col-lg-8"></div>
</div>

<script id="ai-defs" type="application/json"><?= json_encode($defs, JSON_UNESCAPED_SLASHES) ?></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const E = Wizard.escapeHtml;
  const defs = JSON.parse(document.getElementById('ai-defs').textContent);
  const state = Wizard.load();
  if (!state.gtin14 || !Array.isArray(state.selected_ais) || state.selected_ais.length === 0) {
    window.location.href = '<?= base_url() ?>wizard/select-ai'; return;
  }
  const screen = document.querySelector('#ai-data-screen .col-lg-8');
  let cursor = state.ai_cursor || 0;
  let values = state.ai_values || {};

  function buildInput(def) {
    if (def.data_type === 'D') {
      return '<input type="date" id="ai-input" class="form-control form-control-lg" placeholder="' + E(def.placeholder) + '" autocomplete="off">' +
             '<div class="form-text">Pick a date or type ' + E(def.format_hint) + '.</div>';
    }
    if (def.has_decimal) {
      return '<div class="input-group"><input type="text" inputmode="decimal" id="ai-input" class="form-control form-control-lg" placeholder="' + E(def.placeholder) + '" autocomplete="off">' +
             '<span class="input-group-text">' + E(def.unit || '') + '</span></div>';
    }
    if (def.data_type === 'N') {
      return '<input type="text" inputmode="numeric" id="ai-input" class="form-control form-control-lg" placeholder="' + E(def.placeholder) + '" autocomplete="off" maxlength="' + E(def.max_length) + '">';
    }
    return '<input type="text" id="ai-input" class="form-control form-control-lg" placeholder="' + E(def.placeholder) + '" autocomplete="off" maxlength="' + E(def.max_length) + '">';
  }

  function render() {
    const code = state.selected_ais[cursor];
    const def = defs[code];
    const pos = (cursor + 1) + ' of ' + state.selected_ais.length;

    screen.innerHTML =
      '<p class="text-muted small">AI ' + E(pos) + '</p>' +
      '<h1 class="h3 mb-2">(' + E(def.code) + ') ' + E(def.name) + '</h1>' +
      '<p class="text-muted">' + E(def.description) + '</p>' +
      '<div class="mb-3">' +
        '<label for="ai-input" class="form-label fw-semibold">' + E(def.name) + '</label>' +
        buildInput(def) +
        '<div id="ai-fb" class="mt-2" role="status" aria-live="polite"></div>' +
        '<details class="mt-2"><summary class="small text-muted">What format is expected?</summary>' +
          '<div class="small mt-1"><strong>Format:</strong> ' + E(def.format_hint) + '<br><strong>Example:</strong> ' + E(def.example) + '</div>' +
        '</details>' +
      '</div>' +
      '<div class="d-flex justify-content-between">' +
        (cursor === 0
          ? '<a href="<?= base_url() ?>wizard/select-ai" class="btn btn-link">&laquo; Back to selection</a>'
          : '<button id="prev-btn" class="btn btn-link">&laquo; Previous AI</button>') +
        '<button id="next-btn" class="btn btn-primary" disabled>' + (cursor === state.selected_ais.length - 1 ? 'Review &raquo;' : 'Next AI &raquo;') + '</button>' +
      '</div>';

    const input = document.getElementById('ai-input');
    const fb = document.getElementById('ai-fb');
    const next = document.getElementById('next-btn');
    if (values[code] !== undefined) input.value = values[code];

    function evaluate() {
      const v = input.value;
      const r = GS1.validateAi(def, v);
      input.classList.toggle('field-ok', r.ok);
      input.classList.toggle('field-err', !r.ok && v.length > 0);
      fb.textContent = '';
      if (!v.length) { next.disabled = true; return; }
      if (r.ok) {
        const ok = document.createElement('span'); ok.className = 'text-success'; ok.textContent = '\u2713 Valid. ';
        const enc = document.createElement('code'); enc.textContent = '(' + r.resolved_code + ')' + r.encoded;
        fb.appendChild(ok); fb.appendChild(document.createTextNode('Encoded as ')); fb.appendChild(enc);
        next.disabled = false;
      } else {
        const err = document.createElement('span'); err.className = 'text-danger'; err.textContent = '\u2715 ' + r.error;
        fb.appendChild(err);
        next.disabled = true;
      }
    }

    input.addEventListener('input', evaluate);
    if (input.value) evaluate();

    next.addEventListener('click', (e) => {
      e.preventDefault();
      values[code] = input.value;
      Wizard.patch({ ai_values: values });
      if (cursor === state.selected_ais.length - 1) {
        window.location.href = '<?= base_url() ?>wizard/review';
      } else {
        cursor++; Wizard.patch({ ai_cursor: cursor });
        render();
      }
    });
    const prev = document.getElementById('prev-btn');
    if (prev) prev.addEventListener('click', (e) => {
      e.preventDefault();
      values[code] = input.value;
      Wizard.patch({ ai_values: values });
      cursor--; Wizard.patch({ ai_cursor: cursor });
      render();
    });

    setTimeout(() => input.focus(), 50);
  }

  render();
});
</script>
