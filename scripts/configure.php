<?php
/** Run with wp eval-file /setup/configure.php; local development only. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (!class_exists('WooCommerce')) { WP_CLI::error('Activate WooCommerce first.'); }
foreach (['woocommerce_coming_soon'=>'no','woocommerce_store_pages_only'=>'no','woocommerce_currency'=>'SRD','woocommerce_currency_pos'=>'left','woocommerce_price_num_decimals'=>2,'woocommerce_price_thousand_sep'=>',','woocommerce_price_decimal_sep'=>'.','woocommerce_default_country'=>'SR','woocommerce_default_customer_address'=>'base','woocommerce_allowed_countries'=>'specific','woocommerce_specific_allowed_countries'=>['SR'],'woocommerce_ship_to_countries'=>'','woocommerce_calc_taxes'=>'no','woocommerce_enable_guest_checkout'=>'yes','woocommerce_enable_myaccount_registration'=>'yes','woocommerce_enable_signup_and_login_from_checkout'=>'yes','woocommerce_registration_generate_username'=>'yes','woocommerce_registration_generate_password'=>'no','woocommerce_manage_stock'=>'yes','woocommerce_email_from_name'=>'Garib Appliance World'] as $key=>$value) { update_option($key,$value); }
update_option('woocommerce_cod_settings',['enabled'=>'yes','title'=>'Cash on delivery','description'=>'Pay in cash when your order is delivered. Demo store: no actual delivery.','instructions'=>'Please have the order amount ready.','enable_for_virtual'=>'yes']);
// Empty bank details deliberately stay empty until the owner configures real information.
$bacs=get_option('woocommerce_bacs_settings',[]);
update_option('woocommerce_bacs_settings',array_merge($bacs,['enabled'=>'yes','title'=>'Direct bank transfer','description'=>'Your order is placed on hold. Contact the store for verified payment instructions before transferring money.','instructions'=>'Bank information has not been configured. Do not transfer funds until verified instructions are provided.']));
WC_Install::create_pages();
// Classic forms use WooCommerce's complete supported checkout/account functionality.
foreach(['cart'=>'[woocommerce_cart]','checkout'=>'[woocommerce_checkout]','myaccount'=>'[woocommerce_my_account]'] as $key=>$content) {
    $id=wc_get_page_id($key); if($id>0) { wp_update_post(['ID'=>$id,'post_content'=>$content]); }
}
$page=get_page_by_path('home');
$id=$page ? $page->ID : wp_insert_post(['post_type'=>'page','post_title'=>'Home','post_name'=>'home','post_status'=>'publish']);
update_option('show_on_front','page'); update_option('page_on_front',$id);
foreach(['delivery-returns'=>['Delivery & returns','This development store does not fulfil real orders. Delivery areas, fees, warranty terms, and return policies must be confirmed and published by the store owner before launch.'],'privacy-policy'=>['Privacy policy','Development notice: checkout stores customer contact details, delivery addresses, account information and order records in this local WordPress database. Do not enter real personal or payment information. Newsletter requests store the submitted email and consent timestamp privately for administrator review. No marketing messages are sent until a mailing provider and consent workflow are configured. Replace this notice with the business privacy policy and retention details before launch.']] as $slug=>$data) {
    if(!get_page_by_path($slug)) { $new=wp_insert_post(['post_type'=>'page','post_title'=>$data[0],'post_name'=>$slug,'post_content'=>$data[1],'post_status'=>'publish']); if($slug==='privacy-policy')update_option('wp_page_for_privacy_policy',$new); }
}
// Install a local flat rate once, without duplicating zones on reruns.
$exists=false;
foreach(WC_Shipping_Zones::get_zones() as $z) { if($z['zone_name']==='Suriname (development)')$exists=true; }
if(!$exists){$zone=new WC_Shipping_Zone();$zone->set_zone_name('Suriname (development)');$zone->add_location('SR','country');$zone->save();$instance=$zone->add_shipping_method('flat_rate');update_option('woocommerce_flat_rate_'.$instance.'_settings',['title'=>'Local delivery — demonstration rate','tax_status'=>'none','cost'=>'150']);}
update_option('blogdescription','Everyday essentials. Extraordinary possibilities.');
update_option('blog_public','0');
update_option('permalink_structure','/%postname%/');flush_rewrite_rules();
WP_CLI::success('Suriname development store configured. Taxes disabled pending owner review; demonstration shipping SRD 150.');
