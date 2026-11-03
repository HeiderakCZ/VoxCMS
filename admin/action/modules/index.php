<?php

use Sunlight\Admin\Admin;
use Sunlight\Core;
use Sunlight\Database\Database as DB;
use Sunlight\Extend;
use Sunlight\Log\LogQuery;
use Sunlight\Logger;
use Sunlight\Message;
use Sunlight\Page\Page;
use Sunlight\Router;
use Sunlight\Settings;
use Sunlight\User;
use Sunlight\Util\Cookie;
use Sunlight\Util\Environment;
use Sunlight\Util\Form;
use Sunlight\Util\Json;
use Sunlight\VersionChecker;

defined('SL_ROOT') or exit;

if (isset($_POST['hide_recent_log_errors']) && Admin::moduleAccess('log')) {
    Settings::update('admin_index_log_since', (string) time(), false);
}

$admin_index_cfg = Settings::getMultiple([
    'admin_index_custom',
    'admin_index_custom_pos',
    'admin_index_log_since',
]);

$version_data = VersionChecker::check();

$mysqlver = DB::$mysqli->server_info;

if ($mysqlver != null && mb_substr_count($mysqlver, '-') != 0) {
    $mysqlver = mb_substr($mysqlver, 0, strpos($mysqlver, '-'));
}

// prepare custom content
$custom = '';

if ($admin_index_cfg['admin_index_custom'] !== '') {
    $custom = $admin_index_cfg['admin_index_custom'];
}

Extend::call('admin.index.custom', [
    'custom' => &$custom,
    'position' => &$admin_index_cfg['admin_index_custom_pos'],
]);

// logout warning
$logout_warning = '';
$maxltime = ini_get('session.gc_maxlifetime');

if (!empty($maxltime) && !Cookie::exists(User::COOKIE_PERSISTENT_LOGIN)) {
    $logout_warning = Admin::note(_lang('admin.index.logoutwarn', ['%minutes%' => _num(round($maxltime / 60))]));
}

// Build dashboard data from existing CMS tables, respecting each user's module permissions.
$dashboardStats = [];
$statIcons = [
    'articles' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h7"/>',
    'pages' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h7"/>',
    'users' => '<path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20"/><circle cx="9.5" cy="7" r="3.5"/><path d="M17 11a3.5 3.5 0 0 0 0-7M17 14.5a4 4 0 0 1 4 4V20"/>',
    'categories' => '<path d="M3 7h7l2 2h9v10H3z"/><path d="M3 7V5h7l2 2"/>',
    'media' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m4 17 5-5 3 3 3-4 5 6"/>',
    'pending' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
];

if (User::hasPrivilege('adminart')) {
    $dashboardStats[] = [
        'label' => _lang('admin.vox.dashboard.articles'),
        'value' => DB::count('article', Admin::articleAccessSql()),
        'url' => Router::admin('content-articles'),
        'icon' => 'articles',
    ];
}

if (User::hasPrivilege('adminusers')) {
    $dashboardStats[] = [
        'label' => _lang('admin.vox.dashboard.users'),
        'value' => DB::count('user', "username!=''"),
        'url' => Router::admin('users-list'),
        'icon' => 'users',
    ];
}

if (User::hasPrivilege('admincategory') && Admin::moduleAccess('content')) {
    $dashboardStats[] = [
        'label' => _lang('admin.vox.dashboard.categories'),
        'value' => DB::count('page', 'type=' . Page::CATEGORY . ' AND level<=' . (int) User::getLevel()),
        'url' => Router::admin('content'),
        'icon' => 'categories',
    ];
}

if (User::hasPrivilege('adminpages') && Admin::moduleAccess('content')) {
    $dashboardStats[] = [
        'label' => _lang('admin.vox.dashboard.pages'),
        'value' => DB::count('page', 'type=' . Page::SECTION . ' AND level<=' . (int) User::getLevel()),
        'url' => Router::admin('content'),
        'icon' => 'pages',
    ];
}

if (User::hasPrivilege('admingallery')) {
    $dashboardStats[] = [
        'label' => _lang('admin.vox.dashboard.gallery_images'),
        'value' => DB::count('gallery_image'),
        'url' => Router::admin('content'),
        'icon' => 'media',
    ];
}

if (User::hasPrivilege('adminconfirm')) {
    $dashboardStats[] = [
        'label' => _lang('admin.vox.dashboard.pending_articles'),
        'value' => DB::count('article', 'confirmed=0 AND ' . Admin::articleAccessSql()),
        'url' => Router::admin('content-confirm'),
        'icon' => 'pending',
    ];
}

$recentArticles = [];
if (User::hasPrivilege('adminart')) {
    $recentArticlesQuery = DB::query('SELECT id,title,time FROM ' . DB::table('article') . ' WHERE ' . Admin::articleAccessSql() . ' ORDER BY time DESC,id DESC LIMIT 5');
    $recentArticles = DB::rows($recentArticlesQuery);
}

// output
$dashboardIntro = ($custom !== '' && $admin_index_cfg['admin_index_custom_pos'] == 0)
    ? $custom
    : '<h1>' . _lang('admin.menu.index') . '</h1><p>' . _lang('admin.vox.dashboard.welcome') . '</p>' . $logout_warning;

$output .= '<div class="vox-dashboard">
    <section class="vox-dashboard-welcome">' . $dashboardIntro . '</section>
    <section class="vox-dashboard-grid" aria-label="' . _e(_lang('admin.vox.dashboard.stats')) . '">';

foreach ($dashboardStats as $stat) {
    $output .= '<a class="vox-stat-card" href="' . _e($stat['url']) . '">
        <span class="vox-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24">' . $statIcons[$stat['icon']] . '</svg></span>
        <span class="vox-stat-value">' . _num($stat['value']) . '</span>
        <span class="vox-stat-label">' . _e($stat['label']) . '</span>
    </a>';
}

$output .= '</section>';

if ($custom !== '' && $admin_index_cfg['admin_index_custom_pos'] == 1) {
    $output .= '<section class="vox-dashboard-custom">' . $custom . '</section>';
}

$output .= '<div class="vox-dashboard-panels">
    <section class="vox-dashboard-panel">
        <h2>' . _lang('admin.vox.dashboard.recent_activity') . '</h2>';

if (!empty($recentArticles)) {
    $output .= '<ul class="vox-activity-list">';
    foreach ($recentArticles as $article) {
        $output .= '<li><span>' . _e($article['title']) . '</span><time class="vox-activity-meta" datetime="' . date('c', (int) $article['time']) . '">'
            . GenericTemplates::renderDate((int) $article['time']) . '</time></li>';
    }
    $output .= '</ul>';
} else {
    $output .= '<p class="note">' . _lang('admin.vox.dashboard.no_recent_activity') . '</p>';
}

$output .= Extend::buffer('admin.index.after_table');
$output .= '</section>
    <section class="vox-dashboard-panel">
        <h2>' . _lang('admin.vox.dashboard.system') . '</h2>
        <table class="vox-system-info"><tbody>
            <tr><th>' . _lang('global.version') . '</th><td>' . _e(Core::VERSION) . '</td></tr>
            <tr><th>PHP</th><td>'
                . (User::isSuperAdmin() ? '<a href="' . _e(Router::path('admin/script/phpinfo.php')) . '" target="_blank">' : '')
                . _e(Environment::getPhpVersion())
                . (User::isSuperAdmin() ? '</a>' : '')
                . '</td></tr>
            <tr><th>MySQL</th><td>' . _e((string) $mysqlver) . '</td></tr>
            <tr><th>' . _lang('admin.index.box.latest') . '</th><td>';

if ($version_data === null) {
    $output .= '—';
} else {
    $output .= '<a class="latest-version latest-version-age-' . _e($version_data['localAge']) . '" href="' . _e($version_data['url']) . '" target="_blank">'
        . _e($version_data['latestVersion']) . '</a>';
}

$output .= '</td></tr>
        </tbody></table>
        <div class="vox-quick-links">';

if (User::hasPrivilege('adminart')) {
    $output .= '<a class="button" href="' . _e(Router::admin('content-articles-edit')) . '">' . _lang('admin.content.articles.create') . '</a>';
}
if (User::hasPrivilege('adminusers')) {
    $output .= '<a class="button" href="' . _e(Router::admin('users-list')) . '">' . _lang('admin.users.list') . '</a>';
}
if (User::hasPrivilege('fileaccess')) {
    $output .= '<a class="button" href="' . _e(Router::admin('fman')) . '">' . _lang('admin.menu.fman') . '</a>';
}

$output .= '</div></section></div>';

// messages
$messages = [];

if (Core::getStability() !== 'stable') {
    // stability warning
    $messages[] = Message::warning(_lang('admin.index.unstablewarn', ['%stability%' => Core::getStability()]), false, ['message-small']);
}

if (Core::$debug) {
    // debug mode
    $messages[] = Message::warning(_lang('admin.index.debugwarn'), false, ['message-small']);
} elseif (!Core::isCacheEnabled()) {
    // no cache
    $messages[] = Message::warning(_lang('admin.index.cachewarn'), false, ['message-small']);
}

if (Core::$safeMode) {
    $messages[] = Message::warning(_lang('admin.plugins.safe_mode_warning'));
}

if (($version_data !== null) && $version_data['localAge'] >= 0) {
    // old version
    if ($version_data['localAge'] === 0) {
        $messages[] = Message::ok(_lang('admin.index.version.latest'), false, ['message-small']);
    } else {
        $messages[] = Message::ok(
            _lang('admin.index.version.old', ['%version%' => $version_data['latestVersion'], '%link%' => $version_data['url']]),
            true
        );
    }
}

if (Admin::moduleAccess('log')) {
    $log_query = new LogQuery();
    $log_query->maxLevel = Logger::ERROR;
    $log_query->since = $admin_index_cfg['admin_index_log_since'] !== '0' ? (int) $admin_index_cfg['admin_index_log_since'] : null;
    $recent_log_errors = Logger::getTotalResults($log_query);

    if ($recent_log_errors > 0) {
        $messages[] = Message::warning(
            _buffer(function () use ($log_query, $recent_log_errors) { ?>
    <?= _lang('admin.index.recent_log_errors', ['%count%' => _num($recent_log_errors)]) ?><br><br>

    <a class="button" href="<?= _e(Router::admin('log', ['query' => ['maxLevel' => $log_query->maxLevel, 'since' => $log_query->since !== null ? "@{$log_query->since}" : null, 'desc' => '1', 'search' => '1']])) ?>">
        <?= _lang('global.show') ?>
    </a>

    <?= Form::start('hide-recent-log-errors', ['class' => 'inline']) ?>
        <?= Form::input('submit', 'hide_recent_log_errors', _lang('global.hide'), ['class' => 'button']) ?>
    <?php Form::end('hide-recent-log-errors') ?>
<?php
            }),
            true
        );
    }
}

Extend::call('admin.index.messages', [
    'messages' => &$messages,
]);

$output .= '<div id="index-messages" class="well' . (empty($messages) ? ' hidden' : '') . "\">\n";
$output .= '<h2>' . _lang('admin.index.messages') . "</h2>\n";
$output .= implode($messages);
$output .= "</div>\n";

// edit link
if (User::$group['id'] == User::ADMIN_GROUP_ID) {
    $output .= '<p class="text-right"><a class="button" href="' . _e(Router::admin('index-edit')) . '"><img src="' . _e(Router::path('admin/public/images/icons/edit.png')) . '" alt="edit" class="icon">' . _lang('admin.index.edit.link') . '</a></p>';
}

// .htaccess check
$output .= '<script>
Sunlight.admin.indexCheckHtaccess(
    ' . Json::encodeForInlineJs(Core::getBaseUrl()->getPath() . '/vendor/autoload.php?_why=this_is_a_test_if_htaccess_works') . ',
    ' . Json::encodeForInlineJs(_lang('admin.index.htaccess_check_failure', ['%link%' => 'https://sunlight-cms.cz/resource/8.x/no-htaccess'])) . "
);
</script>\n";
