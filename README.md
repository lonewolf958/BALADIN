# Baladin Zanzibar Beach Hotel — website

Server-rendered **PHP** website for ordinary cPanel/Apache hosting. No database, no Node.js, no build step, no third-party scripts, no cookies.
Room and restaurant-table **enquiries** are prepared in the visitor's browser and handed to **WhatsApp (first choice)** or **email (second choice)** — the visitor presses Send. Nothing is stored or sent by the website.

## What was done to the supplied project
The supplied `index.php`, `includes/site.php`, `includes/pages.php`, CSS, JS, `.htaccess`, sitemap and README were kept as the base (same clean URLs, same copy, same look), then refactored:

| Problem found | Fix |
|---|---|
| Contact details had to be entered in **two places** (`site.php` and `site.js`) | One file: `config/site.php`. PHP prints them into the page; JS reads them. |
| Room text repeated on home, rooms and room pages | `config/rooms.php` is the single source; room pages, cards and form options are generated from it. |
| Everything in two big files | Split into `config/`, `includes/`, `pages/`. |
| Canonical URL built from `REQUEST_URI` (duplicate-content risk), no redirects | Router builds canonical from the page list; `/rooms` → `/rooms/` (301), `//`, `/index.php` normalised. |
| Favicon pointed to a non-existent path | Fixed (`assets/images/favicon.svg`). |
| Hotel structured data always on, unverified | Off until `structured_data_verified` is set to `true`. |
| Static `sitemap.xml` that could go stale | Generated from the page list at `/sitemap.xml`. |
| Google Fonts loaded from Google (third-party, privacy) | Removed; system font stack. See "Fonts". |
| Placeholders only; hero video required editing PHP | Missing images/video fall back gracefully; drop files in place and they appear. |
| One combined form; WhatsApp/email opened immediately | Two separate forms (room / table), a **review step**, then WhatsApp (primary) / email (secondary), plus "copy message". |
| `reverse` option precedence bug in `render_section()`, `&` unescaped in footer, `:has()`-only header styling | Fixed. |
| No gallery, enquiry page, 404 template, real privacy/terms | Added `/gallery/`, `/enquire/`, `pages/404.php`, full Privacy Policy and Terms templates. |

**Pages** (14 public URLs): Home `/`, Rooms `/rooms/`, room pages `/rooms/suite-with-seaview/` and `/rooms/bungalow-with-seaview/`, Restaurant `/restaurant/`, Menu `/restaurant/menu/`, Facilities (the "Hotel" page) `/facilities/`, Excursions, Gallery, About, Contact & FAQs, Booking enquiries `/enquire/`, Privacy `/privacy/`, Terms `/terms/`. The original structure was kept: there was no separate "Hotel" page, so Facilities + About fill that role. Privacy/Terms live at `/privacy/` and `/terms/` (templates `pages/privacy-policy.php`, `pages/terms.php`).

## Folder map
```
index.php              front controller (routing, redirects, sitemap, 404)
.htaccess              clean URLs, security headers, caching, blocks private folders
robots.txt
config/site.php        ★ contact details, domain, social, hours, switches, nav, form options
config/pages.php       ★ text of each page (+ SEO title/description)
config/rooms.php       ★ rooms: text, details, images, gallery
includes/              helpers, layout (header/hero/footer), sections, enquiry form
pages/                 templates: room, 404, privacy-policy, terms
assets/css/styles.css  all styling (colours/fonts in :root at the top)
assets/js/site.js      menu, hero video, enquiry forms
assets/images/…        photographs      assets/media/…  hero video
```
Never edit anything outside these to change content. `config/`, `includes/` and `pages/` cannot be opened from a browser.

## Requirements
PHP **8.1+** (tested on 8.3), Apache with `mod_rewrite` and `.htaccess` overrides enabled (standard on cPanel). Optional: `mod_headers`, `mod_deflate`, `mod_expires`. HTTPS (AutoSSL). The site must sit at the **domain root**, not a subfolder.

## Deploy with cPanel File Manager (no SSH)
1. The zip is `baladin-zanzibar-website.zip` and extracts straight into the current folder. In cPanel → **File Manager**, open the domain's document root (usually `public_html`). Back up anything already there (select all → Compress).
2. **Upload** the zip, then **Extract** it. Make sure `index.php`, `.htaccess`, `config/`, `includes/`, `assets/` sit **directly** in the document root (move them up if extraction made a subfolder). Enable *Show Hidden Files* in File Manager → Settings so you can see `.htaccess`.
3. cPanel → **MultiPHP Manager**: choose PHP 8.1 or newer for the domain.
4. cPanel → **SSL/TLS Status**: run AutoSSL, confirm `https://` works.
5. Edit `config/site.php` (right-click → Edit) — see the launch list below.
6. Upload photos/video (below).
7. When HTTPS works, uncomment the HTTPS redirect block and (optionally) HSTS line in `.htaccess` and set your real domain there.
8. Run the test checklist.

## Launch checklist — every value to replace
In **`config/site.php`**:
- [ ] `domain` — final production URL (currently `https://www.baladinzanzibar.com`, taken from the supplied files)
- [ ] `whatsapp_number` — international digits only (no `+`/spaces). **Required for the WhatsApp button**
- [ ] `booking_email` — **required for the email button**
- [ ] `phone_display`
- [ ] `address` / `address_locality` — currently "Michamvi–Pingwe, Zanzibar, Tanzania" from the supplied files; add the full street/plot address if wanted
- [ ] `instagram`, `facebook` (leave empty to hide)
- [ ] `restaurant_hours` — e.g. `['Every day' => '12:00 – 21:30']`; and make `form.table_times` match
- [ ] `privacy_email`, `privacy_effective_date`, `terms_effective_date`
- [ ] `structured_data_verified` → `true` only after the hotel confirms name/address/phone
- [ ] `show_draft_notes` → **`false`** and `show_placeholder_hints` → **`false`** (hides yellow reminders and file-path hints)

Also: [ ] `robots.txt` Sitemap line (if the domain differs) · [ ] `.htaccess` HTTPS block domain · [ ] verify room facts in `config/rooms.php` (sizes, beds, capacity, amenities came from the old website) · [ ] real menu, facilities, excursions, About story, FAQ answers in `config/pages.php` · [ ] hotel to supply booking/cancellation/payment terms and have a local adviser review `pages/terms.php` and `pages/privacy-policy.php` (all yellow `[…]` items).
Search the files for `todo` / `[` markers on the live site: any yellow text means something is still missing.

## Photos and video
Nothing is invented. A missing file shows a neutral placeholder; the site never breaks.
- **Hero video:** upload `assets/media/hero-video.mp4` (and optionally `hero-video.webm`). Keep it short (10–20 s), muted, ideally under ~6 MB, H.264. It is skipped for visitors who prefer reduced motion.
- **Hero poster:** `assets/images/hero-poster.jpg` (~1920×1080). Shown first, and if the video is missing or blocked.
- **Share image:** `assets/images/og-share.jpg` (1200×630) for link previews.
- **Page header photos (optional):** `assets/images/heroes/<page>.jpg`, e.g. `heroes/rooms.jpg`, `heroes/restaurant.jpg`, `heroes/facilities.jpg`, `heroes/excursions.jpg`, `heroes/gallery.jpg`, `heroes/about.jpg`, `heroes/contact.jpg`, `heroes/enquire.jpg`, `heroes/menu.jpg`, `heroes/privacy.jpg`, `heroes/terms.jpg`, `heroes/suite-with-seaview.jpg`, `heroes/bungalow-with-seaview.jpg`.
- **Content photos:** use the exact filenames below (JPG, about 1600 px wide, compressed; WebP is fine if you change the extension in the config). Image width/height are read automatically (prevents layout jumps); below-the-fold images lazy-load.
- **Change a photo/path/alt text:** edit the `image` / `alt` lines in `config/pages.php` or `config/rooms.php`. **Add a gallery photo:** add one line to the `/gallery/` list. **Add a room:** copy a block in `config/rooms.php`.
- Use only photographs you own or are licensed to use, and write alt text that describes what is really in the picture.

Expected files (all optional until you have them):

| File (inside `assets/images/`) | Suggested alt text (edit in config) |
|---|---|
| `excursions/home-card.jpg` | Exploring Zanzibar |
| `excursions/jozani-forest.jpg` | Jozani Forest, Zanzibar |
| `excursions/ocean.jpg` | The ocean off Zanzibar |
| `excursions/spice-tour.jpg` | A spice tour in Zanzibar |
| `excursions/stone-town.jpg` | Stone Town, Zanzibar |
| `facilities/beach.jpg` | The beach at Baladin Zanzibar |
| `facilities/guest-comforts.jpg` | Guest comforts at Baladin Zanzibar |
| `facilities/pool.jpg` | The infinity pool at Baladin Zanzibar |
| `facilities/wellness.jpg` | Jacuzzi and wellness at Baladin Zanzibar |
| `gallery/gallery-01.jpg` | The beach at Baladin Zanzibar |
| `gallery/gallery-02.jpg` | The pool at Baladin Zanzibar |
| `gallery/gallery-03.jpg` | The restaurant at Baladin Zanzibar |
| `gallery/gallery-04.jpg` | A seaview room at Baladin Zanzibar |
| `gallery/gallery-05.jpg` | Sunset over the Zanzibar coast |
| `gallery/gallery-06.jpg` | Food served at Baladin Zanzibar |
| `heroes/about-card.jpg` | Baladin Zanzibar Beach Hotel |
| `heroes/about.jpg` | Baladin Zanzibar Beach Hotel |
| `heroes/location.jpg` | The coast near Baladin Zanzibar |
| `restaurant/atmosphere.jpg` | The restaurant at Baladin Zanzibar |
| `restaurant/drinks.jpg` | Drinks at Baladin Zanzibar |
| `restaurant/food.jpg` | A dish served at Baladin Zanzibar |
| `restaurant/home-feature.jpg` | Dining at Baladin Zanzibar |
| `rooms/bungalow-with-seaview/amenities.jpg` | Amenities in the Bungalow with Seaview |
| `rooms/bungalow-with-seaview/beds.jpg` | Sleeping areas in the Bungalow with Seaview |
| `rooms/bungalow-with-seaview/breakfast.jpg` | Breakfast at Baladin Zanzibar |
| `rooms/bungalow-with-seaview/cover.jpg` | Bungalow with Seaview at Baladin Zanzibar |
| `rooms/bungalow-with-seaview/gallery-1.jpg` | Bungalow with Seaview interior |
| `rooms/bungalow-with-seaview/gallery-2.jpg` | Bungalow upper level |
| `rooms/bungalow-with-seaview/gallery-3.jpg` | Sea view from the Bungalow |
| `rooms/suite-with-seaview/amenities.jpg` | Amenities in the Suite with Seaview |
| `rooms/suite-with-seaview/beds.jpg` | Beds in the Suite with Seaview |
| `rooms/suite-with-seaview/breakfast.jpg` | Breakfast at Baladin Zanzibar |
| `rooms/suite-with-seaview/cover.jpg` | Suite with Seaview at Baladin Zanzibar |
| `rooms/suite-with-seaview/gallery-1.jpg` | Suite with Seaview interior |
| `rooms/suite-with-seaview/gallery-2.jpg` | Suite with Seaview bathroom |
| `rooms/suite-with-seaview/gallery-3.jpg` | Sea view from the Suite |


## Editing content
- Contact, hours, social, nav, form options: `config/site.php`
- Page text and SEO title/description: `config/pages.php` (`title`, `description`)
- Rooms: `config/rooms.php`
- Colours: `:root` at the top of `assets/css/styles.css`
- Menu items: in `/restaurant/menu/` give an item a `'dishes' => ['Dish — price', …]` list to replace the "to be added" line.
Keep quotes balanced (`'like this'`; write `It\'s` or use “curly” quotes) — a typo in a PHP file shows a blank page; undo your last edit to recover.

## How enquiries work (and their limits)
1. Visitor picks **Room enquiry** or **Restaurant table enquiry** (separate fields), fills the form; errors are announced and the field focused.
2. A **review** screen shows the exact message.
3. **Send enquiry via WhatsApp** (prominent) opens `https://wa.me/<number>?text=<encoded message>`; **Send enquiry via email** opens `mailto:` with subject and body. The visitor must press Send. The site never claims anything has been sent, and states clearly it is an enquiry, not a booking.
4. A **Copy message** button helps if WhatsApp/email are not installed.
Limits: `wa.me` needs WhatsApp (app or web); `mailto:` needs a configured email app on the visitor's device (many webmail users have none — hence the copy button); very long messages can be truncated by some apps (inputs are length-limited); you receive no notification until the visitor actually sends; there is no record of abandoned enquiries; the website cannot check availability. If you later need server-side storage, that changes the privacy policy — update it first.

## SEO
Unique title/description/canonical per page, Open Graph, `robots.txt`, auto-generated sitemap at `/sitemap.xml`, 301 canonicalisation, real 404 status with `noindex`, semantic HTML, one `<h1>` per page. No fake ratings/reviews/prices. Hotel JSON-LD is off until verified (`structured_data_verified`); restaurant structured data is deliberately not included until opening hours and menu are verified (add it in `includes/layout.php` then).
**After launch:** add the domain in Google Search Console → verify (DNS or HTML-tag) → Sitemaps → submit `sitemap.xml` → inspect the home URL → request indexing. Test the 404 and page titles. Consider a Google Business Profile.

## Security, privacy, third parties
Output is escaped (`e()`); no user input reaches the server; strict Content-Security-Policy (nothing external allowed); `nosniff`, referrer and frame headers; private folders denied; directory listing off. **No cookies, no analytics, no external fonts/maps/embeds.** If you add any of those, update `pages/privacy-policy.php` and the CSP in `.htaccess` first. The privacy policy makes no legal-compliance claims and flags items needing a local adviser (Tanzanian Personal Data Protection Act 2022 and any Zanzibar requirements).

## Fonts
System fonts are used (Georgia headings, system sans body) to avoid sending visitors' IP addresses to Google. To use brand fonts, download licensed `.woff2` files into `assets/fonts/`, add `@font-face` rules at the top of `styles.css`, and change `--serif` / `--sans`.

## Backup and publishing safely
Before every change: File Manager → select files → Compress → download. Edit one file at a time and reload the site. cPanel → Backup also keeps full-account backups.

## Test checklist
**Verified here (PHP 8.3 CLI dev server emulating `.htaccess` routing + jsdom):**
- [x] All 14 URLs return 200; unknown URL returns 404 + `noindex`; `/rooms`, `//rooms//`, `/index.php` and query strings 301 to the canonical URL
- [x] `php -l` passes on every PHP file; no PHP warnings/notices on any page
- [x] One `<h1>` per page; no duplicate element IDs on form pages; no inline styles (works with the CSP)
- [x] Sitemap is valid XML and lists every page; robots.txt served
- [x] Room form: required fields, phone/email checks, check-out after check-in, past dates blocked; table form: separate fields, past date blocked
- [x] Review step; WhatsApp URL uses digits-only number and decodes to the exact message; email path shows status; edit keeps values; tab switching; room preselected on room pages; table-only form on restaurant page; missing WhatsApp/email shows a message instead of failing
- [x] Site renders with no photos/video present (placeholders)

**NOT yet verified — needs the live server / real devices:**
- [ ] `.htaccess` behaviour on your Apache (rewrites, headers, CSP, `Require all denied`); `/config/` returns 403/404
- [ ] HTTPS redirect, canonical host, HSTS (after enabling)
- [ ] WhatsApp actually opens on iPhone, Android, desktop (app and web); `mailto:` on common email apps
- [ ] Visual review at phone/tablet/desktop widths; keyboard-only navigation; screen-reader pass; colour contrast (e.g. WAVE, Lighthouse)
- [ ] Hero video playback/autoplay on mobile, file size, Lighthouse/PageSpeed with real images
- [ ] Rich-result/structured-data test after enabling; Search Console sitemap submission
- [ ] Legal review of privacy/terms; hosting provider's log retention
Not tested at all: the site has not been deployed anywhere.
