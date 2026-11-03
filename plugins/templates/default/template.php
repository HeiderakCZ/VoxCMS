<?php use Sunlight\Template; ?>
<?php
$themeConfig = $GLOBALS['sunlight_template_universal_config'] ?? [];
$logoUrl = trim((string) ($themeConfig['logo_url'] ?? ''));
$logoAlt = trim((string) ($themeConfig['logo_alt'] ?? ''));
$headerText = trim((string) ($themeConfig['header_text'] ?? ''));
if ($logoAlt === '') {
    $logoAlt = Template::siteTitle();
}
?>

<div class="site-shell">
    <div class="utility-bar">
        <div class="site-width utility-inner">
            <?php if ($headerText !== ''): ?>
                <span class="utility-tagline"><?= _e($headerText) ?></span>
            <?php else: ?>
                <span class="utility-tagline"></span>
            <?php endif; ?>
            <nav class="user-menu" aria-label="Uživatelské menu">
                <?= Template::userMenu() ?>
            </nav>
        </div>
    </div>

    <header class="site-header">
        <div class="site-width header-inner">
            <a class="brand" href="<?= _e(Template::sitePath()) ?>" aria-label="<?= _e(Template::siteTitle()) ?> – úvodní stránka">
                <?php if ($logoUrl !== ''): ?>
                    <img class="brand-logo" src="<?= _e($logoUrl) ?>" alt="<?= _e($logoAlt) ?>">
                <?php else: ?>
                    <span class="brand-name"><?= _e(Template::siteTitle()) ?></span>
                <?php endif; ?>
            </a>
            <nav class="main-navigation" aria-label="Hlavní navigace">
                <?= Template::menu() ?>
            </nav>
        </div>
    </header>

    <main class="site-width page-layout">
        <section class="main-content">
            <div class="breadcrumbs"><?= Template::backlink() ?></div>
            <?= Template::heading() ?>
            <div class="content-body">
                <?= Template::content() ?>
            </div>
        </section>

        <aside class="sidebar" aria-label="Postranní panel">
            <?= Template::boxes('right') ?>
        </aside>
    </main>

    <footer class="site-footer">
        <div class="site-width footer-inner">
            <span>&copy; <?= date('Y') ?> <?= Template::siteTitle() ?></span>
            <ul class="footer-links"><?= Template::links() ?></ul>
            <button class="theme-toggle" type="button" aria-pressed="false" aria-label="Přepnout barevný režim">
                <span class="theme-toggle-icon" aria-hidden="true">☾</span>
                <span class="theme-toggle-label">Tmavý režim</span>
            </button>
        </div>
    </footer>
</div>
