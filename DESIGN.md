# DESIGN.md — Wat Financial Dashboard
> Extracted from Stitch Project · `projects/13353379341949391401` · Last updated: 2026-04-14

---

## 1. Creative North Star: "The Sacred Ledger"

This design system moves away from the cold, industrial feel of traditional accounting software. Instead, it adopts a **"Sacred Ledger"** philosophy — an editorial-inspired aesthetic that balances the weight of financial responsibility with the serenity of a temple environment.

We achieve this through **Organic Professionalism**: using expansive white space, a sophisticated serif-led typography hierarchy, and a "No-Line" architecture. The goal is to create a digital environment that feels as intentional and permanent as stone carvings, yet as fluid and modern as a high-end financial publication.

---

## 2. Color Palette

### Theme Configuration
| Property            | Value       |
|---------------------|-------------|
| Color Mode          | Light       |
| Color Variant       | Fidelity    |
| Override Primary    | `#1E293B`   |
| Override Secondary  | `#10B981`   |
| Override Tertiary   | `#EF4444`   |
| Override Neutral    | `#F8FAFC`   |
| Custom Color        | `#1E293B`   |

---

### Primary Palette
| Token                    | Hex Value   | Usage                                      |
|--------------------------|-------------|--------------------------------------------|
| `primary`                | `#091426`   | Main brand color, authority anchor         |
| `primary_container`      | `#1E293B`   | Hero gradients, nav rail backgrounds       |
| `primary_fixed`          | `#D8E3FB`   | Light tinted surfaces                      |
| `primary_fixed_dim`      | `#BCC7DE`   | Muted accents                              |
| `on_primary`             | `#FFFFFF`   | Text/icons on primary                      |
| `on_primary_container`   | `#8590A6`   | Secondary text on primary container        |
| `on_primary_fixed`       | `#111C2D`   | Text on primary_fixed                      |
| `on_primary_fixed_variant` | `#3C475A` | Variant text on primary_fixed              |
| `inverse_primary`        | `#BCC7DE`   | Inverse contexts                           |

### Secondary Palette (Income / Positive)
| Token                     | Hex Value   | Usage                                      |
|---------------------------|-------------|--------------------------------------------|
| `secondary`               | `#006C49`   | Income indicators, positive values         |
| `secondary_container`     | `#6CF8BB`   | Chip backgrounds for positive values       |
| `secondary_fixed`         | `#6FFBBE`   | Subtle positive background chips           |
| `secondary_fixed_dim`     | `#4EDEA3`   | Dimmed positive accents                    |
| `on_secondary`            | `#FFFFFF`   | Text on secondary                          |
| `on_secondary_container`  | `#00714D`   | Text on secondary container                |
| `on_secondary_fixed`      | `#002113`   | Text on secondary_fixed                    |
| `on_secondary_fixed_variant` | `#005236` | Variant text on secondary_fixed           |

### Tertiary Palette (Expense / Negative)
| Token                       | Hex Value   | Usage                                    |
|-----------------------------|-------------|------------------------------------------|
| `tertiary`                  | `#330002`   | Deep error tones                         |
| `tertiary_container`        | `#5A0008`   | Expense indicator containers             |
| `tertiary_fixed`            | `#FFDAD7`   | Light expense backgrounds                |
| `tertiary_fixed_dim`        | `#FFB3AD`   | Dimmed expense accents                   |
| `on_tertiary`               | `#FFFFFF`   | Text on tertiary                         |
| `on_tertiary_container`     | `#FF5250`   | Text on tertiary container               |
| `on_tertiary_fixed`         | `#410004`   | Text on tertiary_fixed                   |
| `on_tertiary_fixed_variant` | `#930013`   | **Use for expense text** (not pure red)  |

### Surface & Background
| Token                      | Hex Value   | Usage                                     |
|----------------------------|-------------|-------------------------------------------|
| `background`               | `#F7F9FB`   | Page background                           |
| `surface`                  | `#F7F9FB`   | Base canvas                               |
| `surface_bright`           | `#F7F9FB`   | Bright surface variant                    |
| `surface_dim`              | `#D8DADC`   | Dimmed surface                            |
| `surface_container_lowest` | `#FFFFFF`   | Elevated cards, focal points              |
| `surface_container_low`    | `#F2F4F6`   | Content groupings, list backgrounds       |
| `surface_container`        | `#ECEEF0`   | Mid-level containers                      |
| `surface_container_high`   | `#E6E8EA`   | Higher-level containers                   |
| `surface_container_highest`| `#E0E3E5`   | Highest level (e.g. search bars)          |
| `surface_variant`          | `#E0E3E5`   | Surface variant                           |
| `surface_tint`             | `#545F73`   | Tint overlay                              |
| `on_background`            | `#191C1E`   | Text on background                        |
| `on_surface`               | `#191C1E`   | Primary text                              |
| `on_surface_variant`       | `#45474C`   | Secondary / descriptive text              |
| `inverse_surface`          | `#2D3133`   | Dark surface (tooltips, overlays)         |
| `inverse_on_surface`       | `#EFF1F3`   | Text on inverse surface                   |

### Outline & Error
| Token                | Hex Value   | Usage                              |
|----------------------|-------------|------------------------------------|
| `outline`            | `#75777D`   | Default outline                    |
| `outline_variant`    | `#C5C6CD`   | Ghost borders (use at 15% opacity) |
| `error`              | `#BA1A1A`   | Error state                        |
| `error_container`    | `#FFDAD6`   | Error state background             |
| `on_error`           | `#FFFFFF`   | Text on error                      |
| `on_error_container` | `#93000A`   | Text on error container            |

---

## 3. Typography

### Font Families
| Role            | Font Family  | Stitch Token  |
|-----------------|--------------|---------------|
| **Headline**    | Manrope      | `MANROPE`     |
| **Body**        | Noto Serif   | `NOTO_SERIF`  |
| **Label / UI**  | Inter        | `INTER`       |

### Type Scale & Usage
| Role                 | Font       | Size      | Weight | Usage                                      |
|----------------------|------------|-----------|--------|--------------------------------------------|
| `display-lg`         | Manrope    | ~3.5rem   | 700    | Current Balance, KPI hero numbers          |
| `headline-lg`        | Manrope    | ~2rem     | 700    | Section headings, financial totals          |
| `title-lg`           | Noto Serif | ~1.375rem | 600    | Card titles, date groupings in ledger       |
| `title-sm`           | Noto Serif | ~0.875rem | 600    | Secondary action labels                    |
| `body-md`            | Noto Serif | ~1rem     | 400    | Transaction details, narrative content     |
| `label-md`           | Inter      | ~0.75rem  | 500    | Table headers, metadata, chips             |

> **Lao Script Rule:** Ensure a minimum `line-height` multiplier of **1.5×** to prevent stacking of tone marks.

---

## 4. Shape & Spacing

| Property        | Value                |
|-----------------|----------------------|
| Roundness       | `ROUND_FOUR` (4px)   |
| Spacing Scale   | `1` (Default)        |
| Card Padding    | Minimum `24px` (1.5rem) |
| Section Gap     | `32px` vertical white space |

---

## 5. Elevation & Depth

> **The "No-Line" Rule:** 1px solid borders are **prohibited** for sectioning. Use only background color shifts and tonal nesting.

### Layering Principle
```
Level 0 → surface           (#F7F9FB)  — The floor / page background
Level 1 → surface_container_low (#F2F4F6)  — Content groupings & list backgrounds
Level 2 → surface_container_lowest (#FFFFFF) — Interactive cards, elevated focal points
```

### Shadow Guidelines
- **Ambient Shadows:** `box-shadow: 0 0 32px 0 rgba(9, 20, 38, 0.04)` — tinted with `primary`, not black
- **Ghost Border Fallback:** `1px solid rgba(197, 198, 205, 0.15)` — only for accessibility-critical separations
- **Glassmorphism (Nav Rail):** `background: rgba(247, 249, 251, 0.8); backdrop-filter: blur(20px);`

---

## 6. Key Components

### Navigation Rail (Glass)
```css
background: rgba(247, 249, 251, 0.80);
backdrop-filter: blur(20px);
-webkit-backdrop-filter: blur(20px);
```

### Hero Gradient (Dashboard Summary Cards)
```css
background: linear-gradient(135deg, #091426 0%, #1E293B 100%);
```

### Primary CTA Button
```css
background: #091426;
color: #FFFFFF;
border-radius: 4px;        /* ROUND_FOUR */
box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
```

### Secondary / Ghost Button
```css
background: transparent;
border: none;
color: #006C49;             /* secondary */
font-family: 'Noto Serif', serif;
font-size: 0.875rem;        /* title-sm */
font-weight: 600;
```

### Financial Indicator — Positive (Income)
```css
color: #006C49;             /* secondary */
background: #6FFBBE;        /* secondary_fixed */
```

### Financial Indicator — Negative (Expense)
```css
color: #930013;             /* on_tertiary_fixed_variant */
background: #FFDAD7;        /* tertiary_fixed */
```

### Ledger Table Row
```css
/* No divider lines. Use spacing instead. */
padding-bottom: 32px;
font-family: 'Noto Serif', serif;  /* body-md */
```

---

## 7. Do's & Don'ts

### ✅ Do
- Use `display-lg` (Manrope 700) for the **Current Balance** — it must be the undisputed focal point
- Use **background shifts** to imply containment, never borders
- Use **glassmorphism** for the nav rail and floating panels
- Embrace **asymmetry** — let charts bleed off container edges for scale
- Apply a `1.5×` line-height minimum for Lao script to prevent tone mark stacking
- Use `secondary` (#006C49) for all positive/income indicators

### ❌ Don't
- ❌ Use 100% opaque borders — they create "visual cages"
- ❌ Use standard "Success Green" — always use `secondary` (#006C49) for a professional emerald, not neon
- ❌ Overcrowd the interface — if a screen feels full, increase `surface` spacing
- ❌ Use pure black for text — always use `on_surface` (#191C1E)
- ❌ Use heavy drop shadows — ambient shadows only (`4%` opacity max)
- ❌ Use bright/pure red for expenses — use `on_tertiary_fixed_variant` (#930013) for a richer, royal tone

---

## 8. Project Metadata

| Field           | Value                            |
|-----------------|----------------------------------|
| Project ID      | `13353379341949391401`           |
| Project Type    | `TEXT_TO_UI_PRO`                 |
| Device Target   | Desktop (1280px)                 |
| Visibility      | Public                           |
| Origin          | Stitch                           |
| Last Updated    | 2026-04-14                       |
