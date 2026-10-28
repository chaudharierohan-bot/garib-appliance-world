<?php
if(!defined('WP_CLI') || !WP_CLI)exit;
function gaw_require($condition,$message){if(!$condition)WP_CLI::error($message);}
gaw_require(get_option('woocommerce_currency')==='SRD','Store currency must be SRD.');
gaw_require(get_option('woocommerce_default_country')==='SR','Store country must be Suriname.');
foreach(gaw_categories() as $slug=>$name)gaw_require((bool)get_term_by('slug',$slug,'product_cat'),'Missing category '.$slug);
$products=wc_get_products(['limit'=>-1,'status'=>'publish']);
$demo=array_filter($products,fn($p)=>str_starts_with($p->get_sku(),'DEMO-'));
gaw_require(count($demo)===12,'Expected 12 demonstration products.');
foreach($demo as $p)gaw_require((bool)$p->get_image_id(),'Demo product has no image.');
$seen=[];
foreach(wc_get_orders(['limit'=>30,'orderby'=>'date','order'=>'DESC']) as $order){
 if(!str_ends_with($order->get_billing_email(),'@example.test'))continue;
 $method=$order->get_payment_method();if(!in_array($method,['cod','bacs'],true))continue;
 gaw_require($order->get_currency()==='SRD','Order currency mismatch.');
 gaw_require($order->has_status($method==='cod'?'processing':'on-hold'),'Unexpected '.$method.' order status.');
 gaw_require((float)$order->get_shipping_total()===150.0,'Demo shipping rate mismatch.');
 gaw_require((float)$order->get_total()===(float)$order->get_subtotal()+150.0,'Order total mismatch.');
 $seen[$method]=true;
}
gaw_require(isset($seen['cod'],$seen['bacs']),'Both test payment methods must have persisted orders.');
$requests=get_posts(['post_type'=>'gaw_newsletter','post_status'=>'private','numberposts'=>1]);
gaw_require((bool)$requests,'No private newsletter request persisted.');
WP_CLI::success('Verified 12 illustrated demo products, 11 categories, SRD totals, persisted COD/transfer orders with expected statuses, shipping, and private newsletter storage.');
