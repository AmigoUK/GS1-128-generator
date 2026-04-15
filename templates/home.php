<div class="row align-items-center mb-5">
  <div class="col-md-7">
    <h1 class="display-5">Generate standards-compliant GS1-128 barcodes.</h1>
    <p class="lead text-muted">Turn an EAN-13 or GTIN-14 into a fully framed GS1-128 (EAN-128) barcode with batch numbers, dates, weights, serials and more — all validated against the GS1 General Specifications.</p>
  </div>
  <div class="col-md-5 text-center">
    <canvas id="hero-barcode" aria-label="GS1-128 barcode preview"></canvas>
    <script>window.addEventListener('DOMContentLoaded',()=>{try{bwipjs.toCanvas('hero-barcode',{bcid:'gs1-128',text:'(01)15901234123454(15)260115(10)LOT2024A',scale:2,height:14,includetext:true,textxalign:'center'});}catch(e){}});</script>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h2 class="h4">Create a barcode</h2>
        <p>Step-by-step guided wizard. One question at a time. Auto-validates check digits and Application Identifiers as you type.</p>
        <a href="<?= base_url() ?>wizard" class="btn btn-primary">Start the wizard</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100 shadow-sm">
      <div class="card-body">
        <h2 class="h4">Bulk import</h2>
        <p>Upload a CSV or XML file and generate multiple barcodes at once. Download templates with example rows. Demo limited to 6 barcodes per file.</p>
        <a href="<?= base_url() ?>bulk" class="btn btn-outline-primary">Open bulk import</a>
      </div>
    </div>
  </div>
</div>
