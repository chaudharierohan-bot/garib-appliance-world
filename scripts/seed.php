<?php
/** Explicit, idempotent demo content import. Does not alter existing demo products. */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (!class_exists('WooCommerce')) { WP_CLI::error('WooCommerce must be active.'); }
$categories=gaw_categories();
foreach($categories as $slug=>$name) { if(!term_exists($slug,'product_cat'))wp_insert_term($name,'product_cat',['slug'=>$slug]); }
$items=[
['DEMO-RF01','Frostline 320L Double Door Refrigerator','refrigerators-freezers','fridge',18500,15995,'320 litres','Energy-conscious compressor; adjustable shelves; separate freezer'],
['DEMO-WM01','PureWash 8kg Front Load Washing Machine','washing-machines','washer',14900,12995,'8 kg','15 wash programmes; 1200 rpm spin; delay start'],
['DEMO-AC01','Breeze Inverter Air Conditioner 12,000 BTU','air-conditioners','ac',13500,11995,'12,000 BTU','Inverter compressor; washable filter; sleep mode'],
['DEMO-TV01','Vista 55-inch 4K Smart Television','televisions','tv',17500,14995,'55 inches','3840 × 2160 resolution; 3 HDMI ports; smart apps'],
['DEMO-KT01','Everyday Pro 1.5L Countertop Blender','kitchen-appliances','kitchen',1950,1595,'1.5 litres','600 W motor; 3 speeds; pulse mode'],
['DEMO-SM01','Morning Essentials Multi-Purpose Blender','small-household-appliances','kitchen',1450,0,'1.2 litres','500 W motor; easy-clean jar; non-slip base'],
['DEMO-EL01','Studio Wireless Home Speaker','electronics','audio',2495,0,'30 W','Bluetooth connection; rechargeable battery; line input'],
['DEMO-PH01','Connect 128GB Everyday Smartphone','smartphones-tablets','phone',7950,6995,'128 GB','6.5-inch display; dual camera; USB-C charging'],
['DEMO-AU01','Soundroom Tower Speaker','audio-entertainment','audio',3995,3495,'60 W','Rich stereo sound; Bluetooth connection; compact tower'],
['DEMO-HM01','Haven Two-Seater Accent Sofa','home-living','home',9950,0,'Two seats','Woven upholstery; solid wood legs; sage finish'],
['DEMO-RF02','Frostline Compact 120L Refrigerator','refrigerators-freezers','fridge',7950,0,'120 litres','Compact footprint; adjustable thermostat; reversible door'],
['DEMO-WM02','PureWash Family 10kg Washing Machine','washing-machines','washer',21950,0,'10 kg','Family capacity; quick wash; child lock'],
];
foreach($items as $i=>$row){[$sku,$name,$cat,$image,$regular,$sale,$capacity,$features]=$row;
 if(wc_get_product_id_by_sku($sku))continue;
 $p=new WC_Product_Simple();$p->set_name($name.' (Demo)');$p->set_sku($sku);$p->set_status('publish');$p->set_catalog_visibility('visible');$p->set_regular_price((string)$regular);if($sale)$p->set_sale_price((string)$sale);
 $p->set_short_description('Demonstration product — sample specifications and pricing. Replace with verified inventory before launch.');
 $p->set_description('<h2>Made for your everyday</h2><p>'.esc_html($features).'. A practical addition to a comfortable home.</p><p><strong>DEMONSTRATION ONLY:</strong> This is fictional inventory with illustrative prices, features and original placeholder artwork. It is not an offer for sale.</p>');
 $ids=[];foreach(array_filter([$cat,$sale?'special-offers':null]) as $slug){$t=get_term_by('slug',$slug,'product_cat');if($t)$ids[]=$t->term_id;}$p->set_category_ids($ids);
 $p->set_manage_stock(true);$p->set_stock_quantity($i===10?0:12);$p->set_stock_status($i===10?'outofstock':'instock');$p->set_featured($i<4);
 $attrs=[];foreach(['Capacity'=>$capacity,'Features'=>$features,'Content type'=>'Fictional demonstration product'] as $label=>$value){$a=new WC_Product_Attribute();$a->set_name($label);$a->set_options([$value]);$a->set_visible(true);$attrs[]=$a;}$p->set_attributes($attrs);
 $id=$p->save();
 // Draw original raster product illustrations with GD; SVG uploads stay disabled.
 $upload=wp_upload_dir();$file=$upload['path'].'/'.strtolower($sku).'.png';
 $canvas=imagecreatetruecolor(600,600);$bg=imagecolorallocate($canvas,247,248,245);imagefill($canvas,0,0,$bg);$body=imagecolorallocate($canvas,197,213,203);$dark=imagecolorallocate($canvas,32,65,60);$light=imagecolorallocate($canvas,229,236,230);
 if($image==='fridge'){imagefilledrectangle($canvas,190,65,410,520,$body);imagefilledrectangle($canvas,195,70,400,225,$light);imagefilledrectangle($canvas,195,232,400,510,$light);imagefilledrectangle($canvas,212,125,221,192,$dark);imagefilledrectangle($canvas,212,265,221,365,$dark);}
 elseif($image==='washer'){imagefilledrectangle($canvas,115,160,485,515,$body);imagefilledrectangle($canvas,120,165,480,230,$light);imagefilledellipse($canvas,300,365,245,245,$dark);imagefilledellipse($canvas,300,365,192,192,imagecolorallocate($canvas,111,149,146));imagefilledellipse($canvas,430,198,24,24,$dark);}
 elseif($image==='ac'){imagefilledrectangle($canvas,60,220,540,375,$light);for($y=325;$y<360;$y+=12)imagefilledrectangle($canvas,80,$y,520,$y+4,$dark);}
 elseif($image==='tv'){imagefilledrectangle($canvas,45,140,555,440,$dark);imagefilledrectangle($canvas,57,152,543,428,imagecolorallocate($canvas,105,145,132));imagefilledpolygon($canvas,[57,380,210,220,350,350,450,240,543,370,543,428,57,428],imagecolorallocate($canvas,167,196,163));imagefilledrectangle($canvas,280,440,320,480,$dark);imagefilledrectangle($canvas,225,480,375,490,$dark);}
 elseif($image==='phone'){imagefilledrectangle($canvas,190,65,410,535,$dark);imagefilledrectangle($canvas,203,80,397,520,$body);imagefilledrectangle($canvas,260,80,340,93,$dark);}
 elseif($image==='audio'){imagefilledrectangle($canvas,170,70,430,530,$dark);imagefilledellipse($canvas,300,200,145,145,$body);imagefilledellipse($canvas,300,390,200,200,$body);imagefilledellipse($canvas,300,390,120,120,$dark);}
 elseif($image==='home'){imagefilledrectangle($canvas,100,230,500,440,$body);imagefilledrectangle($canvas,65,315,125,470,$dark);imagefilledrectangle($canvas,475,315,535,470,$dark);imagefilledrectangle($canvas,125,380,475,390,$dark);}
 else{imagefilledpolygon($canvas,[195,80,405,80,370,330,230,330],$body);imagefilledrectangle($canvas,235,330,365,370,$dark);imagefilledpolygon($canvas,[230,370,370,370,410,510,190,510],$body);imagefilledellipse($canvas,300,440,55,55,$dark);}
 imagepng($canvas,$file);imagedestroy($canvas);
 $attachment=wp_insert_attachment(['post_mime_type'=>'image/png','post_title'=>$name.' — original demonstration illustration','post_status'=>'inherit'],$file,$id);require_once ABSPATH.'wp-admin/includes/image.php';wp_update_attachment_metadata($attachment,wp_generate_attachment_metadata($attachment,$file));update_post_meta($attachment,'_wp_attachment_image_alt','Original illustration of '.$name);$p->set_image_id($attachment);$p->set_gallery_image_ids([$attachment]);$p->save();
}
WP_CLI::success('Demo catalogue imported. Re-running skips existing SKUs.');
