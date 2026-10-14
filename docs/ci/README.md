# Kompetent CI

The binding corporate design for this project.

| File | Purpose |
| --- | --- |
| `Kompetent CI (Referenz).dc.html` | Binding reference. Do not edit. Opens in the browser together with `support.js`. |
| `Kompetent Styleguide (offline).html` | Self-contained offline copy of the same styleguide. |
| `support.js` | Runtime for the reference file. |

Colour values come from the federal corporate design styleguide (March 2025). Claim: „Nicht zuständig war gestern.“ / EN: "Not my department" was yesterday. Kompetent is today.

## Where the CI lives in the code

| What | Where |
| --- | --- |
| Colour tokens (`kp-*`), font, radius, focus state | `resources/css/app.css` |
| Fonts (Inter, Noto Sans Arabic) | `vite.config.js` |
| Logo and flag | `resources/views/components/kompetent-logo.blade.php`, `kompetent-flag.blade.php` |
| Brand name and web address | `config/app.php` (`app.brand`, `app.brand_domain`, env `APP_BRAND`) |
| Rules for developers and AI agents | `.ai/guidelines/kompetent-ci.md` |

The existing Tailwind palettes `red`, `slate`, `emerald`, `amber` and `blue` are remapped onto CI values in `app.css`, so older views follow the CI. New code should use the `kp-*` tokens directly.
