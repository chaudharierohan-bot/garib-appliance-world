<?php
if (!defined('ABSPATH')) { exit; }
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary' => __('Primary navigation', 'garib-appliance-world')]);
});
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('gaw', get_theme_file_uri('/assets/css/store.css'), [], '1.0.0');
    wp_enqueue_script('gaw', get_theme_file_uri('/assets/js/store.js'), [], '1.0.0', true);
    if (class_exists('WooCommerce') && !is_account_page()) { wp_enqueue_script('wc-cart-fragments'); }
});
function gaw_categories() {
    return ['refrigerators-freezers'=>'Refrigerators & Freezers','washing-machines'=>'Washing Machines','air-conditioners'=>'Air Conditioners','televisions'=>'Televisions','kitchen-appliances'=>'Kitchen Appliances','small-household-appliances'=>'Small Household Appliances','electronics'=>'Electronics','smartphones-tablets'=>'Smartphones & Tablets','audio-entertainment'=>'Audio & Entertainment','home-living'=>'Home & Living','special-offers'=>'Special Offers'];
}
function gaw_category_url($slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    return $term ? get_term_link($term) : home_url('/shop/');
}
function gaw_cart_link() {
    if (!function_exists('WC')) { return; }
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    echo '<a class="cart-link" href="' . esc_url(wc_get_cart_url()) . '">Bag <span class="cart-count">' . esc_html($count) . '</span></a>';
}
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    ob_start(); gaw_cart_link(); $fragments['a.cart-link'] = ob_get_clean(); return $fragments;
});
add_filter('woocommerce_currency_symbol', function ($symbol, $currency) { return $currency === 'SRD' ? 'SRD ' : $symbol; }, 10, 2);
add_action('woocommerce_after_shop_loop_item_title', function () {
    global $product;
    echo '<span class="stock-label">' . esc_html($product->is_in_stock() ? __('In stock', 'garib-appliance-world') : __('Out of stock', 'garib-appliance-world')) . '</span>';
}, 15);
add_action('woocommerce_after_shop_loop_item', function () {
    echo '<a class="details-link" href="' . esc_url(get_permalink()) . '">' . esc_html__('View details', 'garib-appliance-world') . ' →</a>';
}, 15);
add_action('customize_register', function ($c) {
    $c->add_section('gaw_store', ['title' => __('Store promotions & contact', 'garib-appliance-world')]);
    foreach (['hero_title'=>'Better living. Better prices.','hero_copy'=>'Bring comfort home with appliances made for everyday life.','hero_second_title'=>'Small upgrades. Big difference.','hero_second_copy'=>'Discover kitchen essentials for every delicious moment.','whatsapp'=>''] as $key=>$default) {
        $c->add_setting('gaw_'.$key, ['default'=>$default,'sanitize_callback'=>'sanitize_text_field']);
        $c->add_control('gaw_'.$key, ['section'=>'gaw_store','label'=>ucwords(str_replace('_',' ',$key))]);
    }
    $c->add_setting('gaw_hero_image', ['sanitize_callback'=>'esc_url_raw']);
    $c->add_control(new WP_Customize_Image_Control($c,'gaw_hero_image',['section'=>'gaw_store','label'=>__('Main promotional image','garib-appliance-world')]));
});
// Requests are stored privately for administrator review. No marketing email is sent.
add_action('init', function () {
    register_post_type('gaw_newsletter', ['label'=>__('Newsletter requests','garib-appliance-world'),'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>['title'],'capabilities'=>['edit_post'=>'manage_options','read_post'=>'manage_options','delete_post'=>'manage_options','edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options','read_private_posts'=>'manage_options','create_posts'=>'do_not_allow'],'map_meta_cap'=>false]);
});
function gaw_newsletter_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { wp_die('Method not allowed.', '', ['response'=>405]); }
    check_admin_referer('gaw_newsletter','gaw_nonce');
    $email=isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (!is_email($email) || empty($_POST['consent']) || !empty($_POST['website'])) { wp_safe_redirect(add_query_arg('newsletter','invalid',home_url('/#newsletter'))); exit; }
    $ip_key='gaw_nl_'.hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? '', wp_salt());
    if(get_transient($ip_key)) { wp_safe_redirect(add_query_arg('newsletter','wait',home_url('/#newsletter'))); exit; }
    set_transient($ip_key,1,MINUTE_IN_SECONDS);
    $key=hash_hmac('sha256',strtolower($email),wp_salt());
    $found=get_posts(['post_type'=>'gaw_newsletter','post_status'=>'private','meta_key'=>'email_hash','meta_value'=>$key,'fields'=>'ids','posts_per_page'=>1]);
    if(!$found){$id=wp_insert_post(['post_type'=>'gaw_newsletter','post_status'=>'private','post_title'=>$email,'post_content'=>'Requested updates through storefront; pending mailing-service setup. Consent timestamp: '.gmdate('c')],true); if(is_wp_error($id)){wp_die('Could not save your request. Please try later.','',['response'=>503]);}update_post_meta($id,'email_hash',$key);}
    wp_safe_redirect(add_query_arg('newsletter','received',home_url('/#newsletter')));exit;
}
add_action('admin_post_gaw_newsletter','gaw_newsletter_request');
add_action('admin_post_nopriv_gaw_newsletter','gaw_newsletter_request');
// WooCommerce 10.3.4 migrates guest data without renewing an existing cookie.
// Renew it through the public hook so subsequent requests retain customer notices.
add_action('woocommerce_guest_session_to_user_id', function () {
    if (function_exists('WC') && WC()->session) {
        WC()->session->set_customer_session_cookie(true);
    }
}, 10, 0);
