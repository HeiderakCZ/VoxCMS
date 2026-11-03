<?php

use Sunlight\Core;
use Sunlight\Extend;
use Sunlight\Util\Color;
use Sunlight\Util\DateTime;
use Sunlight\Util\Request;

require __DIR__ . '/../../system/bootstrap.php';
Core::init([
    'env' => Core::ENV_ADMIN,
    'session_enabled' => false,
    'content_type' => 'text/css; charset=UTF-8',
]);

header('Expires: ' . DateTime::formatForHttp(2592000, true));

$dark = isset($_GET['d']);
$s = (int) Request::get('s', 0);

$adminColor = function(int $loff = 0, ?float $satc = null, bool $sat_abs = false, bool $light_abs = false): string {
    if ($satc === 0.0) {
        $light_abs = true;
        $loff += 127;
    }

    $h = $GLOBALS['hue'];

    if ($GLOBALS['dark']) {
        $l = ($light_abs ? 255 - $loff : $GLOBALS['light'] - $loff);
    } else {
        $l = ($light_abs ? $loff : $GLOBALS['light'] + $loff);
    }

    $s = (isset($satc) ? ($sat_abs ? $satc :  $GLOBALS['sat'] * $satc) : $GLOBALS['sat']);

    return (new Color([$h, $s, $l], 1))->getRgbStr();
};

// default HSL values
$hue = 0;
$light = 127;
$sat = 255;

// default color values
$scheme_link = null;
$scheme_bar_text = null;
$scheme_bar_shadow = null;
$scheme_bar_flip = false;

if ($dark) {
    $scheme_white = '#000';
    $scheme_black = '#fff';
    $scheme_bg_info = '#00626A';
    $scheme_bg_alert = '#845100';
    $scheme_bg_danger = '#840000';
} else {
    $scheme_white = '#fff';
    $scheme_black = '#000';
    $scheme_bg_info = '#D0EDEE';
    $scheme_bg_alert = '#FFD183';
    $scheme_bg_danger = '#FFA7A7';
}

$scheme_bar_loff = 30;
$scheme_text = $scheme_black;

if ($dark) {
    $scheme_contrast = $scheme_black;
    $scheme_contrast2 = $scheme_white;
} else {
    $scheme_contrast = $scheme_white;
    $scheme_contrast2 = $scheme_black;
}

$scheme_link_loff = ($dark ? -20 : -10);
$dark_suffix = ($dark ? '_dark' : '');

// apply scheme
switch ($s) {
    // blue
    case 1:
        $hue = 145;
        $sat -= 10;
        break;

    // green
    case 2:
        $hue = 70;

        if (!$dark) {
            $light -= 20;
        }

        $sat *= 0.7;
        break;

    // red
    case 3:
        $hue = 5;

        if (!$dark) {
            $light -= 10;
        }
        break;

    // yellow
    case 4:
        $hue = 35;
        $scheme_contrast = $scheme_black;
        $scheme_link = '#BE9B02';

        if (!$dark) {
            $light -= 20;
            $scheme_bar_flip = true;
        } else {
            $light += 5;
        }
        break;

    // purple
    case 5:
        $hue = 205;
        break;

    // azure
    case 6:
        $hue = 128;

        if (!$dark) {
            $light -= 10;
            $sat -= 70;
            $scheme_link_loff -= 10;
            $scheme_bar_flip = true;
        }
        break;

    // violet
    case 7:
        $hue = 195;

        if ($dark) {
            $light += 10;
        }
        break;

    // brown
    case 8:
        $hue = 20;
        $light -= 10;
        $sat *= 0.6;
        break;

    // dark blue
    case 9:
        $hue = 170;

        if ($dark) {
            $scheme_link_loff -= 20;
        }

        $sat *= 0.5;
        break;

    // grey
    case 10:
        $hue = 150;
        $sat = 0;
        $scheme_link = '#67939F';
        $scheme_bar_loff = 50;

        if (!$dark) {
            $scheme_bar_flip = true;
        }
        break;

    // orange
    default:
        $hue = 14;
        $scheme_link = '#F84A00';
        $light -= 10;
        break;
}

Extend::call('admin.style.init');

// generate colors
$scheme = $adminColor(($dark ? 40 : 0));
$scheme_lighter = $adminColor(80);
$scheme_lightest = $adminColor(100);
$scheme_smoke = $adminColor(115, 0);
$scheme_smoke_text = $adminColor((int) ($light * 0.2), 0);
$scheme_smoke_text_dark = $adminColor(10, 0);
$scheme_smoke_text_darker = $adminColor(-30, 0);
$scheme_smoke = $adminColor(110, 0);
$scheme_smoke_med = $adminColor(90, 0);
$scheme_smoke_dark = $adminColor(60, 0);
$scheme_smoke_darker = $adminColor($dark ? -20 : -10, 0);
$scheme_smoke_light = $adminColor(110, 0);
$scheme_smoke_lighter = $adminColor(118, 0);
$scheme_smoke_lightest = $adminColor(125, 0);
$scheme_smoke_lightest_colored = $adminColor(125);
$scheme_med = $adminColor(30);
$scheme_dark = $adminColor(-10);
$scheme_bar = $adminColor($scheme_bar_loff);

if ($scheme_link == null) {
    $scheme_link = $adminColor($scheme_link_loff, 255, true);
}

if ($scheme_bar_shadow === null) {
    $scheme_bar_shadow = ($scheme_bar_flip ? 'rgba(255, 255, 255, 0.3)' : 'rgba(0, 0, 0, 0.3)');
}

if ($dark) {
    $scheme_bar_flip = !$scheme_bar_flip;
}

if ($scheme_bar_text === null) {
    $scheme_bar_text = ($scheme_bar_flip ? $scheme_black : $scheme_white);
}

if ($dark) {
    $scheme_alpha_shadow = 'rgba(255, 255, 255, 0.15)';
    $scheme_alpha_shadow2 = 'rgba(255, 255, 255, 0.075)';
} else {
    $scheme_alpha_shadow = 'rgba(0, 0, 0, 0.15)';
    $scheme_alpha_shadow2 = 'rgba(0, 0, 0, 0.075)';
}

Extend::call('admin.style.start');

?>
/* <style>
/* tags */
* {margin: 0; padding: 0;}
body {font-family: sans-serif; font-size: 12px; color: <?= $scheme_text ?>; background-color: <?= $scheme_smoke_light ?>; margin: 0 0 1em 0;}
a {font-size: 12px; color: <?= $scheme_link ?>; text-decoration: none;}
a:hover {color: <?= $scheme_text ?>; text-decoration: none;}
h1 {font-size: 18px; font-weight: normal; margin-bottom: 0.5em;}
h2 {font-size: 14px; margin: 0.25em 0;}
h3 {font-size: 12px; margin: 0.25em 0;}
p {padding: 0; margin: 2px 0 10px 0; line-height: 160%;}
ul, ol {padding: 2px 0 12px 40px; line-height: 160%;}
dfn {font-style: normal; border-bottom: 1px dashed <?= $scheme_smoke_dark ?>; cursor: help;}
img {border: 0;}
small {color: <?= $scheme_smoke_text ?>;}
td, th {font-size: 12px; padding: 1px;}
th {text-align: left; font-weight: bold;}
form td {padding: 2px;}
form > table > tbody > tr > th, .cform th {padding-right: 10px; text-align: right;}

/* forms */
form {margin: 0 0 8px 0;}
form.inline {line-height: 1;}
fieldset {margin: 25px 0; padding: 8px; background-color: <?= $scheme_smoke_lighter ?>; border: 1px solid <?= $scheme_smoke ?>;}
fieldset fieldset {background-color: <?= $scheme_white ?>;}
fieldset.hs_fieldset > legend:hover {text-decoration: underline;}
legend {font-weight: bold; color: <?= $scheme_text ?>;}
label {padding-right: 0.5em;}

input, button, select, textarea {padding: 6px; margin: 0; box-sizing: border-box; font-size: 12px; line-height: 1 !important; vertical-align: middle;}

input[type=checkbox], input[type=radio] {padding: 0; margin: 3px; border: none; vertical-align: middle;}
input[type=text], input[type=password], input[type=number], input[type=email], input[type=search], input[type=datetime-local], select {height: 26px; border: 1px solid <?= $scheme_smoke_dark ?>; box-shadow: inset 0 0 4px <?= $scheme_alpha_shadow2 ?>;}
input[type=submit], input[type=button], input[type=reset], button {cursor: pointer; padding: 8px 12px; border: 1px solid <?= $scheme_smoke_med ?>; background: <?= $scheme_smoke_lighter ?>; background: linear-gradient(to bottom, <?= $scheme_smoke_lightest ?>, <?= $scheme_smoke ?>); color: <?= $scheme_text ?>; font-size: 13px;}
input[type=submit]:focus, input[type=button]:focus, input[type=reset]:focus, button:focus {outline: 1px solid <?= $scheme_med ?>;}
input[type=submit]:hover, input[type=button]:hover, input[type=reset]:hover, button:hover {background: <?= $scheme_lightest ?>; background: linear-gradient(to bottom, <?= $scheme_lightest ?>, <?= $scheme_lighter ?>); border-color: <?= $scheme_lighter ?>; outline: none;}
input[type=color] {padding: 0;}

select {padding: 0 20px 0 6px; background: <?= $scheme_white ?> url("../public/images/select_arrow<?= $dark ? '_inverted' : '' ?>.png") right center no-repeat; appearance: none; -webkit-appearance: none; -moz-appearance: none; text-indent: 0.01px; text-overflow: ''; line-height: 2 !important;}
select::-ms-expand {display: none;}
select[multiple], select[size] {height: auto;}
optgroup option {padding-left: 16px;}

@-moz-document url-prefix() {
    input::-moz-focus-inner, button::-moz-focus-inner, select::-moz-focus-inner, textarea::-moz-focus-inner {border: 0; padding: 0;}
    input[type=text], input[type=password], select {padding-top: 0; padding-bottom: 0;}
}

<?php if ($dark) { ?>
input, textarea, button, select {
    background-color: <?= $scheme_white ?>;
    color: <?= $scheme_black ?>;
    border: 1px solid <?= $scheme_smoke_dark ?>;
}
<?php } ?>

/* layout */
.wrapper {max-width: 1400px; margin: 0 auto;}

/* header */
#top {background: url("../public/images/top_bg<?= $dark_suffix ?>.png") left bottom repeat-x;}
#header {font-family: Georgia, "Times New Roman", Times, serif; font-size: 24px; padding: 0.7em 16px 0.5em 16px;}
#title {color: <?= $scheme_smoke_darker ?>;}
#usermenu {float: right; position: relative; top: 6px;}
#usermenu, #usermenu a {font-size: 14px; color: <?= $scheme_smoke_text ?>;}
#usermenu a {text-decoration: none;}
#usermenu a:hover {color: <?= $scheme_link ?>;}
a#usermenu-username {margin-right: 0.5em; font-weight: bold; color: <?= $scheme_smoke_darker ?>;}
#usermenu a.usermenu-web-link {margin-left: 0.5em;}
#usermenu-avatar {position: absolute; left: -42px; top: -8px; display: block; width: 32px; height: 32px; overflow: hidden; border: 1px solid <?= $scheme_smoke_med ?>; background-color: <?= $scheme_white ?>; vertical-align: middle; border-radius: 50%;}
#usermenu-avatar img {max-width: 32px; opacity: 0.9;}
#usermenu-avatar:hover img {opacity: 1;}

/* menu */
#menu {position: relative; padding: 5px 0 0 16px; background-color: <?= $scheme_bar ?>; font-size: 0;}
#menu a {color: <?= $scheme_bar_text ?>; text-decoration: none; display: inline-block;}
#menu a span {display: inline-block; padding: 7px 16px; background: url("../public/images/menu_sep<?= $dark_suffix ?>.png") right bottom no-repeat; font-size: 13px; text-shadow: 0 0 5px <?= $scheme_bar_shadow ?>;}
#menu a:hover span, #menu a.act span {color: <?= $scheme_black ?>; background: <?= $scheme_white ?> url("../public/images/menu_active<?= $dark_suffix ?>.png") left top repeat-x; text-shadow: none;}
#menu-toggle-button {display: none;}

/* content */
#content {min-width: 320px; padding: 12px 16px 16px 16px; background-color: <?= $scheme_white ?>;}

/* footer */
#footer {margin-bottom: 0.5em; text-align: right;}
#footer-content {padding: 8px 16px; background-color: <?= $scheme_bar ?>;}
#footer, #footer a {font-size: 11px; text-decoration: none; color: <?= $scheme_bar_text ?>; text-shadow: 0 0 5px <?= $scheme_bar_shadow ?>;}
#footer a:hover {text-decoration: underline;}
#footer-links {float: left;}
#footer-links a {margin-right: 1em;}

/* login layout */
.login-layout {background: radial-gradient(at center 270px, <?= $scheme_smoke_light ?>, <?= $scheme_smoke_dark ?>) no-repeat fixed;}
.login-layout .wrapper {max-width: 500px;}
.login-layout #header, .login-layout #menu {display: none;}
.login-layout #top {padding-top: 140px; background: url("../public/images/logo.png") center 50px no-repeat;}
.login-layout #content {padding: 24px 16px; box-shadow: 0 0 6px 1px <?= $scheme_alpha_shadow ?>; text-align: center;}
.login-layout #content form {display: inline-block;}
.login-layout .login-form-links, .login-layout #content form {text-align: left;}
.login-layout .login-form-links {padding: 0 0 0 3px; list-style-type: none;}
.login-layout #content .message {text-align: left;}
.login-layout #footer-content {background: none;}
.login-layout #footer, .login-layout #footer a {color: <?= $scheme_text ?>; text-shadow: none;}

/* external container */
#external-container {padding: 10px;}
#external-container h1 {border-bottom: 3px solid <?= $scheme ?>; padding-bottom: 3px; margin-bottom: 6px;}

/* index */
#index-table {width: 100%; margin: 0 0 10px 0; padding: 0; border-collapse: collapse;}
#index-table > tbody > tr > td {padding: 10px; border: 1px solid <?= $scheme_smoke_med ?>; background-color: <?= $scheme_smoke_lighter ?>;}
#index-table > tbody > tr > td:last-child {width: 200px;}
#index-table h2 {margin-bottom: 6px; border-bottom: 2px solid <?= $scheme_smoke_med ?>; padding-bottom: 6px;}
#index-table li {padding: 3px;}
#index-table table {width: 100%;}
#index-table table th, #index-table table td {text-align: left; padding: 0.2em; border-bottom: 1px solid <?= $scheme_smoke_med ?>;}
#index-table table tr:last-child th, #index-table table tr:last-child td {border-bottom: none;}
.module-index .latest-version {font-weight: bold; color: #0077a7;}
.module-index .latest-version.latest-version-age-0 {color: #009800;}
.module-index .latest-version.latest-version-age-1 {color: #cda869;}
.module-index .latest-version.latest-version-age-2 {color: #f06c00;}
.module-index .latest-version.latest-version-age-3 {color: #e60000;}

/* content management */
#contenttable {width: 100%; border: 1px solid <?= $scheme_smoke ?>; line-height: 140%;}
#contenttable a {text-decoration: none;}
#contenttable h2 {margin: 0 0 8px 0; padding: 4px 0 7px 0; border-bottom: 1px solid <?= $scheme_smoke ?>;}
#contenttable .pad {padding: 20px 0;}
#contenttable .main-box {width: 80%;}

#content-modules {border: none;}
#content-modules h2 {margin-top: 1em;}
#content-modules h2:first-child {margin-top: 0;}

/* page list */
.page-list {width: 100%; margin-bottom: 0.5em; border-collapse: separate; border-spacing: 0; white-space: nowrap;}
.page-list td {min-width: 19px; padding: 0 5px; border-left: 1px solid <?= $scheme_white ?>; line-height: 30px; position: relative;}
.page-list td:first-child {border-left: none;}

.page-list a {color: <?= $scheme_text ?>;}

.page-list .page-title {width: 90%;}
.page-list .page-title a {color: inherit; padding: 4px 0;}
.page-list .page-title a:hover {text-decoration: underline !important;}
.page-list .page-invisible .page-title-content {color: <?= $scheme_smoke_text_dark ?>;}
.page-list .page-title-content .page-actions {margin-left: 2px;}

.page-list.page-list-full-tree .page-title-content:not(.node-level-m0) {padding-left: 8px; border-left: 1px solid <?= $scheme_smoke_dark ?>;}
.page-list.page-list-full-tree .page-separator .page-title-content {border-left-color: <?= $scheme_smoke_text ?>;}

.page-list .page-separator td {border-top: 24px solid <?= $scheme_white ?>; border-bottom: 2px solid <?= $scheme_smoke_dark ?>; background-color: <?= $scheme_lighter ?>;}
.page-list .page-separator.sorting td {border-top: none;}
.page-list .page-separator:first-child td {border: none;}
.page-list .page-separator .page-title-content {font-weight: bold;}

.page-list tr:hover:not(.page-separator) td {background-color: <?= $scheme_lighter ?>;}
.page-list tr:hover .page-type {color: inherit;}

.page-list .page-actions {text-align: right; white-space: nowrap;}
.page-list .page-actions a {padding: 4px;}
.page-list .page-actions a img.icon {margin: 0; padding: 0; vertical-align: middle;}
.page-list .page-actions a span {display: none;}

.page-list .sortable-placeholder td {height: 36px;}
.page-list .page-list-sortcell {width: 1%; vertical-align: bottom;}
.page-list .page-list-ord {width: 45px;}
.page-list .page-type {color: <?= $scheme_smoke_text_dark ?>;}

.page-list-breadcrumbs {margin: 0 0 0.5em 0; padding: 0.5em 1em; border-bottom: 2px solid <?= $scheme_smoke_dark ?>; list-style-type: none; background-color: <?= $scheme_lightest ?>;}
.page-list-breadcrumbs li {display: inline; padding-right: 0.5em;}
.page-list-breadcrumbs li:not(:last-child):after {content: ">"; display: inline-block; margin-left: 0.5em;}
.page-list-breadcrumbs a {color: <?= $scheme_text ?>; font-weight: bold; vertical-align: middle;}
.page-list-breadcrumbs a:hover {color: <?= $scheme_link ?>;}

/* editpages settings */
#settingseditform fieldset label{display: block;}
#settingseditform fieldset:first-child {margin: 0 0 25px 0;}
#settingseditform table td { padding: 4px 8px;border-bottom: 1px solid <?= $scheme_smoke_med ?>;}
#settingseditform table tr:last-child td {border-bottom: none;}

/* box management */
.module-content-boxes .list {margin-bottom: 32px;}
.module-content-boxes td {white-space: nowrap;}
.module-content-boxes .box-title-cell {white-space: normal; width: 90%;}

/* article edit */
#article-edit-picture {width: 100%;}
#article-edit-picture-file {display: block; max-width: 200px; max-height: 200px; margin: 0 auto; border: 1px solid <?= $scheme_lighter ?>;}
#article-edit-picture-delete {padding: 6px; text-align: center;}
#article-edit-picture-upload {display: block; margin: 0 auto;}
#article-edit-time input[type=datetime-local] {margin-bottom: 10px;}

/* box manager */
#boxesedit {width: 100%;}
#boxesedit .cell {padding: 10px 20px 25px 10px;}
#boxesedit .cell > div {border: 1px solid <?= $scheme_smoke ?>; padding: 20px 15px;}

/* file manager */
#fman-action {border-bottom: 1px solid <?= $scheme_smoke ?>; margin-bottom: 10px;}
#fman-action h2 {margin-bottom: 6px;}
#fman-list {width: 100%; border-collapse: collapse; margin-bottom: 6px;}
#fman-list a {color: <?= $scheme_text ?>;}
#fman-list .fman-size {width: 15%;}
#fman-list .actions {width: 25%;}
#fman-list .fman-item a {display: block;}
#fman-list td {padding: 2px 4px; border: 1px solid <?= $scheme_white ?>; line-height: 200%;}
#fman-list tr:hover td {background-color: <?= $scheme_lightest ?>;}
#fman-list input {margin: 5px 4px 0 0; float: left;}
#fman-list .fman-uploaded td {background: <?= $scheme_lighter ?>;}
#fmanFiles {white-space: nowrap;}
.fman-menu {border-width: 1px 0 1px 0; border-style: solid; border-color: <?= $scheme_smoke ?>; line-height: 200%;}
.fman-menu {margin-top: 5px; padding: 5px;}
.fman-menu a, .fman-menu > span {border-right: 1px solid <?= $scheme_smoke ?>; padding-right: 8px; margin-right: 8px;}
.fman-spacer {height: 10px;}

/* galleries */
.gallery-savebutton {float: left; margin: 0 14px 0 0; display: block;}
#gallery-edit {float: left; margin: 14px 0; padding: 5px; border: 1px solid <?= $scheme_smoke_light ?>;}
.gallery-edit-image {float: left; margin: 5px;}
.gallery-edit-image input {cursor: default;}
.gallery-edit-image table {width: 100%; height: 100%; border: 1px solid <?= $scheme_smoke_dark ?>; padding: 10px; background-color: <?= $scheme_smoke ?>; cursor: move;}
.gallery-edit-image a {color: <?= $scheme_black ?>; cursor: pointer;}
.gallery-edit-image-preview {margin-bottom: 5px;}
.gallery-edit-image-preview img {border: 1px solid <?= $scheme_smoke_text ?>; max-width: 248px;}

/* sqlex */
#sqlex {width: 100%; margin-top: 1em; border-collapse: collapse;}
#sqlex td {padding: 1em; border: 1px solid <?= $scheme_smoke ?>; vertical-align: top;}
#sqlex td:first-child ul {padding: 0; margin: 0; list-style-type: none;}
#sqlex li, #sqlex-result li {line-height: 160%;}
#sqlex-result {overflow: auto;}
#sqlex-result h2 {margin-bottom: 1em;}
#sqlex-result .list {width: 100%; background-color: <?= $scheme_white ?>; outline: 6px solid <?= $scheme_white ?>;}
#sqlex-result .list textarea {width: 100%;}

/* settings */
#settings-container {display: flex; align-items: flex-start;}
#settings-nav {margin-right: 1em; flex-basis: 20%; position: sticky; top: 10px;}
#settings-nav, #settings-nav a {font-size: 12px;}
#settings-nav input[type=submit] {width: 100%;}
#settings-nav ul {padding: 0; margin: 0.5em 0 0 0; border: 1px solid <?= $scheme_smoke ?>; background-color: <?= $scheme_smoke_lighter ?>;}
#settings-nav li {display: block; list-style-type: none;}
#settings-nav li a {display: block; padding: 6px 11px; border-bottom: 1px solid <?= $scheme_smoke_light ?>; color: <?= $scheme_text ?>;}
#settings-nav a:hover {background-color: <?= $scheme_smoke_dark ?>; color: <?= $scheme_black ?>;}

#settings-form fieldset {margin: 0 0 5em 0;}
#settings-form fieldset:target {border-color: <?= $scheme ?>;}
#settings-form label {font-weight: bold;}
#settings-form table {border-collapse: collapse;}
#settings-form table td {padding: 4px 8px; border-bottom: 1px solid <?= $scheme_smoke_med ?>;}
#settings-form table th {padding-right: 8px; padding-left: 4px;}
#settings-form table tr:last-child td {border-bottom: none;}

/* plugins */
.plugin-list {width: 100%; table-layout: fixed;}
.plugin-list td {vertical-align: top;}
.plugin-list > thead > tr > th:first-child {width: 30%;}

/* busy overlay */
body.busy-overlay-active {overflow: hidden;}
#busy-overlay {position: fixed; left: 0; top: 0; z-index: 5000; width: 100%; height: 100%; cursor: wait; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;}
#busy-overlay > div {transition: opacity 1s; width: 100%; height: 100%; opacity: 0; background-color: rgba(0, 0, 0, 0.5);}
#busy-overlay.busy-overlay-visible > div {opacity: 1;}
#busy-overlay > div > div {position: absolute; left: 50%; top: 50%; margin: -60px 0 0 -150px; width: 300px; height: 120px; text-align: center;}
#busy-overlay > div > div > p {color: #fff; font-size: 1.8em;}

/* link styles */
a.invisible-link {color: <?= $scheme_smoke_text ?>;}
a.active-link {text-decoration: underline !important;}

/* highlight */
.hl {background-color: <?= $scheme_smoke_lighter ?>;}

/* messages */
.message {margin: 1em 0; padding: 13px 5px 13px 48px; line-height: 140%; background-color: <?= $scheme_smoke ?>; background-position: 5px 5px; background-repeat: no-repeat;}
.message ul {margin: 0; padding: 5px 0 0 15px;}
.message a {color: inherit; text-decoration: underline;}
.message a.button {color: unset; text-decoration: unset;}
.message-ok {background-color: <?= $scheme_bg_info ?>; background-image: url("../public/images/icons/info.png");}
.message-warn {background-color: <?= $scheme_bg_alert ?>; background-image: url("../public/images/icons/warning.png");}
.message-err {background-color: <?= $scheme_bg_danger ?>; background-image: url("../public/images/icons/error.png");}
.message-small {margin: 0.5em 0; padding: 7px 5px 7px 30px; background-color: unset !important; background-size: 20px 20px;}
.message-small + .message-small {margin-top: -0.25em;}

/* preformatted */
pre {white-space: pre-wrap; overflow-wrap: anywhere; word-break: normal;}
pre.exception {max-height: 300px; margin: 1em 0; padding: 13px; overflow: auto; background-color: <?= $scheme_bg_danger ?>;}

/* form tables */
.formtable {border: 1px dotted <?= $scheme_smoke ?>; background-color: <?= $scheme_smoke_lightest ?>;}
.cform table {width: 100%; table-layout: fixed;}
.cform th {width: 111px;}
.cform th:first-child + td {width: 40%;}
.cform th:first-child + td:last-child {width: auto;}
.form-box {padding: 8px; margin: 0; border-right: 1px solid <?= $scheme_smoke ?>;}
.form-box.main-box {width: 70%; padding-bottom: 0;}
.customsettings {padding-left: 10px;}

/* text colors */
.text-success {color: #080;}
.text-warning {color: #FE7F00;}
.text-danger {color: #E71717;}

/* table cell colors */
.cell-success, .row-success > th, .row-success > td {background-color: <?= $dark ? '#001e00' : '#e1ffe1' ?>;}
.cell-warning, .row-warning > th, .row-warning > td {background-color: <?= $dark ? '#1e0f00' : '#fff0e1' ?>;}
.cell-danger, .row-danger > th, .row-danger > td {background-color: <?= $dark ? '#1b0303' : '#fde3e3' ?>;}
tr.even > td {background-color: <?= $scheme_smoke ?>;}
tr.od >d td {background-color: <?= $scheme_smoke_lightest ?>;}

/* form element sizes */
.arealine {width: 100%; height: 100px;}
.areasmall {width: 290px; height: 150px;}
.areasmall_100pwidth {width: 100%; height: 200px;}
.areasmallwide {width: 620px; height: 150px;}
.areamedium {width: 600px; height: 350px;}
.areabig {width: 100%; height: 400px;}
.areabigperex {width: 100%; height: 150px;}
.inputmini {width: 38px;}
.inputmini[type=number] {width:50px;}
.inputsmaller {width: 80px;}
.inputsmall {width: 145px;}
.inputmedium {width: 290px;}
.inputbig {width: 750px;}
.inputmax {width: 100%;}
.inputfat {padding: 8px 16px !important;}
.selectmedium {width: 294px;}
.selectbig {width: 753px;}
.selectmax {width: 100%;}

/* horizontal rule */
.hr {height: 10px; background-image: url("../public/images/hr<?= $dark_suffix ?>.gif"); background-position: left center; background-repeat: repeat-x;}
.hr hr {display: none;}

.paging {margin: 1em 0;}
.paging-label {display: none;}
.paging a {display: inline-block; padding: 0.3em 0.6em; border: 1px solid <?= $scheme_smoke ?>; text-decoration: none;}
.paging a.act, .paging a:hover {color: <?= $scheme_text ?>; background-color: <?= $scheme_smoke_light ?>;}
fieldset .paging a {background-color: <?= $scheme_white ?>;}
fieldset .paging a.act, fieldset .paging a:hover {background-color: <?= $scheme_smoke_dark ?>; color: <?= $scheme_white ?>;}

/* two-column layout */
.two-columns {width: 100%; table-layout: fixed; border: 1px solid <?= $scheme_smoke ?>; border-collapse: collapse;}
.two-columns > tbody > tr > td {width: 50%; padding: 5px 15px;}
.two-columns > tbody > tr > td:first-child {border-right: 1px solid <?= $scheme_smoke ?>;}
.two-columns > tbody > tr > td > h2 {margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid <?= $scheme_smoke ?>;}
.two-columns > tbody > tr > td > h2:first-child {margin-top: 5px;}
.two-columns > tbody > tr > td > form:last-child {margin-bottom: 0;}

/* list */
.list {margin: 10px 0; border-collapse: collapse; border: 1px solid <?= $scheme_smoke ?>; background-color: <?= $scheme_white ?>;}
.list.list-max {width: 100%;}
.list.list-half {min-width: 50%;}
.list > caption {padding: 15px; border-bottom: 3px double <?= $scheme_smoke_lightest ?>; background-color: <?= $scheme_smoke ?>; text-align: left;}
.list > * > tr > th, .list > thead > tr > td {font-weight: bold; background-color: <?= $scheme_smoke ?>;}
.list > * > tr > td, .list > * > tr > th {padding: 7px 15px; border-bottom: 1px solid <?= $scheme_smoke ?>;}
.list.list-hover > tbody > tr:hover:not(.nohover) > td {background-color: <?= $scheme_smoke_lightest_colored ?>;}
.list.list-noborder > * > tr > *, .list > * > tr.list-noborder > * {border: none;}
.list > tbody + tbody {border-top: 3px double <?= $scheme_smoke ?>;}
.list h3 {font-weight: normal; font-size: 16px;}
fieldset .list > thead > tr > td, fieldset .list > * th {background-color: <?= $scheme_smoke_dark ?>;}
fieldset .list > * tr > td {border-color: <?= $scheme_smoke_dark ?>;}

/* log */
.log-list {background-color: <?= $scheme_smoke_lightest ?>; min-width: 800px;}
.log-list a {color: unset;}
.log-list > tbody:hover > tr > td {background-color: <?= $scheme_smoke_lighter ?>;}
.log-list > tbody + tbody {border-top: 1px solid <?= $scheme_smoke_med ?>;}
.log-url {white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 0;}
.log-message {color: <?= $scheme_smoke_text_darker ?>;}
.log-message a {display: block;}
.log-message code {overflow-wrap: anywhere; word-break: normal;}
.log-search .log-time-presets {width: 50px;}

/* actions list */
.actions {white-space: nowrap;}
.actions a {margin-left: 0.5em;}
.actions a:first-child {margin-left: 0;}

/* radio group */
.radio-group {border: 1px solid <?= $scheme_smoke_dark ?>; background-color: <?= $scheme_white ?>; box-shadow: inset 0 0 4px <?= $scheme_alpha_shadow ?>;}
.radio-group label {display: block; float: left; padding: 5px; border-left: 1px solid <?= $scheme_smoke_dark ?>;}
.radio-group label:first-child {border-left: none;}

/* inline list */
.inline-list {padding-left: 0; padding-right: 0; list-style-type: none;}
.inline-list > li {display: inline-block; padding-left: 0.5em;}
.inline-list > li:after {content: "|"; padding-left: 0.5em; color: <?= $scheme_smoke_dark ?>;}
.inline-list > li:first-child {padding-left: 0;}
.inline-list > li:last-child:after {display: none;}
.inline-list > li > strong {padding-right: 0.2em;}

/* buttons */
a.button {display: inline-block; margin: 0; padding: 6px; border: 1px solid <?= $scheme_smoke_med ?>; background: <?= $scheme_smoke_lighter ?>; background: linear-gradient(to bottom, <?= $scheme_smoke_lightest ?>, <?= $scheme_smoke ?>); color: <?= $scheme_text ?>; vertical-align: middle; font-weight: normal; line-height: 1;}
a.button img.icon {margin: -1px 0 -1px 0; padding: 0 6px 0 0;}
a.button.block {display: block; margin: 6px;}
a.button.block img.icon {float: none; margin: 0; padding: 0 10px 0 0;}
a.button:hover {background: <?= $scheme_lightest ?>; background: linear-gradient(to bottom, <?= $scheme_lightest ?>, <?= $scheme_lighter ?>); border-color: <?= $scheme_lighter ?>;}
a.button.big, input[type=submit].button.big, input[type=button].button.big, input[type=reset].button.big, button.button.big {padding: 8px 12px; font-size: 13px;}
a.button.bigger, input[type=submit].button.bigger, input[type=button].button.bigger, input[type=reset].button.bigger, button.button.bigger {padding: 12px 18px; font-size: 14px;}
input[type=submit].button, input[type=button].button, input[type=reset].button, button.button {padding: 6px; font-size: 12px;}

/* inline separator */
.inline-separator {margin-left: 0.5em; padding-right: 0.5em; border-left: 1px solid <?= $scheme_smoke_med ?>;}

/* well */
.well {margin: 10px 0; padding: 10px; border: 1px solid <?= $scheme_smoke_med ?>; background-color: <?= $scheme_smoke_lighter ?>;}
.well h2 {font-weight: normal;}

/* sortables */
.sortable.ui-sortable {overflow: hidden;}
.sortable-handle {display: inline-block; vertical-align: middle; padding-right: 10px; width: 11px; height: 18px; background: url("../public/images/icons/drag-handle.png") left top no-repeat; cursor: move;}

/* generic */
.bborder {padding-bottom: 8px; margin-bottom: 12px; border-bottom: 1px solid <?= $scheme_smoke ?>;}
fieldset .bborder {border-color: <?= $scheme_smoke_text ?>;}
fieldset fieldset .bborder {border-color: <?= $scheme_smoke ?>;}
.backlink {display: block; font-weight: bold; padding-bottom: 10px;}
.icon {margin: -2px 0 0 0; padding-right: 4px; vertical-align: middle;}
.inline {display: inline;}
.hidden {display: none;}
.cleaner {clear: both;}
.special {color: <?= $scheme_link ?>;}
.small {font-size: 10px;}
.block {display: block;}
.note {color: <?= $scheme_smoke_text ?>;}
.important {color: red;}
.highlight {color: <?= $scheme_link ?>;}
.max-area {width: 100%; height: 100%;}
.max-width {width: 100%;}
.half-width {width: 50%;}
.separated {margin-top: 1em;}
.clickable {cursor: pointer;}
.strike {text-decoration: line-through;}
.em {font-style: italic;}
.big-text {font-size: larger;}
.left {float: left; margin: 1px 10px 5px 1px;}
.right {float: right; margin: 1px 1px 5px 10px;}
.text-left {text-align: right;}
.text-center {text-align: center;}
.text-right {text-align: right;}
tr.valign-top > *, table.valign-top > * > tr > * {vertical-align: top;}
.cell-shrink {width: 0; white-space: nowrap;}
.error-border {border-color: #f00 !important;}
.no-bullets {list-style-type: none; padding-left: 10px;}
.ui-sortable-handle {-ms-touch-action: none; touch-action: none;}
.no-wrap {white-space: nowrap;}

/* tree */
.node-level-m0 {margin-left: 0 !important;}
.node-level-p0 {padding-left: 0 !important;}
.node-level-m1 {margin-left: 24px !important;}
.node-level-p1 {padding-left: 24px !important;}
.node-level-m2 {margin-left: 48px !important;}
.node-level-p2 {padding-left: 48px !important;}
.node-level-m3 {margin-left: 72px !important;}
.node-level-p3 {padding-left: 72px !important;}
.node-level-m4 {margin-left: 96px !important;}
.node-level-p4 {padding-left: 96px !important;}
.node-level-m5 {margin-left: 120px !important;}
.node-level-p5 {padding-left: 120px !important;}
.node-level-m6 {margin-left: 144px !important;}
.node-level-p6 {padding-left: 144px !important;}
.node-level-m7 {margin-left: 168px !important;}
.node-level-p7 {padding-left: 168px !important;}
.node-level-m8 {margin-left: 192px !important;}
.node-level-p8 {padding-left: 192px !important;}
.node-level-m9 {margin-left: 216px !important;}
.node-level-p9 {padding-left: 216px !important;}
.node-level-m10 {margin-left: 240px !important;}
.node-level-p10 {padding-left: 240px !important;}
.node-level-m11 {margin-left: 264px !important;}
.node-level-p11 {padding-left: 264px !important;}
.node-level-m12 {margin-left: 288px !important;}
.node-level-p12 {padding-left: 288px !important;}
.node-level-m13 {margin-left: 312px !important;}
.node-level-p13 {padding-left: 312px !important;}
.node-level-m14 {margin-left: 336px !important;}
.node-level-p14 {padding-left: 336px !important;}
.node-level-m15 {margin-left: 360px !important;}
.node-level-p15 {padding-left: 360px !important;}
.node-level-m16 {margin-left: 384px !important;}
.node-level-p16 {padding-left: 384px !important;}
.node-level-m17 {margin-left: 408px !important;}
.node-level-p17 {padding-left: 408px !important;}
.node-level-m18 {margin-left: 432px !important;}
.node-level-p18 {padding-left: 432px !important;}
.node-level-m19 {margin-left: 456px !important;}
.node-level-p19 {padding-left: 456px !important;}
.node-level-m20 {margin-left: 480px !important;}
.node-level-p20 {padding-left: 480px !important;}

/* desktop only styles >=1000px */
@media all and (min-width: 1000px) {
    .mobile-only, .mobile-spacer {display: none !important;}
}

/* responsive styles <1000px */
@media all and (max-width: 999px) {
    /* tags */
    html {scroll-padding-top: 90px;}
    body {margin: 0;}

    /* header */
    #top {position: sticky; top: 0; background-color: <?= $scheme_smoke_light ?>; z-index: 200;}
    #header {padding: 0;}
    #usermenu {display: block; position: static; float: none; padding: 10px 5px; text-align: right;}
    #usermenu, #usermenu a {font-size: 16px;}
    #title {padding: 8px 16px; color: <?= $scheme_white ?>; background: <?= $scheme_bar ?>; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;}

    /* menu */
    #menu-toggle-button {display: initial; cursor: pointer; user-select: none;}
    #menu {display: none; position: absolute; left: 0; top: 100%; z-index: 200; padding: 16px;}
    #menu {max-width: 300px;}
    #menu a {display: block; background: unset;}
    #menu a.act {background-color: <?= $scheme_white ?>}
    #menu a.act span, #menu a:hover span {background: unset;}
    #menu a span {font-size: 16px;}
    #menu a:hover {background-color: <?= $scheme_smoke_lightest_colored ?>;}

    /* content */
    #page {overflow-x: auto; background-color: <?= $scheme_white ?>;}
    #content {background: unset;}

    /* footer */
    #footer {position: sticky; bottom: 0; z-index: 190; margin-bottom: 0;}

    /* login layout */
    .login-layout #container {min-width: 320px;}
    .login-layout .wrapper {width: 90%; max-width: 400px;}
    .login-layout #page {overflow-x: unset;}
    .login-layout .login_form :is(table, tbody, tr, th, td) {display: block;}
    .login-layout .login_form th {text-align: left; margin-top: 10px;}

    /* content management */
    #contenttable {border: none; background: none;}
    #contenttable .form-box {margin-bottom: 20px; border: 1px solid <?= $scheme_smoke ?>;}
    #contenttable-actions {line-height: 270%;}

    /* backup */
    .module-backup label:has(input[type=radio]) {white-space: nowrap;}

    /* settings */
    #settings-container {display: initial;}
    #settings-nav {margin: 0 0 5em 0;}
    #settings-nav li.active a {background-color: unset; color: unset;}
    #settings-form table td {border: none;}
    #settings-form table tr {margin: 10px 0; border-bottom: 1px solid <?= $scheme_smoke_med ?>;}
    #settings-form table tr:last-child {border-bottom: 0;}
    #settings-form th {margin-bottom: 5px;}

    /* plugins */
    #plugins-menu .right {float: none;}
    .module-plugins .list .inline-list > li {display: block; padding: 0;}
    .module-plugins .list .inline-list > li:after {display: none;}

    /* articles */
    .module-content-articles-list .list > tbody > tr > td:first-child,
    .module-content-confirm .list > tbody > tr > td:first-child {min-width: 100px;}

    /* two-column layout */
    .two-columns {border: none;}
    .two-columns, .two-columns > tbody, .two-columns > tbody > tr, .two-columns > tbody > tr > td {display: block; width: auto; margin: 0; padding: 0;}
    .two-columns > tbody > tr > td {margin-bottom: 20px;}

    /* form tables */
    .formtable,
    .formtable > *,
    .formtable > * > tr,
    .formtable > * > tr > td,
    .formtable > * > tr > th {display: block;}
    .formtable > * > tr > th {margin-top: 10px; text-align: left;}
    .formtable :is(
        .areasmall,
        .areasmallwide,
        .areamedium,
        .areabig,
        .areabigperex,
        .inputbig,
        .selectbig,
        select:not(.inputmax)
    ) {width: 80%;}

    .edittable .main-box > table,
    .edittable .main-box > table > *,
    .edittable .main-box > table > *,
    .edittable .main-box > table > * > tr,
    .edittable .main-box > table > * > tr > td,
    .edittable .main-box > table > * > tr > th {display: block;}
    .edittable .main-box > table > * > tr > th {margin-top: 10px; text-align: left;}
    .edittable select + select {margin-top: 5px;}

    .form-box {width: auto !important; border-right: none;}

    /* form element sizes */
    .inputmedium, .selectmedium {width: 190px;}
    .inputbig {width: 100%;}

    /* buttons */
    *:has(> .button + .button:not(.block)) > .button {margin-bottom: 5px !important;}
    td.actions a.button:has(.icon) {width: 14px; overflow: hidden;}
    td.actions button.button:has(.icon) {width: 26px; overflow: hidden;}

    /* list */
    .list-half {width: 100%;}

    /*  horizontal scroller */
    .horizontal-scroller {overflow-x: auto;}
    fieldset .horizontal-scroller {max-width: calc(100vw - 40px);}

    /* tables */
    .table-collapse,
    .table-collapse > *,
    .table-collapse > * > tr,
    .table-collapse > * > tr > th,
    .table-collapse > * > tr > td {display: block; width: auto !important;}
    .table-collapse > * > tr > td:not(:last-child) {margin-bottom: 10px;}
    .table-collapse > * > tr > th {text-align: left;}
    .table-collapse > * > tr > th:empty {display: none;}

    /* generic */
    .desktop-only {display: none !important;}
    .user-link {white-space: nowrap;}
    .mobile-text-wrap {overflow-wrap: anywhere;}
    .mobile-spacer {height: 5px;}
}

/* responsive styles < 420px */
@media all and (max-width: 419px) {
    .login-layout #top {background-size: 90%;}
}

/* VoxCMS design foundation */
:root {
    color-scheme: <?= $dark ? 'dark' : 'light' ?>;
    --vox-bg: <?= $dark ? '#17191c' : '#f3f4f6' ?>;
    --vox-surface: <?= $dark ? '#222529' : '#ffffff' ?>;
    --vox-surface-raised: <?= $dark ? '#292d32' : '#ffffff' ?>;
    --vox-text: <?= $dark ? '#e8eaed' : '#25282c' ?>;
    --vox-text-muted: <?= $dark ? '#a6abb2' : '#686e76' ?>;
    --vox-border: <?= $dark ? '#383d43' : '#dfe2e6' ?>;
    --vox-sidebar-bg: #222a35;
    --vox-sidebar-border: #313b49;
    --vox-sidebar-text: #c5cbd4;
    --vox-sidebar-muted: #929ca9;
    --vox-topbar-bg: #1d232c;
    --vox-accent: <?= $scheme_link ?>;
    --vox-accent-hover: <?= $dark ? '#ff743d' : '#d94308' ?>;
    --vox-focus: <?= $dark ? 'rgba(255, 111, 54, .42)' : 'rgba(248, 74, 0, .25)' ?>;
    --vox-info-bg: <?= $dark ? '#173c40' : '#e8f5f5' ?>;
    --vox-warning-bg: <?= $dark ? '#493819' : '#fff5df' ?>;
    --vox-danger-bg: <?= $dark ? '#482727' : '#fff0f0' ?>;
    --vox-success-bg: <?= $dark ? '#1e3a2a' : '#edf8f0' ?>;
    --vox-info-border: <?= $dark ? '#286b70' : '#b8dfe1' ?>;
    --vox-warning-border: <?= $dark ? '#80612c' : '#ead19e' ?>;
    --vox-danger-border: <?= $dark ? '#803e3e' : '#e8b8b8' ?>;
    --vox-success-border: <?= $dark ? '#376c4b' : '#b9dfc5' ?>;
    --vox-shadow-sm: <?= $dark ? '0 1px 3px rgba(0, 0, 0, .28)' : '0 1px 3px rgba(24, 31, 38, .08)' ?>;
    --vox-radius-sm: 5px;
    --vox-radius: 8px;
    --vox-space-1: 4px;
    --vox-space-2: 8px;
    --vox-space-3: 12px;
    --vox-space-4: 16px;
    --vox-space-6: 24px;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    color: var(--vox-text);
    background: var(--vox-bg);
}
#header { font-family: inherit; font-size: inherit; }
#content {
    background-color: var(--vox-surface);
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius);
    box-shadow: var(--vox-shadow-sm);
}

a { color: var(--vox-accent); }
a:hover { color: var(--vox-accent-hover); }
h1 { font-size: 1.75rem; font-weight: 600; line-height: 1.25; margin-bottom: var(--vox-space-4); }
h2 { font-size: 1.25rem; font-weight: 600; margin: var(--vox-space-3) 0; }
h3 { font-size: 1rem; font-weight: 600; margin: var(--vox-space-3) 0; }
small { color: var(--vox-text-muted); }

input[type=text], input[type=password], input[type=number], input[type=email],
input[type=search], input[type=datetime-local], select, textarea {
    min-height: 36px;
    padding: 7px 10px;
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius-sm);
    background: var(--vox-surface);
    color: var(--vox-text);
    box-shadow: none;
    font: inherit;
}
textarea { min-height: 90px; line-height: 1.5 !important; }
input[type=checkbox], input[type=radio] { accent-color: var(--vox-accent); }
input[type=submit], input[type=button], input[type=reset], button, .button {
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius-sm);
    background: var(--vox-surface-raised);
    color: var(--vox-text);
    box-shadow: var(--vox-shadow-sm);
    font: inherit;
    transition: background-color .15s ease, border-color .15s ease, color .15s ease;
}
input[type=submit]:hover, input[type=button]:hover, input[type=reset]:hover, button:hover, .button:hover {
    border-color: var(--vox-accent);
    background: <?= $dark ? '#35393e' : '#fff4ef' ?>;
    color: var(--vox-text);
}
input:focus-visible, select:focus-visible, textarea:focus-visible, button:focus-visible, a:focus-visible {
    outline: 2px solid var(--vox-accent);
    outline-offset: 2px;
    box-shadow: 0 0 0 3px var(--vox-focus);
}

fieldset {
    padding: var(--vox-space-4);
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius);
    background: var(--vox-surface);
}
fieldset fieldset { background: var(--vox-surface-raised); }
legend { padding: 0 var(--vox-space-2); }
table { border-color: var(--vox-border); }
td, th { padding: var(--vox-space-2); }
th { color: var(--vox-text); }
tr { border-color: var(--vox-border); }
.table, .list, .formtable { border-color: var(--vox-border); }
.table tr:nth-child(even), .list tr:nth-child(even) { background-color: <?= $dark ? '#25292d' : '#f8f9fa' ?>; }
.table tr:hover, .list tr:hover { background-color: <?= $dark ? '#2b3035' : '#f1f3f5' ?>; }

.message {
    border-radius: var(--vox-radius-sm);
    border: 1px solid var(--vox-border);
}
.message-ok { background-color: var(--vox-success-bg); border-color: var(--vox-success-border); }
.message-warn { background-color: var(--vox-warning-bg); border-color: var(--vox-warning-border); }
.message-err { background-color: var(--vox-danger-bg); border-color: var(--vox-danger-border); }

/* Apply the design tokens to the existing SunLight administration markup. */
html, body { min-height: 100%; }
body { margin: 0; }
#top {
    background: var(--vox-surface);
    border-bottom: 1px solid var(--vox-border);
    box-shadow: var(--vox-shadow-sm);
}
#header {
    display: flex;
    align-items: center;
    gap: var(--vox-space-3);
    min-height: 68px;
    padding: 12px 24px;
    color: var(--vox-text);
}
#title {
    color: var(--vox-text);
    font-size: 1.125rem;
    font-weight: 600;
    letter-spacing: -.01em;
}
#usermenu {
    position: static;
    top: auto;
    display: flex;
    align-items: center;
    gap: var(--vox-space-2);
    margin-left: auto;
    color: var(--vox-text-muted);
}
#usermenu, #usermenu a { color: var(--vox-text-muted); font-family: inherit; }
#usermenu a#usermenu-username { color: var(--vox-text); }
#usermenu-avatar { position: relative; inset: auto; flex: 0 0 32px; }
#menu {
    display: flex;
    flex-wrap: wrap;
    gap: var(--vox-space-1);
    padding: 0 24px 12px;
    background: var(--vox-surface);
    font-size: 14px;
}
#menu a {
    display: inline-flex;
    align-items: center;
    border-radius: var(--vox-radius-sm);
    color: var(--vox-text-muted);
    background: transparent;
    transition: color .15s ease, background-color .15s ease;
}
#menu a span {
    padding: 9px 13px;
    background: none;
    font-size: 14px;
    font-weight: 500;
    text-shadow: none;
}
#menu a:hover, #menu a:hover span {
    color: var(--vox-text);
    background: <?= $dark ? '#30343a' : '#f1f2f4' ?>;
}
#menu a.act, #menu a.act span {
    color: #fff;
    background: var(--vox-accent);
    text-shadow: none;
}
#page { padding: 28px 20px; }
#content {
    min-width: 0;
    padding: 26px;
    color: var(--vox-text);
    background: var(--vox-surface);
}
#content h1 { color: var(--vox-text); }
#footer { margin: 0 auto 20px; }
#footer-content {
    padding: 14px 20px;
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius);
    background: var(--vox-surface);
    box-shadow: var(--vox-shadow-sm);
}
#footer, #footer a { color: var(--vox-text-muted); text-shadow: none; }

/* Dashboard cards use the legacy table structure, restyled in place. */
#index-table {
    width: calc(100% + 20px);
    margin: -10px;
    border-collapse: separate;
    border-spacing: 10px;
}
#index-table > tbody > tr > td {
    width: auto;
    padding: 22px;
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius);
    background: var(--vox-surface-raised);
    box-shadow: var(--vox-shadow-sm);
}
#index-table > tbody > tr > td:last-child { width: 34%; }
#index-table h1 { margin: 0 0 var(--vox-space-3); font-size: 1.6rem; }
#index-table h2 {
    margin: 0 0 var(--vox-space-3);
    padding: 0 0 var(--vox-space-3);
    border-bottom: 1px solid var(--vox-border);
    color: var(--vox-text);
    font-size: 1rem;
}
#index-table p { color: var(--vox-text-muted); }
#index-table table { border-collapse: collapse; }
#index-table table th, #index-table table td {
    padding: 9px 4px;
    border-bottom: 1px solid var(--vox-border);
    color: var(--vox-text);
}
#index-table table tr:last-child th, #index-table table tr:last-child td { border-bottom: 0; }
.well {
    margin: var(--vox-space-4) 0;
    padding: var(--vox-space-4);
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius);
    background: var(--vox-surface-raised);
}
.well h2 { font-weight: 600; }

/* Existing form, table and message selectors now consume the token palette. */
input[type=text], input[type=password], input[type=number], input[type=email],
input[type=search], input[type=datetime-local], select, textarea {
    background-color: var(--vox-surface);
    color: var(--vox-text);
}
select { background-color: var(--vox-surface); }
input[type=submit], input[type=button], input[type=reset], button, .button {
    cursor: pointer;
    min-height: 36px;
    padding: 8px 13px;
    border: 1px solid var(--vox-border);
    border-radius: var(--vox-radius-sm);
    background: var(--vox-surface-raised);
    color: var(--vox-text);
    font-weight: 500;
}
input[type=submit], .button.bigger {
    border-color: var(--vox-accent);
    background: var(--vox-accent);
    color: #fff;
}
input[type=submit]:hover, .button.bigger:hover {
    border-color: var(--vox-accent-hover);
    background: var(--vox-accent-hover);
    color: #fff;
}
input[type=button]:hover, input[type=reset]:hover, button:hover, .button:hover {
    border-color: var(--vox-accent);
    background: <?= $dark ? '#30343a' : '#fff3ed' ?>;
    color: var(--vox-text);
}
fieldset { margin: var(--vox-space-4) 0; }
.message {
    padding: 13px 14px 13px 48px;
    border-radius: var(--vox-radius-sm);
    border-width: 1px;
    background-color: var(--vox-surface-raised);
    color: var(--vox-text);
}
.message-ok { background-color: var(--vox-success-bg); border-color: var(--vox-success-border); }
.message-warn { background-color: var(--vox-warning-bg); border-color: var(--vox-warning-border); }
.message-err { background-color: var(--vox-danger-bg); border-color: var(--vox-danger-border); }

@media all and (max-width: 999px) {
    #header { min-height: 0; padding: 0; flex-wrap: wrap; gap: 0; }
    #usermenu { order: 1; width: 100%; padding: 10px 16px; }
    #title {
        order: 3;
        width: 100%;
        padding: 10px 16px;
        background: var(--vox-surface);
        color: var(--vox-text);
        border-top: 1px solid var(--vox-border);
    }
    #menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 200;
        width: min(300px, calc(100vw - 32px));
        max-width: none;
        padding: 10px;
        border: 1px solid var(--vox-border);
        border-radius: var(--vox-radius);
        background: var(--vox-surface);
        box-shadow: var(--vox-shadow-sm);
    }
    #menu a { display: flex; }
    #menu a span { width: 100%; }
    #menu a.act, #menu a.act span { color: #fff; background: var(--vox-accent); }
    #page { padding: 18px 12px; }
    #content { padding: 20px; }
    #footer { padding: 0 12px; }
    #index-table { width: calc(100% + 12px); margin: -6px; border-spacing: 6px; }
    #index-table > tbody > tr > td:last-child { width: auto; }
}

@media all and (max-width: 599px) {
    #index-table, #index-table > tbody, #index-table > tbody > tr,
    #index-table > tbody > tr > td { display: block; width: auto !important; }
    #index-table > tbody > tr > td { margin: 0 0 12px; }
    #footer-content { display: flex; flex-direction: column; gap: var(--vox-space-2); }
    #footer-links { float: none; }
}

/* VoxCMS application shell */
body { background: var(--vox-bg); color: var(--vox-text); }
#container.vox-admin-shell { min-height: 100vh; background: var(--vox-bg); }
#top.vox-topbar {
    position: sticky;
    top: 0;
    z-index: 300;
    width: 100%;
    border: 0;
    background: var(--vox-topbar-bg);
    box-shadow: 0 2px 12px rgba(0, 0, 0, .16);
}
.vox-topbar-inner {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    min-height: 72px;
    padding: 0 24px;
    color: #f4f6f8;
    font-family: inherit;
    font-size: 14px;
}
.vox-brand {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #f8fafc;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -.025em;
}
.vox-brand:hover { color: #fff; }
.vox-brand-mark {
    display: inline-grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 11px;
    background: var(--vox-accent);
    color: #fff;
    font-size: 22px;
    font-weight: 700;
}
.vox-icon-button {
    display: inline-grid;
    width: 40px;
    height: 40px;
    min-height: 40px;
    padding: 0;
    place-items: center;
    border: 1px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: #e7ebf0;
    box-shadow: none;
}
.vox-icon-button svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.vox-icon-button:hover { border-color: #3a4451; background: #2b333e; color: #fff; }
.vox-icon-button:focus-visible { outline-color: #ff934f; box-shadow: 0 0 0 3px rgba(255, 111, 54, .3); }
.vox-topbar-actions { display: flex; align-items: center; gap: 14px; margin-left: auto; }
.vox-admin-layout { display: flex; min-height: calc(100vh - 72px); align-items: stretch; }
.vox-sidebar {
    position: sticky;
    top: 72px;
    z-index: 250;
    flex: 0 0 258px;
    width: 258px;
    height: calc(100vh - 72px);
    overflow: auto;
    border-right: 1px solid var(--vox-sidebar-border);
    background: var(--vox-sidebar-bg);
    color: var(--vox-sidebar-text);
    transition: width .2s ease, flex-basis .2s ease, transform .2s ease;
}
#admin-sidebar #menu.vox-sidebar-nav {
    position: static;
    display: flex;
    width: auto;
    max-width: none;
    flex-direction: column;
    gap: 22px;
    padding: 20px 14px 28px;
    border: 0;
    background: transparent;
    font-size: 14px;
}
.vox-nav-group { display: flex; flex-direction: column; gap: 4px; }
.vox-nav-heading {
    margin: 0 0 4px;
    padding: 0 10px;
    color: var(--vox-sidebar-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .09em;
    line-height: 1.4;
    text-transform: uppercase;
}
#menu a.vox-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 42px;
    padding: 0 10px;
    border-radius: 9px;
    background: transparent;
    color: var(--vox-sidebar-text);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: color .15s ease, background-color .15s ease;
}
#menu a.vox-nav-link:hover { background: #303946; color: #fff; }
#menu a.vox-nav-link.is-active,
#menu a.vox-nav-link.is-active:hover { background: var(--vox-accent); color: #fff; }
#admin-sidebar #menu a.vox-nav-link span {
    display: inline;
    padding: 0;
    background: none;
    font-size: inherit;
    text-shadow: none;
}
.vox-nav-icon { flex: 0 0 19px; width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
.vox-nav-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.vox-main { display: flex; flex: 1 1 auto; min-width: 0; flex-direction: column; }
#page.wrapper { width: 100%; max-width: none; flex: 1 0 auto; margin: 0; padding: 28px clamp(18px, 3vw, 42px); }
#content {
    width: 100%;
    min-width: 0;
    min-height: calc(100vh - 180px);
    margin: 0;
    padding: clamp(22px, 3vw, 38px);
    border: 1px solid var(--vox-border);
    border-radius: 14px;
    background: var(--vox-surface);
    box-shadow: var(--vox-shadow-sm);
}
#content h1 { margin: 0 0 14px; color: var(--vox-text); font-size: 1.8rem; font-weight: 650; letter-spacing: -.025em; }
#content h2 { color: var(--vox-text); }
#footer.wrapper { width: 100%; max-width: none; margin: 0; padding: 0 clamp(18px, 3vw, 42px) 22px; }
#footer-content { padding: 14px 4px 2px; border: 0; border-top: 1px solid var(--vox-border); border-radius: 0; background: transparent; box-shadow: none; }
#footer, #footer a { color: var(--vox-text-muted); font-size: 12px; text-shadow: none; }
#footer a:hover { color: var(--vox-accent); }
.vox-sidebar-overlay { display: none; }

/* Account popover; its destination links retain the original profile/settings/logout routes. */
details#usermenu { position: relative; top: auto; display: block; float: none; margin: 0; color: #f4f6f8; }
#usermenu > summary {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 4px 2px 4px 8px;
    border-radius: 10px;
    color: #f4f6f8;
    cursor: pointer;
    list-style: none;
}
#usermenu > summary::-webkit-details-marker { display: none; }
#usermenu > summary:hover { background: #2b333e; }
#usermenu-avatar {
    display: inline-grid;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    place-items: center;
    overflow: hidden;
    border: 0;
    border-radius: 50%;
    background: #343e4b;
    color: #fff;
}
img#usermenu-avatar { object-fit: cover; }
.vox-user-avatar-fallback svg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
#usermenu-username { margin: 0; color: #f4f6f8; font-size: 14px; font-weight: 600; }
.vox-user-chevron { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 1.7; }
#usermenu-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    z-index: 400;
    display: flex;
    width: 210px;
    flex-direction: column;
    padding: 6px;
    border: 1px solid var(--vox-border);
    border-radius: 10px;
    background: var(--vox-surface);
    box-shadow: 0 12px 32px rgba(0, 0, 0, .2);
}
#usermenu-dropdown a { padding: 10px; border-radius: 7px; color: var(--vox-text); font-size: 13px; text-decoration: none; }
#usermenu-dropdown a:hover { background: var(--vox-bg); color: var(--vox-accent); }

/* Compact desktop sidebar */
#container.vox-sidebar-collapsed .vox-sidebar { flex-basis: 76px; width: 76px; }
#container.vox-sidebar-collapsed .vox-sidebar-nav { padding-right: 10px; padding-left: 10px; }
#container.vox-sidebar-collapsed .vox-nav-heading,
#container.vox-sidebar-collapsed .vox-nav-label { display: none; }
#container.vox-sidebar-collapsed #admin-sidebar #menu a.vox-nav-link .vox-nav-label,
#container.vox-sidebar-collapsed #admin-sidebar .vox-nav-heading { display: none; }
#container.vox-sidebar-collapsed #menu a.vox-nav-link { justify-content: center; padding: 0; }
#container.vox-sidebar-collapsed .vox-nav-icon { width: 21px; height: 21px; flex-basis: 21px; }

/* Home dashboard */
.vox-dashboard-welcome { margin-bottom: 22px; }
.vox-dashboard-welcome p { margin: 0; color: var(--vox-text-muted); font-size: 15px; }
.vox-dashboard-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 20px 0 24px; }
.vox-stat-card {
    display: flex;
    min-height: 126px;
    flex-direction: column;
    justify-content: space-between;
    padding: 17px 18px;
    border: 1px solid var(--vox-border);
    border-radius: 12px;
    background: var(--vox-surface-raised);
    box-shadow: var(--vox-shadow-sm);
    color: var(--vox-text);
    text-decoration: none;
    transition: border-color .15s ease, transform .15s ease;
}
a.vox-stat-card:hover { transform: translateY(-2px); border-color: var(--vox-accent); color: var(--vox-text); }
.vox-stat-icon { width: 21px; height: 21px; color: var(--vox-accent); }
.vox-stat-icon svg { width: 100%; height: 100%; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.vox-stat-value { margin-top: 12px; font-size: 28px; font-weight: 700; letter-spacing: -.03em; line-height: 1; }
.vox-stat-label { margin-top: 6px; color: var(--vox-text-muted); font-size: 13px; }
.vox-dashboard-panels { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(260px, .8fr); gap: 16px; }
.vox-dashboard-panel { min-width: 0; padding: 20px; border: 1px solid var(--vox-border); border-radius: 12px; background: var(--vox-surface-raised); }
.vox-dashboard-panel h2 { margin: 0 0 15px; padding: 0 0 12px; border-bottom: 1px solid var(--vox-border); font-size: 1rem; }
.vox-activity-list { margin: 0; padding: 0; list-style: none; }
.vox-activity-list li { display: flex; align-items: baseline; justify-content: space-between; gap: 14px; padding: 10px 0; border-bottom: 1px solid var(--vox-border); }
.vox-activity-list li:last-child { border-bottom: 0; }
.vox-activity-list a { color: var(--vox-text); font-weight: 550; }
.vox-activity-list a:hover { color: var(--vox-accent); }
.vox-activity-meta { flex: 0 0 auto; color: var(--vox-text-muted); font-size: 12px; }
.vox-quick-links { display: flex; flex-wrap: wrap; gap: 8px; }
.vox-quick-links .button { display: inline-flex; align-items: center; min-height: 38px; }

@media all and (max-width: 1100px) {
    .vox-dashboard-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media all and (max-width: 999px) {
    #top.vox-topbar { position: sticky; }
    .vox-topbar-inner { min-height: 64px; padding: 0 14px; }
    .vox-brand { font-size: 18px; }
    .vox-brand-mark { width: 34px; height: 34px; }
    .vox-admin-layout { min-height: calc(100vh - 64px); }
    .vox-sidebar {
        position: fixed;
        top: 64px;
        bottom: 0;
        left: 0;
        width: min(290px, calc(100vw - 54px));
        height: auto;
        transform: translateX(-105%);
        box-shadow: 12px 0 32px rgba(0, 0, 0, .22);
    }
    body.vox-sidebar-open .vox-sidebar { transform: translateX(0); }
    #admin-sidebar #menu.vox-sidebar-nav {
        position: static;
        display: flex;
        width: auto;
        max-width: none;
        flex-direction: column;
        padding: 16px 12px 24px;
        border: 0;
        background: transparent;
    }
    #container.vox-sidebar-collapsed .vox-sidebar { width: min(290px, calc(100vw - 54px)); }
    #container.vox-sidebar-collapsed .vox-nav-heading,
    #container.vox-sidebar-collapsed .vox-nav-label { display: block; }
    #container.vox-sidebar-collapsed #menu a.vox-nav-link { justify-content: flex-start; padding: 0 10px; }
    .vox-sidebar-overlay {
        position: fixed;
        inset: 64px 0 0;
        z-index: 240;
        display: none;
        width: 100%;
        min-height: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: rgba(10, 14, 19, .48);
        box-shadow: none;
    }
    body.vox-sidebar-open .vox-sidebar-overlay { display: block; }
    #page.wrapper { padding: 18px 14px; }
    #content { min-height: calc(100vh - 120px); padding: 22px; }
    #footer.wrapper { padding: 0 14px 18px; }
    .vox-dashboard-panels { grid-template-columns: 1fr; }
}
@media all and (max-width: 599px) {
    .vox-topbar-inner { gap: 8px; }
    .vox-topbar-actions { gap: 5px; }
    #usermenu > summary { gap: 6px; padding-left: 2px; }
    #usermenu-username { display: none; }
    #usermenu-avatar { width: 34px; height: 34px; flex-basis: 34px; }
    .vox-dashboard-grid { grid-template-columns: 1fr 1fr; gap: 9px; }
    .vox-stat-card { min-height: 112px; padding: 13px; }
    .vox-stat-value { font-size: 24px; }
    .vox-dashboard-panel { padding: 15px; }
    #content { padding: 18px 14px; border-radius: 10px; }
    .vox-activity-list li { align-items: flex-start; flex-direction: column; gap: 4px; }
}

/* Keep wide legacy module content inside the VoxCMS work area. */
html, body, #container.vox-admin-shell { width: 100%; max-width: 100%; min-width: 0; }
#container.vox-admin-shell, #container.vox-admin-shell *,
#container.vox-admin-shell *::before, #container.vox-admin-shell *::after { box-sizing: border-box; }
body { overflow-x: clip; }
#container.vox-admin-shell { overflow-x: clip; }
#top.vox-topbar, .vox-topbar-inner, .vox-admin-layout, .vox-main,
#page.wrapper, #content, #footer.wrapper, #footer-content {
    min-width: 0;
    max-width: 100%;
}
#header.vox-topbar-inner { min-height: 72px; padding: 0 24px; flex-wrap: nowrap; }
.vox-admin-layout { width: 100%; }
.vox-sidebar { box-sizing: border-box; flex-shrink: 0; }
.vox-main { width: 0; }
#page.wrapper { flex: 1 1 auto; width: auto; box-sizing: border-box; background: transparent; overflow: visible; }
#content { width: 100%; box-sizing: border-box; }
#footer.wrapper { width: auto; box-sizing: border-box; }
.vox-dashboard-grid, .vox-dashboard-panels,
.vox-stat-card, .vox-dashboard-panel { min-width: 0; max-width: 100%; }
.vox-dashboard-grid > *, .vox-dashboard-panels > * { min-width: 0; }
#content form, #content input, #content select, #content textarea { max-width: 100%; }
.inputbig { max-width: 100%; }
.horizontal-scroller, .page-list-container {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: auto;
    overscroll-behavior-x: contain;
}
.horizontal-scroller > table, .page-list-container > table { max-width: none; }
#content img, #content video, #content iframe { max-width: 100%; }

/* Keep the navigation scrollable without letting its scrollbar dominate the rail. */
.vox-sidebar { scrollbar-width: thin; scrollbar-color: var(--vox-sidebar-muted) var(--vox-sidebar-bg); }
.vox-sidebar::-webkit-scrollbar { width: 8px; }
.vox-sidebar::-webkit-scrollbar-track { background: var(--vox-sidebar-bg); }
.vox-sidebar::-webkit-scrollbar-thumb { border: 2px solid var(--vox-sidebar-bg); border-radius: 8px; background: var(--vox-sidebar-muted); }

/* The legacy content dashboard used a fixed 80/20 table split. Use a fluid grid instead. */
#contenttable { display: block; width: 100%; border: 0; background: transparent; }
#contenttable > tbody { display: block; width: 100%; }
#contenttable > tbody > tr {
    display: grid;
    width: 100%;
    grid-template-columns: minmax(0, 1fr) minmax(250px, 290px);
    align-items: start;
    gap: 18px;
}
#contenttable > tbody > tr > td {
    display: block;
    width: auto;
    min-width: 0;
    max-width: 100%;
    margin: 0;
    padding: 16px;
    overflow-wrap: anywhere;
    border: 1px solid var(--vox-border);
    border-radius: 12px;
    background: var(--vox-surface-raised);
}
#contenttable .main-box { width: auto; }
#contenttable-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; line-height: 1.5; }
#contenttable-actions .inline { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 8px; max-width: 100%; }
#contenttable-actions .inline-separator { display: none; }
#contenttable .page-list-container { margin-top: 12px; }
#content-modules { width: auto; padding-top: 8px; }
#content-modules > div { min-width: 0; max-width: 100%; }
#content-modules h2 { margin-top: 18px; }
#content-modules h2:first-child { margin-top: 0; }
.page-list { table-layout: fixed; white-space: normal; }
.page-list td { min-width: 0; white-space: normal; overflow-wrap: anywhere; }
.page-list .page-title { width: auto; }
.page-list .page-actions { white-space: normal; }
@media all and (max-width: 1599px) {
    #contenttable > tbody > tr { grid-template-columns: minmax(0, 1fr); gap: 14px; }
    #content-modules { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 18px; }
    #content-modules > h2 { grid-column: 1 / -1; }
    .two-columns, .two-columns > tbody, .two-columns > tbody > tr, .two-columns > tbody > tr > td {
        display: block;
        width: auto;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    .two-columns > tbody > tr > td { margin-bottom: 18px; }

    #settings-container { display: block; width: 100%; }
    #settings-nav { position: static; width: 100%; margin: 0 0 18px; }
    #settings-nav ul { display: flex; flex-wrap: wrap; gap: 5px; border: 0; background: transparent; }
    #settings-nav li { display: block; flex: 0 1 auto; max-width: 100%; }
    #settings-nav li a { padding: 7px 9px; border: 1px solid var(--vox-border); border-radius: 7px; background: var(--vox-surface-raised); }
    #settings-form { width: 100%; min-width: 0; }
    #settings-form fieldset { min-width: 0; max-width: 100%; }
    #settings-form table { width: 100%; table-layout: fixed; }
    #settings-form th { width: 30%; }
    #settings-form td { min-width: 0; overflow-wrap: anywhere; }
    #settings-form .CodeMirror, #settings-form .CodeMirror-scroll { max-width: 100%; }
}
@media all and (max-width: 999px) {
    #contenttable > tbody > tr { grid-template-columns: minmax(0, 1fr); }
    #contenttable > tbody > tr > td { margin: 0 0 12px; padding: 14px; }
    #content-modules { display: block; }
    #settings-form th { width: auto; }
    #header.vox-topbar-inner { min-height: 64px; padding: 0 14px; flex-wrap: nowrap; gap: 8px; }
    #footer.wrapper { position: static; margin: 0; }
    .vox-main { width: 100%; }
    #page.wrapper { width: 100%; }
}
@media all and (max-width: 599px) {
    #contenttable > tbody > tr > td { padding: 12px; }
    #contenttable-actions { align-items: stretch; }
    #contenttable-actions .inline { width: 100%; }
    #contenttable-actions .button,
    #contenttable-actions input[type="submit"],
    #contenttable-actions select { max-width: 100%; }
    #content-modules .button.block { width: 100%; }
}

<?= Extend::buffer('admin.style') ?>
