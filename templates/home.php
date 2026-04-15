<div class="row align-items-center mb-5">
  <div class="col-md-7">
    <h1 class="display-5">Generate standards-compliant GS1-128 barcodes.</h1>
    <p class="lead text-muted">Turn an EAN-13 or GTIN-14 into a fully framed GS1-128 (EAN-128) barcode with batch numbers, dates, weights, serials and more &mdash; all validated against the GS1 General Specifications.</p>
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

<hr class="my-5">

<section aria-labelledby="learn-heading">
  <h2 id="learn-heading" class="h3 mb-4">A quick primer on GS1 barcodes</h2>

  <div class="row g-4">
    <article class="col-md-6">
      <h3 class="h5">What is a GS1-128 barcode?</h3>
      <p>GS1-128 (formerly called EAN-128) is a <strong>high-capacity, variable-length</strong> barcode based on the Code 128 symbology. Unlike a plain EAN-13 on a consumer product &mdash; which only carries the product identifier &mdash; a GS1-128 can pack together multiple pieces of information in a single scan: the product GTIN plus batch number, production date, best-before date, net weight, serial number, purchase order, and so on.</p>
      <p>It's the standard barcode for <strong>logistics and supply-chain labels</strong>: warehouse pallets, case labels, shipping cartons, healthcare traceability labels, fresh-food date coding.</p>
    </article>

    <article class="col-md-6">
      <h3 class="h5">How does it work?</h3>
      <p>Each piece of data is prefixed by a 2-, 3- or 4-digit <strong>Application Identifier (AI)</strong> that tells the scanner what it is. For example:</p>
      <ul class="small mb-2">
        <li><code>(01)</code> &mdash; GTIN (Global Trade Item Number)</li>
        <li><code>(10)</code> &mdash; batch / lot number</li>
        <li><code>(15)</code> &mdash; best-before date (YYMMDD)</li>
        <li><code>(17)</code> &mdash; expiry date (YYMMDD)</li>
        <li><code>(3103)</code> &mdash; net weight in kg with 3 decimal places</li>
        <li><code>(21)</code> &mdash; serial number</li>
      </ul>
      <p class="small mb-0">Variable-length fields are followed by a special separator character called FNC1 so the scanner knows where one field ends and the next AI begins.</p>
    </article>
  </div>

  <div class="row g-4 mt-1">
    <article class="col-md-6">
      <h3 class="h5">Do I need to register with GS1?</h3>
      <p><strong>Yes &mdash; if you want your barcode to be globally unique and scannable by retailers, distributors or customs.</strong> GS1 is the only organisation that issues legitimate GTIN / EAN company prefixes.</p>
      <ol class="small">
        <li>Join your national GS1 member organisation (in the UK this is <a href="https://www.gs1uk.org/" target="_blank" rel="noopener">GS1 UK</a>).</li>
        <li>You'll be assigned a GS1 Company Prefix. From that prefix you create your own GTINs for each product variant.</li>
        <li>Pay the annual licence fee (tiered by number of GTINs and company turnover).</li>
      </ol>
      <p class="small mb-0"><em>Internal-only barcodes (inside your own warehouse) don't strictly need GS1 registration, but any barcode that leaves your site must use a genuine, licensed GTIN &mdash; otherwise it risks collisions with other companies' codes.</em></p>
    </article>

    <article class="col-md-6">
      <h3 class="h5">Structure of a GTIN</h3>
      <p>A <strong>GTIN-13</strong> (the 13-digit EAN on consumer packaging) is made up of:</p>
      <ul class="small">
        <li>Country / numbering-system prefix (first 2&ndash;3 digits)</li>
        <li>Your GS1 Company Prefix (4&ndash;10 digits, assigned to you)</li>
        <li>Item reference (the rest, assigned by you)</li>
        <li>A <strong>check digit</strong> (last digit, calculated using a weighted mod-10 algorithm)</li>
      </ul>
      <p class="small">A <strong>GTIN-14</strong> wraps a GTIN-13 with an extra <em>packaging indicator</em> digit (0&ndash;9) at the front, identifying how the goods are packed (inner, case, pallet, etc.), and its own recalculated check digit.</p>
      <p class="small mb-0">This tool validates all of the above automatically &mdash; the wizard flags bad check digits and tells you exactly what the correct digit should have been.</p>
    </article>
  </div>
</section>

<section aria-labelledby="resources-heading" class="mt-5">
  <h2 id="resources-heading" class="h3 mb-3">Where to learn more</h2>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h3 class="h6">Official GS1</h3>
          <p class="small mb-2">The global standards body. Free PDF of the GS1 General Specifications (the definitive rulebook) is published here.</p>
          <a href="https://www.gs1.org/" target="_blank" rel="noopener" class="small">gs1.org &rarr;</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h3 class="h6">GS1 UK (registration)</h3>
          <p class="small mb-2">UK member organisation. Start here if you're a UK business and need to apply for a company prefix and GTINs.</p>
          <a href="https://www.gs1uk.org/" target="_blank" rel="noopener" class="small">gs1uk.org &rarr;</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h3 class="h6">This project's spec</h3>
          <p class="small mb-2">The full technical specification used to build this tool &mdash; check-digit algorithms, AI table, FNC1 rules, encoding details.</p>
          <a href="https://github.com/AmigoUK/GS1-128-generator/blob/main/SPECIFICATION.md" target="_blank" rel="noopener" class="small">SPECIFICATION.md on GitHub &rarr;</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section aria-labelledby="how-tool-heading" class="mt-5">
  <h2 id="how-tool-heading" class="h3 mb-3">How this tool fits in</h2>
  <div class="row">
    <div class="col-lg-9">
      <p>You already have your GS1 company prefix and GTINs &mdash; this generator helps you <strong>compose valid supply-chain labels from them</strong>. It doesn't issue GTINs (only GS1 can), but it does:</p>
      <ul>
        <li>Validate every EAN-13 / GTIN-14 you type against the mod-10 check digit.</li>
        <li>Walk you through the Application Identifiers step by step, with plain-English prompts and instant format checking.</li>
        <li>Place the FNC1 separators correctly &mdash; a surprisingly common source of bugs in home-grown generators.</li>
        <li>Render the final barcode as PNG, SVG or PDF, ready to print onto labels.</li>
      </ul>
      <p>Good for: warehouse operators, small food producers, pharmacies and anyone preparing logistics labels without a full WMS. Not a substitute for certified label-printing software in highly regulated environments (medical devices, some pharma).</p>
    </div>
  </div>
</section>

<section aria-labelledby="faq-heading" class="mt-5 mb-4">
  <h2 id="faq-heading" class="h3 mb-3">FAQ</h2>
  <div class="accordion" id="faq-accordion">
    <div class="accordion-item">
      <h3 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-1">Can I use a random 13-digit number as my barcode?</button>
      </h3>
      <div id="faq-1" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
        <div class="accordion-body small">
          <p class="mb-2">Only for internal use. A retail-facing EAN-13 must come from a GS1-assigned company prefix, otherwise it will clash with someone else's real GTIN and cause ugly confusion in retail scanners and online marketplaces (Amazon, in particular, enforces this).</p>
          <p class="mb-2"><strong>For internal use do it properly:</strong> EAN-13 codes starting with the prefix range <code>020</code>&ndash;<code>029</code> are reserved by GS1 for <em>in-store / restricted circulation</em>. They are never assigned to any company's products, so they're guaranteed not to collide with a real GTIN. Use them for:</p>
          <ul class="mb-2">
            <li>warehouse bin / location labels</li>
            <li>variable-weight items priced at the till (deli, butcher, produce)</li>
            <li>internal asset tagging, tote IDs, work-in-progress tracking</li>
          </ul>
          <p class="mb-0">Structure: <code>02 X XXXXXX CCCCC D</code> &mdash; the leading <code>02</code> marks it as internal, the next digit identifies the store / department (your own convention), then your item reference, then the standard mod-10 check digit. This tool will validate the check digit for any 020&ndash;029 code you enter, same as a real GTIN.</p>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h3 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-2">What's the difference between EAN-13, GTIN-13, GTIN-14 and GS1-128?</button>
      </h3>
      <div id="faq-2" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
        <div class="accordion-body small">EAN-13 and GTIN-13 are essentially the same thing &mdash; a 13-digit identifier on consumer-unit packaging. GTIN-14 is a 14-digit form used on cases and pallets (one extra packaging-indicator digit up front). GS1-128 is a <em>barcode format</em> (based on Code 128) that can carry a GTIN <em>plus</em> additional data like batch and expiry using Application Identifiers.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h3 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-3">Why does my scanner read an extra invisible character between fields?</button>
      </h3>
      <div id="faq-3" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
        <div class="accordion-body small">That's the FNC1 separator (ASCII 0x1D / GS). It's how the scanner knows a variable-length Application Identifier has ended. Most POS / WMS software strips it or converts it to a field separator automatically. Your barcode without FNC1 between variable-length AIs is <em>not</em> a valid GS1-128 &mdash; this tool places them for you.</div>
      </div>
    </div>
    <div class="accordion-item">
      <h3 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-4">What's the maximum length of a GS1-128 barcode?</button>
      </h3>
      <div id="faq-4" class="accordion-collapse collapse" data-bs-parent="#faq-accordion">
        <div class="accordion-body small">48 data characters, per the GS1 General Specifications. This tool enforces that limit and will tell you if your combination of AIs exceeds it before you print a label that scanners will refuse.</div>
      </div>
    </div>
  </div>
</section>
