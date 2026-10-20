<?php
declare(strict_types=1);

/**
 * Baladin Zanzibar Beach Hotel  -  front controller.
 * .htaccess sends every clean URL (/rooms/, /contact/ ...) here. Server-rendered PHP 8.1+, no database.
 *
 * Page text:        config/pages.php   (room pages: config/rooms.php)
 * Contact details:  config/site.php
 * Page templates:   pages/
 * Shared parts:     includes/
 */
require_once __DIR__ . '/includes/site.php';

// Not parse_url(): it misreads a leading '//' as a hostname.
$rawPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
$routes  = get_routes();

// --- Sitemap, generated from the page list so it can never go stale ---
if ($rawPath === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (array_keys($routes) as $p) echo '  <url><loc>' . e(abs_url($p)) . '</loc></url>' . "\n";
    echo '</urlset>' . "\n";
    exit;
}

// --- One canonical URL per page: collapse // and force the trailing slash (301) ---
$path = preg_replace('#/+#', '/', $rawPath) ?? '/';
if ($path === '/index.php') $path = '/';
$canonicalPath = $path === '/' ? '/' : rtrim($path, '/') . '/';
if (isset($routes[$canonicalPath]) && $canonicalPath !== $rawPath) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: ' . $canonicalPath . ($qs !== '' ? '?' . $qs : ''), true, 301);
    exit;
}

$page = $routes[$canonicalPath] ?? null;
if ($page === null) {
    http_response_code(404);
    header('X-Robots-Tag: noindex');
    $page = [
        'slug' => '404', 'template' => '404', 'path' => $path,
        'title' => 'Page not found', 'description' => 'The page you requested could not be found.',
        'eyebrow' => '404', 'heading' => 'This page could not be found.',
        'intro' => 'The page may have moved. Return to the homepage to continue exploring Baladin Zanzibar.',
    ];
}

render_header($page);
?>
<main id="main-content">
<?php
render_hero($page);

if (($page['slug'] ?? '') === 'home'): ?>
    <section class="intro-section section-pad" id="discover">
        <div class="container split-intro">
            <div>
                <p class="eyebrow"><?= e($page['intro_eyebrow']) ?></p>
                <h2><?= e($page['intro_heading']) ?></h2>
            </div>
            <div class="intro-text">
                <p><?= e($page['intro_body']) ?></p>
                <a class="text-link" href="/about/">Discover our story <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>
<?php endif;

if (!empty($page['template'])) {
    include ROOT . '/pages/' . basename($page['template']) . '.php';   // basename() keeps this inside pages/
}
foreach (($page['sections'] ?? []) as $section) render_section($section);

// Enquiry forms that belong under specific pages
if (($page['slug'] ?? '') === 'restaurant') {
    echo '<section class="section-pad booking-section"><div class="container booking-wrap"><div class="booking-copy"><p class="eyebrow">Reserve a table</p><h2>Plan a meal by the ocean.</h2><p>Send a table enquiry. We prepare your message for WhatsApp or email; the restaurant confirms availability.</p></div>';
    render_booking_form('table');
    echo '</div></section>';
}
if (($page['slug'] ?? '') === 'home') {
    echo '<section class="section-pad booking-section" id="home-enquiry"><div class="container booking-wrap"><div class="booking-copy"><p class="eyebrow">Make it yours</p><h2>Start planning your stay.</h2><p>Tell us what you have in mind. Send a room enquiry or a restaurant table enquiry.</p></div>';
    render_booking_form('combined');
    echo '</div></section>';
}
?>
</main>
<?php render_footer(); ?>
