# GS1-128 barcode generator: complete developer specification

**This document contains every rule, algorithm, data structure, and validation step a developer needs to build a web application that accepts EAN-13 or GTIN-14 input and produces valid GS1-128 barcodes.** No code is included — only logic, formats, and specifications. The system must validate input identifiers, construct GS1-128 element strings from Application Identifiers, encode them using Code 128 symbology with FNC1 framing, compute all check digits, and render a scannable barcode with correct human-readable interpretation. The sections below progress from foundational identifiers through encoding logic to rendering rules.

---

## 1. EAN-13 (GTIN-13) structure and validation

EAN-13 and GTIN-13 are synonymous — EAN-13 names the barcode symbology, GTIN-13 names the data structure. The number is **exactly 13 numeric digits** (character set: 0–9 only).

### 1.1 Digit allocation

| Component | Positions (L→R) | Length | Description |
|---|---|---|---|
| GS1 Prefix | 1–3 | 2–3 digits | Identifies the issuing GS1 Member Organization (not country of manufacture). Examples: 00–13 = US/Canada, 30–37 = France, 50 = UK, 400–440 = Germany, 690–695 = China, 978 = ISBN |
| Manufacturer Code | Variable | Variable | Assigned by the GS1 organization; shared by all products from one company |
| Product Code | Variable | Variable | Assigned by the manufacturer |
| Check Digit | 13 | 1 digit | Calculated via modulo-10 weighted sum |

GS1 Prefix + Manufacturer Code + Product Code always total **12 digits**. The 13th digit is always the check digit. Prefixes 020–029 are reserved for retailer internal use.

### 1.2 Check digit algorithm (modulo-10)

The universal GS1 rule: numbering positions **from right to left**, odd positions get weight **3** and even positions get weight **1**. For EAN-13's 12 data digits read left to right, this produces alternating weights:

```
Position (L→R):   1   2   3   4   5   6   7   8   9  10  11  12
Weight:            1   3   1   3   1   3   1   3   1   3   1   3
```

**To calculate the check digit:**
1. Multiply each of the first 12 digits by its weight (alternating 1, 3, 1, 3… from left).
2. Sum all products.
3. Compute `remainder = sum mod 10`.
4. If remainder is 0, check digit = **0**. Otherwise, check digit = **10 − remainder**.

**To validate a complete 13-digit EAN-13:**
1. Apply weights 1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3, **1** to all 13 digits.
2. Sum all weighted digits.
3. Valid if and only if `sum mod 10 == 0`.

### 1.3 Worked example

Input (first 12 digits): `4 0 0 6 3 8 1 3 3 3 9 3`

| Pos | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 | 12 |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Digit | 4 | 0 | 0 | 6 | 3 | 8 | 1 | 3 | 3 | 3 | 9 | 3 |
| Weight | 1 | 3 | 1 | 3 | 1 | 3 | 1 | 3 | 1 | 3 | 1 | 3 |
| Product | 4 | 0 | 0 | 18 | 3 | 24 | 1 | 9 | 3 | 9 | 9 | 9 |

Sum = **89**. Remainder = 89 mod 10 = **9**. Check digit = 10 − 9 = **1**. Complete EAN-13: `4006381333931`. Verification: 89 + (1 × 1) = 90; 90 mod 10 = 0 ✓.

### 1.4 Input validation rules for EAN-13

1. String must be exactly 13 characters.
2. Every character must be a digit 0–9.
3. The 13th digit must pass the modulo-10 check (weighted sum of all 13 digits divisible by 10).
4. Reject any input that fails any of these three conditions.

---

## 2. GTIN-14 structure and its relationship to EAN-13

GTIN-14 is the 14-digit identifier used for cases, cartons, and pallets. It is typically encoded in ITF-14 symbology or within GS1-128 using AI (01). The number is **exactly 14 numeric digits** (0–9 only).

### 2.1 Exact structure

```
Position:   [1]     [2–13]                              [14]
            PI      First 12 digits of EAN-13            New check digit
                    (EAN-13 WITHOUT its check digit)
```

| Component | Position(s) | Description |
|---|---|---|
| **Packaging Indicator** | 1 | Single digit 0–9. Values 1–8 denote packaging hierarchy levels (no universal meaning — company-defined). Value 9 = variable measure trade item. Value 0 = the GTIN-14 is effectively a zero-padded GTIN-13. |
| **GS1 Company Prefix + Item Reference** | 2–13 | Identical to positions 1–12 of the source EAN-13 (i.e., the EAN-13 with its check digit stripped) |
| **Check Digit** | 14 | Recalculated over all 13 preceding digits — **not** the same as the EAN-13 check digit |

### 2.2 Deriving GTIN-14 from EAN-13 — step by step

1. **Strip** the EAN-13's last digit (its check digit). Result: 12 digits.
2. **Prepend** the chosen packaging indicator digit (1–8 typically). Result: 13 digits.
3. **Calculate** a new check digit using the GTIN-14 modulo-10 algorithm (below).
4. **Append** the new check digit. Result: 14 digits = valid GTIN-14.

### 2.3 GTIN-14 check digit algorithm

Same modulo-10 algorithm as EAN-13, but because GTIN-14 has an **even** number of digits, the left-to-right weight pattern starts with **3** (not 1):

```
Position (L→R):   1   2   3   4   5   6   7   8   9  10  11  12  13
Weight:            3   1   3   1   3   1   3   1   3   1   3   1   3
```

Calculation and validation steps are identical to EAN-13 (substitute the weights above). To validate a complete 14-digit GTIN-14: apply weights 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3, 1, 3, **1** to all 14 digits; valid if `sum mod 10 == 0`.

### 2.4 Worked example

Derive GTIN-14 from EAN-13 `4006381333931` with packaging indicator `1`:

1. Strip check digit: `400638133393`
2. Prepend indicator: `1400638133393`
3. Calculate check digit:

| Pos | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 | 11 | 12 | 13 |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Digit | 1 | 4 | 0 | 0 | 6 | 3 | 8 | 1 | 3 | 3 | 3 | 9 | 3 |
| Weight | 3 | 1 | 3 | 1 | 3 | 1 | 3 | 1 | 3 | 1 | 3 | 1 | 3 |
| Product | 3 | 4 | 0 | 0 | 18 | 3 | 24 | 1 | 9 | 3 | 9 | 9 | 9 |

Sum = **92**. Remainder = 2. Check digit = **8**. GTIN-14 = `14006381333938`.

### 2.5 Embedding GTIN into GS1-128 AI (01)

When encoding a GTIN-13 in AI (01), left-pad with a zero to create 14 digits: `0` + full 13-digit EAN-13 = 14-digit GTIN. Example: EAN-13 `4006381333931` → AI (01) data = `04006381333931`. When encoding a GTIN-14 directly, use all 14 digits as-is.

---

## 3. Code 128 symbology — the engine behind GS1-128

GS1-128 uses Code 128 symbology (ISO/IEC 15417). Understanding Code 128's three character subsets, switching mechanisms, and check digit is essential.

### 3.1 Three character subsets

| Subset | Values 0–95 map to | Special characters (values 96–102) | Primary use |
|---|---|---|---|
| **Code A** | ASCII 0–95: digits, uppercase A–Z, control characters (NUL–US), space, punctuation | FNC3(96), FNC2(97), ShiftB(98), CodeC(99), CodeB(100), FNC4(101), FNC1(102) | Data containing control characters or uppercase-only text |
| **Code B** | ASCII 32–127: digits, uppercase A–Z, lowercase a–z, space, punctuation, `` ` { | } ~ DEL `` | FNC3(96), FNC2(97), ShiftA(98), CodeC(99), FNC4(100), CodeA(101), FNC1(102) | Data with mixed case or lowercase letters |
| **Code C** | Digit pairs 00–99 (double-density numeric) | CodeB(100), CodeA(101), FNC1(102) | Long numeric sequences — encodes 2 digits per symbol |

**Key values for start and stop characters:**

| Character | Value |
|---|---|
| Start Code A | 103 |
| Start Code B | 104 |
| Start Code C | 105 |
| Stop | 106 |

### 3.2 Switching between subsets

**Persistent switches** (apply to all subsequent characters): CodeA (value 101 in B/C), CodeB (value 100 in A/C), CodeC (value 99 in A/B). **SHIFT** (value 98, exists only in A and B): temporarily interprets the next single character using the other subset, then reverts. All switch characters participate in the check digit calculation at their positional weight.

### 3.3 When to use Code C for optimization

Code C encodes two digits per symbol, halving the width of numeric sequences. The overhead of switching into and out of Code C is 1–2 symbols.

- **At the start of data**: use Start C if the data begins with **≥ 4 consecutive digits**.
- **In the middle of data**: switch to Code C for **≥ 6 consecutive digits** (2 switch symbols overhead).
- **At the end of data**: switch to Code C for **≥ 4 consecutive digits** (1 switch symbol overhead).
- If the digit count in a Code C run is **odd**, encode the last lone digit in Code A or B.

Since most GS1 AIs begin with numeric AI codes followed by numeric data, **Code C is the typical starting subset for GS1-128**.

### 3.4 Code 128 check digit — modulo 103

Each Code 128 symbol character is 3 bars + 3 spaces = 6 elements, each 1–4 modules wide, totaling **11 modules per character**. The stop pattern is special at **13 modules** (4 bars + 3 spaces).

**Algorithm:**
1. Start with the start character's value (103, 104, or 105). This counts as weight 1 (position 0, effectively multiplied by 1).
2. Each subsequent symbol character is assigned position weight 1, 2, 3, … n (the first data character after start = position 1).
3. Multiply each character's value by its position weight.
4. Sum: `start_value + Σ(value[i] × i)` for i = 1 to n.
5. Check digit = **sum mod 103**.
6. Look up the check digit value in the Code 128 table to find its bar pattern.
7. Append the check character before the stop symbol.

**FNC1, SHIFT, and CODE A/B/C switch characters all participate** in this calculation with their respective values and position weights. The stop character does **not** participate. The check digit is **not** shown in human-readable text.

### 3.5 Worked example — GS1-128 with AI (10)

Encoding AI (10) with batch data "2503X": Start C, FNC1, 10, 25, 03, Code B, X.

| Symbol | Value | Position | Value × Position |
|---|---|---|---|
| Start C | 105 | — | 105 |
| FNC1 | 102 | 1 | 102 |
| 10 (pair) | 10 | 2 | 20 |
| 25 (pair) | 25 | 3 | 75 |
| 03 (pair) | 3 | 4 | 12 |
| Code B | 100 | 5 | 500 |
| X | 56 | 6 | 336 |

Sum = 105 + 102 + 20 + 75 + 12 + 500 + 336 = **1150**. Check digit = 1150 mod 103 = **17**. Final: [StartC][FNC1] 10 25 03 [CodeB] X [check=17] [Stop].

---

## 4. GS1-128 framing rules and FNC1

GS1-128 **is** Code 128 with two additional constraints: an FNC1 character must appear first, and all data must be structured using Application Identifiers.

### 4.1 Mandatory FNC1 in first position

**FNC1 (value 102)** must be the first symbol character after the start character. This is what distinguishes GS1-128 from generic Code 128. Structure: `[Start A/B/C] [FNC1] [AI + Data] ... [Check] [Stop]`. A Code 128 symbol without FNC1 first is never a valid GS1-128 symbol.

### 4.2 FNC1 as field separator

FNC1 also serves as a delimiter after **variable-length** AI data fields when another AI follows. Rules:

- **Fixed-length (predefined) AIs**: no separator needed — the scanner knows the exact data length from the AI.
- **Variable-length AIs followed by another AI**: FNC1 **required** between the variable-length data and the next AI.
- **Variable-length AI at the end of the symbol**: no trailing FNC1 needed.
- When transmitted by a scanner, separator FNC1 is output as **ASCII GS** (decimal 29, hex 0x1D).
- FNC1 is **never shown** in human-readable text.

### 4.3 Maximum symbol constraints

- **Maximum data characters**: **48** (includes AI digits, data characters, and FNC1 separators; excludes start character, mandatory leading FNC1, check digit, and stop character).
- **Maximum physical width**: **165.10 mm** (6.5 inches) including quiet zones.

### 4.4 GS1-128 restrictions versus generic Code 128

GS1-128 does **not** use FNC2, FNC4, or any characters with ASCII values above 127. Only FNC1 and FNC3 (for programming scanners) are relevant.

---

## 5. Application Identifiers — formats, lengths, and separator rules

### 5.1 Data type definitions

- **N** = Numeric only (digits 0–9)
- **X** = Alphanumeric (GS1 Character Set 82: digits, uppercase A–Z, lowercase a–z, plus 20 special characters: `! " % & ' ( ) * + , - . / : ; < = > ? _`). **Space is excluded.**

### 5.2 Complete AI reference table

| AI | Name | Format | Data Type | Data Length | Fixed Data? | Predefined Length? | FNC1 Separator Needed? | Check Digit? |
|---|---|---|---|---|---|---|---|---|
| **(01)** | GTIN | N2+N14 | Numeric | 14 fixed | Yes | **Yes** | No | MOD-10 on pos 14 |
| **(02)** | GTIN of contained items | N2+N14 | Numeric | 14 fixed | Yes | **Yes** | No | MOD-10 on pos 14 |
| **(10)** | Batch/Lot Number | N2+X..20 | Alphanumeric | 1–20 variable | No | **No** | **Yes** | None |
| **(11)** | Production Date | N2+N6 | Numeric | 6 fixed | Yes | **Yes** | No | Date validation |
| **(13)** | Packaging Date | N2+N6 | Numeric | 6 fixed | Yes | **Yes** | No | Date validation |
| **(15)** | Best Before Date | N2+N6 | Numeric | 6 fixed | Yes | **Yes** | No | Date validation |
| **(17)** | Expiry Date | N2+N6 | Numeric | 6 fixed | Yes | **Yes** | No | Date validation |
| **(21)** | Serial Number | N2+X..20 | Alphanumeric | 1–20 variable | No | **No** | **Yes** | None |
| **(310x)** | Net Weight (kg) | N4+N6 | Numeric | 6 fixed | Yes | **Yes** | No | None (x = decimal) |
| **(320x)** | Net Weight (lb) | N4+N6 | Numeric | 6 fixed | Yes | **Yes** | No | None (x = decimal) |
| **(37)** | Count of trade items | N2+N..8 | Numeric | 1–8 variable | No | **No** | **Yes** | None |
| **(400)** | Customer Purchase Order | N3+X..30 | Alphanumeric | 1–30 variable | No | **No** | **Yes** | None |
| **(8005)** | Price per unit of measure | N4+N6 | Numeric | 6 fixed | Yes | **No** ⚠️ | **Yes** ⚠️ | None |

**Critical distinction: "Fixed Data" ≠ "Predefined Length."** AI (8005) has a fixed 6-digit data format, but its first two digits ("80") are **not** in the GS1 predefined-length lookup table. Therefore it **still requires FNC1** after its data when followed by another AI. This is a common implementation error.

### 5.3 The predefined-length lookup table

Only AIs whose **first two digits** match this table are predefined-length (no FNC1 separator needed):

| First 2 digits of AI | Total element string length (AI + data) |
|---|---|
| 00 | 20 |
| 01 | 16 |
| 02 | 16 |
| 11, 12, 13, 15, 16, 17 | 8 |
| 20 | 4 |
| 31, 32, 33, 34, 35, 36 | 10 |
| 41 | 16 |

Any AI whose first two digits are absent from this table requires FNC1 after its data (unless it is the last AI in the symbol).

### 5.4 Decimal handling for measure AIs (310x–360x)

The fourth digit of the AI (the "x" in 310**x**) specifies the **implied decimal point position** — i.e., how many of the 6 data digits fall to the right of the decimal.

- AI **3100**: integer (000000–999999 kg)
- AI **3101**: 5+1 format (12345.6 kg)
- AI **3102**: 4+2 format (1234.56 kg)
- AI **3103**: 3+3 format (123.456 kg)
- AI **3104**: 2+4 format (12.3456 kg)
- AI **3105**: 1+5 format (1.23456 kg)

At least 1 digit must remain left of the decimal point, so **x ≤ 5** is practical. The same rule applies to 320x (pounds) and all other measure AIs in the 31xx–36xx range.

---

## 6. Date format rules (YYMMDD)

All date AIs (11, 13, 15, 17) use the **YYMMDD** format — exactly 6 numeric digits.

### 6.1 Century determination — sliding 100-year window

1. Compute candidate year: `candidate = 2000 + YY`.
2. If `candidate > (current_year + 50)`, then `actual_year = candidate − 100`.
3. Otherwise, `actual_year = candidate`.

This maps any YY value to the range **(current_year − 49)** to **(current_year + 50)**. For example, with current_year = 2026: YY=76 → 2076; YY=77 → 1977; YY=00 → 2000.

### 6.2 DD = 00 interpretation

When **DD = 00**, the date means "the last day of the indicated month." This is commonly used when only the month and year are known. For regulated healthcare products as of January 1, 2025, DD = 00 is **no longer valid** — a specific day is required. For non-healthcare applications, DD = 00 should be accepted and interpreted as month-end.

### 6.3 Date validation rules

1. **YY**: 00–99 (all values valid; century determined by sliding window).
2. **MM**: 01–12 (MM = 00 is **invalid**).
3. **DD**: 00–31. When DD ≠ 00, the day must be valid for the given month and year (account for leap years in February). DD = 00 is valid for non-healthcare contexts.
4. The complete date must fall within the 100-year window.

---

## 7. AI combination and ordering rules

### 7.1 Mandatory pairings

| AI | Must appear with | Rule |
|---|---|---|
| **(02)** | **(37)** | AI (02) — GTIN of contained items — **must** be accompanied by AI (37) — count |
| **(37)** | **(02)** or **(8006)** | AI (37) may **only** be used with AI (02) or AI (8006) |

### 7.2 Invalid pairings (must not appear together)

| AI | Conflicts with | Reason |
|---|---|---|
| (01) | (02) | A trade item cannot simultaneously identify its own GTIN and contained items' GTIN |
| (01) | (37) | Count of contained items is only valid with AI (02), not (01) |
| Any AI | Duplicate of itself | Same AI must not appear twice with different values on a single entity |

### 7.3 Implicit associations

All attribute AIs (dates, batch, serial, weights) are attributes of a trade item and should appear alongside the GTIN they modify — typically AI (01) or AI (02).

### 7.4 Encoding order conventions

1. Place **predefined-length (fixed) AIs first**, then **variable-length AIs after**.
2. Place the **GS1 identification key** (typically AI 01) **before** its attributes.
3. Place the **single variable-length AI that appears last** at the end of the symbol to avoid needing a trailing FNC1.
4. If multiple variable-length AIs must be encoded, each except the last requires a trailing FNC1 separator.

---

## 8. GS1-128 string encoding — machine-readable vs. human-readable

### 8.1 Human-Readable Interpretation (HRI)

The text printed near the barcode for human reading. Rules:

- **AIs are enclosed in parentheses**: `(01)09501101530003(17)250101(10)ABC123`
- Parentheses are **not** encoded in the barcode — they are for visual identification only.
- FNC1 characters are **never** shown.
- Start, stop, and Code 128 check characters are **never** shown.
- HRI **must include all encoded data**, in the same sequence as encoded in the barcode.
- An element string (AI + data) must **never be broken** across two lines.
- Preferred font: OCR-B or sans-serif (e.g., Arial). **Bold, italic, narrow variants must not be used.** Minimum character height: **2 mm**.
- HRI is placed **below** the barcode (preferred). May be placed above when space requires it.

### 8.2 Machine-readable format (barcode data stream)

What the scanner reads and transmits:

- **No parentheses** — AIs flow directly into their data.
- The leading FNC1 (after start) identifies GS1-128 and is typically transmitted as the AIM symbology identifier `]C1`.
- Subsequent FNC1 separators are transmitted as **ASCII GS** (decimal 29).
- Start, stop, and Code 128 check characters are **not** transmitted.

**Example encoding of `(01)09501101530003(17)250101(10)ABC123`:**

```
Barcode symbols: [StartC] [FNC1] 01 09 50 11 01 53 00 03 17 25 01 01 [CodeB] [FNC1] 1 0 A B C 1 2 3 [Check] [Stop]
```

Wait — let's be precise. AI (17) has predefined length (6 digits), so no FNC1 is needed after it. AI (10) is variable-length and is the **last** AI, so no trailing FNC1 is needed either. But the transition from the fixed-length AI (17) data to the AI (10) data requires no separator because (17) is predefined-length. However, AI (10) data is alphanumeric, so a switch from Code C to Code B is needed.

Corrected encoding:

```
[StartC] [FNC1] 01 09 50 11 01 53 00 03 17 25 01 01 10 [CodeB] A B C 1 2 3 [Check] [Stop]
```

Here, "01 09 50 11 01 53 00 03" encodes the GTIN as digit pairs in Code C, "17 25 01 01" encodes the date AI and data as digit pairs, "10" encodes the batch AI as a digit pair, then [CodeB] switches to Code B for the alphanumeric batch data "ABC123". The AI (10) is last, so no FNC1 separator follows it.

If AI (10) were **not** last (e.g., another AI followed), an FNC1 would be required after "ABC123" to delimit the variable-length batch field.

---

## 9. Barcode rendering specifications

### 9.1 Physical dimensions

| Parameter | Value |
|---|---|
| **X-dimension (module width)** | Min: **0.495 mm** for general distribution; Max: **1.016 mm** (100% magnification) |
| **Bar height** | Min: **31.75 mm** (1.25 in) for general distribution; absolute minimum 13 mm for non-automated scanning |
| **Maximum symbol width** | **165.10 mm** (6.5 in) including quiet zones |
| **Quiet zone (left)** | Minimum **10× the X-dimension** (10 modules) |
| **Quiet zone (right)** | Minimum **10× the X-dimension** (10 modules) |

### 9.2 Symbol structure

Each Code 128 symbol character = **11 modules** (3 bars + 3 spaces). The stop pattern = **13 modules** (4 bars + 3 spaces). Total symbol width in modules:

```
Total modules = 11 × (start + data_chars + FNC1s + switches + check) + 13 (stop) + 2 × 10 (quiet zones)
```

Or equivalently: `11 × (N + 2) + 2 + 20` where N = number of symbol characters between start and stop (excluding both), and +2 accounts for start and check, +2 for the stop's extra modules, +20 for quiet zones.

### 9.3 Bearer bars

**Not required** for GS1-128. They are required for ITF-14 but optional for GS1-128.

### 9.4 Truncation

**Not recommended.** GS1 discourages reducing bar height below the specified minimums. Truncated symbols score lower verification grades.

---

## 10. Web rendering approach and implementation guidance

### 10.1 Recommended rendering method

**SVG is the recommended output format** for a web application: resolution-independent, scalable, and suitable for both screen display and high-quality print. Bars are rendered as `<rect>` elements with precise x-position and width attributes. The HRI text is rendered as `<text>` elements below the bars.

### 10.2 Module-to-pixel mapping

| Context | Pixels per module | Notes |
|---|---|---|
| Screen preview (96 DPI) | 2–3 px | JsBarcode default is 2px; minimum 1px but 2px recommended |
| Print at 300 DPI | ~6 dots per module | For 0.495 mm X-dimension |
| Print at 600 DPI | ~12 dots per module | For 0.495 mm X-dimension |

**Module width should be an integer multiple of device dots** to avoid sub-pixel rounding artifacts. For a 300 DPI printer targeting 0.495 mm X-dim: 6 dots × (25.4 mm / 300 dots) = 0.508 mm actual X-dim.

### 10.3 JavaScript library options

- **JsBarcode**: Most popular open-source library. Renders to SVG, Canvas, or `<img>`. Supports CODE128 and GS1-128 natively. Key options: `width` (module width in px), `height`, `displayValue`, `font`, `textMargin`, `fontSize`.
- **bwip-js**: Comprehensive barcode library supporting all major symbologies including GS1-128.

### 10.4 Minimum quality requirements (ISO/IEC 15416)

For GS1-128, the minimum acceptable verification grade is **1.5 (C)**. The grading system evaluates eight parameters per scan line (symbol contrast, modulation, defects, decodability, quiet zones, edge contrast, minimum reflectance, and decode success). The overall grade is the average of 10 scan lines, with each line graded by its worst-performing parameter.

For screen-only applications, aim for **black bars (#000000) on white background (#FFFFFF)** for maximum contrast. Do not use colors that reduce scan reliability.

---

## 11. Complete validation flowchart — from input to GS1-128

This section describes the full validation and encoding pipeline a developer must implement.

### Step 1: Accept and validate the base identifier

**If input is EAN-13:**
1. Verify exactly 13 digits, all numeric.
2. Verify check digit (modulo-10 with weights 1,3,1,3… from left).
3. If valid, pad to 14 digits for AI (01): prepend `0` to make `0XXXXXXXXXXXXX`.

**If input is GTIN-14:**
1. Verify exactly 14 digits, all numeric.
2. Verify check digit (modulo-10 with weights 3,1,3,1… from left).
3. Use all 14 digits directly for AI (01).

**If deriving GTIN-14 from EAN-13 + packaging indicator:**
1. Validate the EAN-13 as above.
2. Accept packaging indicator digit (1–8, or 9 for variable measure, or 0).
3. Strip EAN-13 check digit → 12 digits.
4. Prepend indicator → 13 digits.
5. Calculate GTIN-14 check digit (weights 3,1,3,1… from left) → append.
6. Result: 14-digit GTIN-14.

### Step 2: Collect and validate Application Identifier data

For each AI the user wants to encode:

1. **Validate the AI code** against the known AI table.
2. **Validate data type**: numeric AIs accept digits only; alphanumeric AIs accept GS1 Character Set 82.
3. **Validate data length**: must not exceed the AI's maximum. For fixed-length AIs, must be exactly the specified length.
4. **Validate dates** (AIs 11, 13, 15, 17): YYMMDD format, MM in 01–12, DD in 00–31 (with month/year validity checks).
5. **Validate GTIN check digits** in AIs (01) and (02): the 14th digit must pass modulo-10.
6. **Validate AI combination rules**: (02) requires (37); (37) requires (02); (01) and (02) cannot coexist; no duplicate AIs with different values.
7. **Validate decimal indicator** for measure AIs: the x in 310x must be 0–9, practically 0–5.

### Step 3: Construct the element string

1. **Order AIs**: fixed/predefined-length AIs first, variable-length AIs after. Place the last variable-length AI at the end.
2. **Build the machine-readable string**: concatenate AI codes and data without parentheses. Insert FNC1 after each variable-length AI's data **except the last one**.
3. **Build the HRI string**: concatenate `(AI)data` for each AI in the same order. No FNC1 representation.

### Step 4: Encode into Code 128 symbols

1. **Choose the start character**: Start C if the data begins with ≥4 consecutive digits (very common for GS1-128). Otherwise Start B. Start A only if control characters are present (rare in GS1-128).
2. **Encode FNC1** as the first symbol (value 102).
3. **Encode each character**, switching between Code C (for digit pairs), Code B (for alphanumeric), and Code A (for control chars) as needed. Use Code C for maximum compactness on numeric runs.
4. **Insert FNC1 separators** (value 102) between variable-length fields as required.
5. **Calculate the modulo-103 check digit** over the entire symbol character sequence.
6. **Append the check character** and the stop pattern.
7. **Verify** total data characters ≤ 48 and total symbol width ≤ 165.10 mm.

### Step 5: Render the barcode

1. Look up bar patterns for each symbol character (11 modules each, stop = 13 modules).
2. Render bars and spaces at the chosen X-dimension and bar height.
3. Add quiet zones (≥10 modules) on both sides.
4. Render HRI text below the barcode in OCR-B or Arial, ≥2 mm height, with AIs in parentheses.

---

## Conclusion

Three algorithmic pillars underpin this entire system: the **GS1 modulo-10 check digit** (with its right-to-left weighting rule that produces different left-to-right patterns for 13-digit vs. 14-digit numbers), the **Code 128 modulo-103 check digit** (positionally weighted sum of all symbol character values), and the **predefined-length lookup table** (which determines whether FNC1 separators are needed — a distinction that is **not** the same as whether the data format itself is fixed-length, as AI 8005 demonstrates).

The most error-prone implementation areas are: confusing "fixed data format" with "predefined length" for separator logic; failing to recalculate the GTIN-14 check digit when deriving from EAN-13 (since the check digit changes); omitting FNC1 between variable-length AIs; and incorrect Code C optimization (particularly handling odd-count digit sequences). A developer who correctly implements the validation chain — EAN-13 input → GTIN-14 derivation → AI data validation → element string construction → Code 128 symbol encoding → rendering — will produce standards-compliant GS1-128 barcodes that scan reliably across the global supply chain.
