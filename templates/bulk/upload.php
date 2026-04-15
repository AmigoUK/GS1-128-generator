<h1 class="h3 mb-3">Bulk import</h1>
<p class="text-muted">Upload a CSV or XML file to generate multiple GS1-128 barcodes at once.</p>

<div class="alert alert-secondary" role="alert">
  <strong>Demo limit:</strong> Bulk import processes the first <?= BULK_LIMIT ?> rows of any file. Rows beyond that are listed but not generated.
</div>

<?php if (!empty($_SESSION['bulk_error'])): ?>
  <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_SESSION['bulk_error']) ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-md-7">
    <form method="post" enctype="multipart/form-data" action="<?= base_url() ?>bulk/upload" id="upload-form">
      <div id="drop-zone" class="border border-2 border-dashed rounded p-5 text-center bg-white" tabindex="0" role="button" aria-label="Choose or drop a CSV/XML file">
        <p class="mb-2"><strong>Drop your CSV or XML file here</strong> or click to browse.</p>
        <p class="small text-muted mb-0" id="file-info">No file selected.</p>
        <input type="file" name="datafile" id="datafile" accept=".csv,.xml" class="d-none" required>
      </div>
      <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Upload &amp; validate</button>
      </div>
    </form>
  </div>

  <div class="col-md-5">
    <div class="card">
      <div class="card-body">
        <h2 class="h6">Need a starting point?</h2>
        <p class="small">Download a template with column headers and one example row.</p>
        <div class="d-grid gap-2">
          <a href="<?= base_url() ?>download/template.csv" class="btn btn-outline-secondary btn-sm">CSV template</a>
          <a href="<?= base_url() ?>download/template.xml" class="btn btn-outline-secondary btn-sm">XML template</a>
          <a href="<?= base_url() ?>download/schema.xsd" class="btn btn-outline-secondary btn-sm">XML schema (XSD)</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const dropZone = document.getElementById('drop-zone');
  const input = document.getElementById('datafile');
  const info = document.getElementById('file-info');
  const submit = document.getElementById('submit-btn');

  function describe() {
    if (input.files && input.files.length) {
      const f = input.files[0];
      info.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
      submit.disabled = false;
    } else {
      info.textContent = 'No file selected.';
      submit.disabled = true;
    }
  }
  BulkImport.wireDropZone(dropZone, input, describe);
  dropZone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
});
</script>
