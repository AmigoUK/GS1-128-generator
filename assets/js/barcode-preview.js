// Live GS1-128 preview using bwip-js. Pass the HRI string (with FNC1 as ~) or
// the assembled segments. bwip-js handles GS1-128 via bcid:'gs1-128'.

const BarcodePreview = (function () {
  function render(canvasEl, hriString) {
    if (!canvasEl || !window.bwipjs) return;
    try {
      bwipjs.toCanvas(canvasEl, {
        bcid: 'gs1-128',
        text: hriString,
        scale: 2,
        height: 14,
        includetext: true,
        textxalign: 'center',
        parsefnc: true,
      });
      canvasEl.dataset.error = '';
    } catch (e) {
      const ctx = canvasEl.getContext('2d');
      ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);
      canvasEl.dataset.error = e && e.message ? e.message : String(e);
    }
  }
  return { render };
})();
