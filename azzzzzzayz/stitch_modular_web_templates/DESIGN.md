```markdown
# The Design System: High-End Editorial Specification

## 1. Overview & Creative North Star: "The Digital Atrium"
This design system rejects the "standard dashboard" aesthetic in favor of **The Digital Atrium**—a philosophy of light, space, and structural transparency. While the Backoffice requires data density and the Frontoffice demands engagement, both are unified by a "High-End Editorial" lens. We move away from rigid, boxed-in grids toward an airy, layered environment where information breathes.

**The Signature Look:** We break the "template" feel through **Intentional Asymmetry** (e.g., placing oversized display type against tight functional data) and **Tonal Depth**. This isn't just a tool; it’s a curated experience that feels as intentional as a premium print magazine.

---

## 2. Color Philosophy & Surface Architecture
We utilize a sophisticated Material-based palette to move beyond basic green and blue. 

### The "No-Line" Rule
**Explicit Instruction:** Designers are prohibited from using 1px solid borders for sectioning. Structural boundaries must be defined solely through background color shifts or subtle tonal transitions.
*   *Correct:* A `surface-container-low` sidebar sitting against a `surface` main content area.
*   *Incorrect:* A `#DDDDDD` border line separating the sidebar.

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers—stacked sheets of frosted glass or fine paper.
*   **Backoffice Depth:** Use `surface-container` for the main canvas, and `surface-container-lowest` (pure white) for data tables or form containers to make them "pop" as the primary focus.
*   **Frontoffice Depth:** Use `surface-bright` for hero sections, transitioning into `surface-container-low` for secondary content sections.

### The "Glass & Gradient" Rule
To inject "soul" into the professional layout:
*   **Glassmorphism:** For floating navigation bars or modal overlays, use `surface` at 70% opacity with a `24px` backdrop-blur.
*   **Signature Textures:** Main CTAs and Hero backgrounds should utilize a subtle linear gradient: `primary` (#006e1c) to `primary-container` (#4caf50) at a 135-degree angle. This prevents the "flat-and-cheap" look.

---

## 3. Typography: Editorial Authority
We pair **Manrope** (Display/Headline) for personality with **Inter** (Body/Label) for clinical precision.

*   **Display & Headline (Manrope):** These are your "Editorial Voices." Use `display-lg` for Frontoffice hero sections with tight letter-spacing (-0.02em) to create an authoritative, premium feel.
*   **Title & Body (Inter):** The "Functional Voice." Inter provides maximum readability in the Backoffice. Use `title-sm` for table headers in all-caps with 0.05em tracking to differentiate from data.
*   **The Hierarchy Gap:** Create drama by pairing a `headline-lg` title with a `body-sm` caption nearby. This high-contrast scale is the hallmark of high-end design.

---

## 4. Elevation & Depth: Tonal Layering
Traditional shadows are often "dirty." We achieve depth through **Tonal Layering**.

*   **The Layering Principle:** Instead of a shadow, place a `surface-container-lowest` card on a `surface-container-high` background. The natural contrast creates a "soft lift."
*   **Ambient Shadows:** If a floating element (like a dropdown) requires a shadow, use the `on-surface` color at 6% opacity with a 32px blur and 8px Y-offset. This mimics natural light.
*   **The "Ghost Border" Fallback:** For accessibility in form fields, use `outline-variant` at 20% opacity. Never use 100% opaque lines.

---

## 5. Component Logic

### Buttons
*   **Primary:** Gradient of `primary` to `primary_container`. Border-radius: `md` (0.375rem).
*   **Secondary:** Solid `secondary_fixed`. Text color: `on_secondary_fixed`. No border.
*   **Danger:** `error` container with `on_error_container` text. Use only for destructive actions.
*   **Interaction:** On hover, elevate the button by 2px and increase gradient saturation.

### Input Fields
*   **Style:** Background `surface_container_low`. No visible border until focus.
*   **Focus State:** A 2px "Ghost Border" using `primary` at 40% opacity.
*   **Labels:** Always use `label-md` in `on_surface_variant`.

### Cards & Lists
*   **Constraint:** **Forbid dividers.** Use vertical white space from the spacing scale (e.g., 24px or 32px) to separate list items.
*   **Backoffice Tables:** Use alternating rows of `surface` and `surface_container_low` instead of grid lines.
*   **Frontoffice Cards:** Use `surface_container_lowest` with a `lg` (0.5rem) corner radius and a 4% ambient shadow.

### Additional Signature Components
*   **The Module Switcher (Backoffice):** A glassmorphic top-bar element using `tertiary_container` (Pink) as a subtle glow indicator for the active module.
*   **Data Summary Chips:** Small, high-contrast pills using `secondary_fixed_dim` to highlight key metrics within tables.

---

## 6. Do’s and Don’ts

### Do:
*   **Embrace Negative Space:** If a section feels crowded, remove content or increase the padding. High-end design is defined by what you leave out.
*   **Use Tonal Transitions:** Change the background color of the page as the user scrolls from a "Story" section to a "Functional" section.
*   **Center Typography for Impact:** In the Frontoffice, use centered `display-sm` type to break the left-aligned monotony of the Backoffice.

### Don't:
*   **No "Pure Black" Shadows:** Never use `#000000` for shadows; always tint them with your `on_surface` color.
*   **No Grid Lines:** If you feel the urge to draw a line to separate two things, use an 8px color shift or 40px of white space instead.
*   **Don't Mix Radii:** If cards are `lg` (0.5rem), do not make buttons `full` (pill-shaped). Keep the roundedness scale consistent to maintain the "Professional & Modular" promise.

---

**Director's Closing Note:** This design system is a living framework. It succeeds when it feels like a bespoke gallery rather than a generic software suite. Prioritize the "feel" of the space between the elements as much as the elements themselves.```