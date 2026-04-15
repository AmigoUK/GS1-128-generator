// Client-side mirrors of /includes/validation.php — used for instant feedback.
// Final authority is the server.

const GS1 = (function () {
  const charset82 = "!\"%&'()*+,-./0123456789:;<=>?ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz";

  function checkDigit(body) {
    let sum = 0;
    for (let i = 0; i < body.length; i++) {
      const d = parseInt(body[body.length - 1 - i], 10);
      sum += (i % 2 === 0) ? d * 3 : d;
    }
    return (10 - (sum % 10)) % 10;
  }

  function validateEan13(s) {
    if (!/^\d{13}$/.test(s)) return { ok: false, error: 'EAN-13 must be exactly 13 digits.' };
    const expected = checkDigit(s.slice(0, 12));
    const actual = parseInt(s[12], 10);
    if (expected !== actual) return { ok: false, error: `Invalid check digit: should be ${expected}, you entered ${actual}.` };
    return { ok: true };
  }

  function validateGtin14(s) {
    if (!/^\d{14}$/.test(s)) return { ok: false, error: 'GTIN-14 must be exactly 14 digits.' };
    const expected = checkDigit(s.slice(0, 13));
    const actual = parseInt(s[13], 10);
    if (expected !== actual) return { ok: false, error: `Invalid check digit: should be ${expected}, you entered ${actual}.` };
    return { ok: true };
  }

  function deriveGtin14(ean13, packagingIndicator) {
    const v = validateEan13(ean13);
    if (!v.ok) return v;
    const pi = parseInt(packagingIndicator, 10);
    if (Number.isNaN(pi) || pi < 0 || pi > 9) return { ok: false, error: 'Packaging indicator must be 0-9.' };
    const body = String(pi) + ean13.slice(0, 12);
    return { ok: true, gtin14: body + checkDigit(body) };
  }

  function toYymmdd(value) {
    value = (value || '').trim();
    if (!value) return null;
    if (/^\d{6}$/.test(value)) return value;
    let m;
    if ((m = value.match(/^(\d{4})-(\d{2})-(\d{2})$/))) return m[1].slice(2) + m[2] + m[3];
    if ((m = value.match(/^(\d{4})\/(\d{2})\/(\d{2})$/))) return m[1].slice(2) + m[2] + m[3];
    if ((m = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/))) return m[3].slice(2) + m[2] + m[1];
    return null;
  }

  function inCharset82(s) {
    for (const ch of s) { if (!charset82.includes(ch)) return false; }
    return true;
  }

  function validateAi(def, value) {
    if (!value) return { ok: false, error: 'Value is required.' };
    if (def.data_type === 'D') {
      const yy = toYymmdd(value);
      if (!yy) return { ok: false, error: 'Date must be YYMMDD or YYYY-MM-DD.' };
      const month = parseInt(yy.slice(2, 4), 10);
      const day = parseInt(yy.slice(4, 6), 10);
      if (month < 1 || month > 12) return { ok: false, error: `Month must be 01-12, got ${yy.slice(2,4)}.` };
      if (day < 0 || day > 31) return { ok: false, error: `Day must be 00-31, got ${yy.slice(4,6)}.` };
      return { ok: true, encoded: yy, resolved_code: def.code };
    }
    if (def.has_decimal) {
      if (!/^\d+(\.\d{0,6})?$/.test(value)) return { ok: false, error: 'Weight must be a decimal number (e.g. 1.250).' };
      const [intPart, fracPart = ''] = value.split('.');
      const intStr = intPart.replace(/^0+/, '') || '0';
      const combined = intStr + fracPart;
      if (combined.length > 6) return { ok: false, error: 'Weight value too large for 6-digit field.' };
      const encoded = combined.padStart(6, '0');
      return { ok: true, encoded, resolved_code: def.code + fracPart.length };
    }
    if (def.data_type === 'N') {
      if (!/^\d+$/.test(value)) return { ok: false, error: 'Only digits are allowed.' };
      if (value.length < def.min_length || value.length > def.max_length) {
        return { ok: false, error: `Length must be between ${def.min_length} and ${def.max_length} digits.` };
      }
      if (def.code === '01' || def.code === '02') {
        const v = validateGtin14(value); if (!v.ok) return v;
      }
      return { ok: true, encoded: value, resolved_code: def.code };
    }
    if (def.data_type === 'X') {
      if (!inCharset82(value)) return { ok: false, error: 'Value contains characters outside GS1 Character Set 82.' };
      if (value.length < def.min_length || value.length > def.max_length) {
        return { ok: false, error: `Length must be between ${def.min_length} and ${def.max_length} characters.` };
      }
      return { ok: true, encoded: value, resolved_code: def.code };
    }
    return { ok: false, error: 'Unknown data type.' };
  }

  return { checkDigit, validateEan13, validateGtin14, deriveGtin14, toYymmdd, inCharset82, validateAi };
})();
