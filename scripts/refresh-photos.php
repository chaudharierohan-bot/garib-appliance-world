<?php
/** Import synthetic photographic demo placeholders without replacing owner-uploaded images. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
require_once ABSPATH . 'wp-admin/includes/image.php';
// Let WordPress create efficient WebP display sizes; original PNG assets stay unchanged.
add_filter('image_editor_output_format', function ($formats) {
    if (wp_image_editor_supports(['mime_type'=>'image/webp'])) { $formats['image/png'] = 'image/webp'; }
    return $formats;
});
$keys = ['fridge','washer','ac','tv','kitchen','audio','phone','home','hero'];
foreach ($keys as $key) {
    $source = get_theme_file_path('/assets/images/demo-' . $key . '.png');
    if (!is_readable($source)) { WP_CLI::error('Missing demo photo: ' . $key); }
    $hash = hash_file('sha256', $source);
    $id = (int) get_option('gaw_demo_photo_' . $key);
    if (!$id || !get_post($id) || get_post_meta($id, '_gaw_demo_asset_sha', true) !== $hash) {
        $upload = wp_upload_bits('gaw-demo-' . $key . '.png', null, file_get_contents($source));
        if ($upload['error']) { WP_CLI::error($upload['error']); }
        $id = wp_insert_attachment(['post_mime_type'=>'image/png','post_title'=>'Photorealistic demonstration placeholder — ' . $key,'post_status'=>'inherit','post_content'=>'AI-generated temporary demonstration image. Not an actual manufacturer or inventory photograph.'], $upload['file'], 0, true);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        
        update_post_meta($id, '_wp_attachment_image_alt', 'Photorealistic demonstration placeholder: ' . $key);
        update_post_meta($id, '_gaw_demo_asset_sha', $hash);
        update_option('gaw_demo_photo_' . $key, $id);
    }
    if (get_post_meta($id, '_gaw_demo_media_version', true) !== '2') {
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, get_attached_file($id)));
        update_post_meta($id, '_gaw_demo_media_version', '2');
    }
}
$map = ['RF01'=>'fridge','RF02'=>'fridge','WM01'=>'washer','WM02'=>'washer','AC01'=>'ac','TV01'=>'tv','KT01'=>'kitchen','SM01'=>'kitchen','EL01'=>'audio','AU01'=>'audio','PH01'=>'phone','HM01'=>'home'];
foreach ($map as $sku=>$key) {
    $product_id = wc_get_product_id_by_sku('DEMO-' . $sku);
    if (!$product_id) { continue; }
    $product = wc_get_product($product_id);
    $old_id = $product->get_image_id();
    $new_id = (int) get_option('gaw_demo_photo_' . $key);
    $title = $old_id ? get_the_title($old_id) : '';
    if ($old_id && $old_id !== $new_id && !str_contains($title, 'original demonstration illustration') && !get_post_meta($old_id, '_gaw_demo_asset_sha', true)) {
        WP_CLI::log('Preserved owner-supplied image for DEMO-' . $sku);
        continue;
    }
    $product->set_image_id($new_id);
    if ($product->get_gallery_image_ids() === [$old_id]) { $product->set_gallery_image_ids([$new_id]); }
    $product->save();
}
WP_CLI::success('Demo photo placeholders imported. Owner images, pricing, stock, orders and accounts were preserved.');
