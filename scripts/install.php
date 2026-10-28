<?php
/** Standalone PHP CLI bootstrap, before WordPress has database tables. */
if (PHP_SAPI !== 'cli') { exit; }
$password=getenv('WP_ADMIN_PASSWORD');
if (!$password || strlen($password)<16) { throw new RuntimeException('Use an admin password of at least 16 characters.'); }
$url=getenv('WP_URL') ?: 'http://localhost:8080';
$_SERVER['HTTP_HOST']=parse_url($url,PHP_URL_HOST).(parse_url($url,PHP_URL_PORT) ? ':'.parse_url($url,PHP_URL_PORT) : '');
$_SERVER['REQUEST_URI']='/';
define('WP_INSTALLING',true);
require '/var/www/html/wp-load.php';
require_once ABSPATH.'wp-admin/includes/upgrade.php';
// Local installation must not send a credential email.
add_filter('pre_wp_mail','__return_true');
if (!is_blog_installed()) {
    $result=wp_install('Garib Appliance World',sanitize_user(getenv('WP_ADMIN_USER') ?: 'storeadmin'),sanitize_email(getenv('WP_ADMIN_EMAIL') ?: 'admin@example.test'),false,'',$password);
    if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    update_option('siteurl',esc_url_raw($url)); update_option('home',esc_url_raw($url));
    echo "WordPress installed.\n";
}
