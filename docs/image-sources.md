# Demo imagery

The retail refresh uses **AI-generated, photorealistic temporary placeholders**, not actual manufacturer or inventory photographs. They were generated specifically for this project on 28 October 2026. No external product photographs, Lucky Store assets, brand logos or copyrighted retailer graphics were copied. These files are not a replacement for the business's official logo.

The files `theme/garib-appliance-world/assets/images/demo-{fridge,washer,ac,tv,kitchen,audio,phone,home,hero}.png` depict fictional, unbranded items. Product images use a single item on a white studio background; the hero depicts a fictional modern kitchen. The homepage disclosure, media attachment descriptions/alt text and `(Demo)` product names identify their demonstration status. They should not be represented as photographs of products actually held in stock.

External stock-photo hosting and the requested reference site were denied by the environment's network policy. The generated placeholders provide a realistic temporary presentation while the owner sources and licenses genuine inventory photography. Replace them with owned or properly licensed product photographs before launch, retaining source and license records.

`scripts/refresh-photos.php` imports the original PNGs into the WordPress media library and lets WordPress produce responsive display sizes (WebP where supported). Original source files remain unchanged. The importer is idempotent, targets only `DEMO-*` products and preserves owner-supplied images, prices, stock, orders and accounts. Category/promotion photographs use media attachment IDs stored in `gaw_demo_photo_*` options. Hero artwork can still be overridden through the existing WordPress Customizer control.
