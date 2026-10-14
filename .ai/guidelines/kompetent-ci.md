# Kompetent CI (binding)

Every UI in this project follows the Kompetent CI. Reference: `docs/ci/Kompetent CI (Referenz).dc.html` (do not edit). Read it before designing anything new and take values from it. Never invent new colours or fonts.

## Tokens

Use the `kp-*` Tailwind tokens from `resources/css/app.css`, not raw hex values or Tailwind default colours.

- Flag red `kp-flag` (#E1320F): logo, flag element, large areas only. Never for text, warnings or errors.
- Text red `kp-textred` (#B92B06): text accents and links (underlined).
- Black `kp-black`, white, blue-grey `kp-bluegrey` / `-60` / `-30`, sand `kp-sand` / `-60` / `-30`.
- Interaction: navy `kp-navy` (#002D66) for primary buttons, user messages, inputs, selected chips; hover `kp-darkblue`.
- States (always with a text label, colour is never the only signal): verified `kp-verified` (green), not verified `kp-unverified` (ochre), no trusted source `kp-nosource` (wine red), handover `kp-handover` (navy).

## Type and shape

- Inter only: Light 300 for taglines, Regular 400 for logo and body, Semibold 600 for emphasis, Bold 700 for headings. Noto Sans Arabic for Arabic.
- Sentence case, no all-caps. Emphasis through weight, size or text red.
- Square, flat surfaces (radius 0); no shadows or gradients. Pills and dots may stay round.
- Logo: `<x-kompetent-logo />` (flag + word mark, tracking +60, shared baseline). Web address without "www"/"https://" in text red.

## Accessibility (WCAG 2.2 AA minimum)

- Text contrast at least 4.5 : 1. Focus: 3px navy outline with 3px offset, never remove it.
- Click targets at least 44 × 44 px, fully keyboard operable, text zoom to 200 %.
- Respect `prefers-reduced-motion`. New chat messages are announced via a live region.

## AI disclosure and answers

- Every AI answer carries the visible label "KI-Antwort" above the text.
- Deadlines, fees and documents come only from the fact database and carry the green label "Geprüfte Angabe".
- Every answer names its source (Austrian authority sources only), links in text red.
- "Mit einer Person sprechen" is always visible and is the last of at most four next-step buttons.
- If no verified source exists, say so ("Dazu habe ich keine geprüfte Quelle.") and offer a person. Never guess.
- Answer pattern: confirm understanding first, then A steps (where, by when), B documents per step, C sources, D next-step buttons.

## Language

- Formal "Sie", active voice, short sentences, answer first, every answer ends with a next step.
- Austrian German: Jänner, Meldezettel, Magistrat, heuer. Never "nicht zuständig" or "leider" without an alternative.
- Answer in the user's language; official German terms stay in brackets. Language names in their own language, no country flags for languages. Arabic mirrors the layout, never the logo or flag.
