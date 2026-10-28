const assert = require('node:assert/strict');
const fs = require('node:fs');
const { chromium } = require('playwright');
const base = process.env.STORE_URL || 'http://localhost:8080';
const results = [];
let currentPage;
async function check(name, fn) { try { await fn(); } catch(error) { await currentPage?.screenshot({path:'test-results/failure.png',fullPage:true}); console.error((await currentPage?.locator('body').innerText())?.slice(0,700)); throw error; } results.push(name); console.log(`PASS ${name}`); }
(async () => {
  fs.mkdirSync('test-results', {recursive:true});
  const browser = await chromium.launch({executablePath:process.env.CHROMIUM_PATH || (fs.existsSync('/usr/bin/chromium') ? '/usr/bin/chromium' : undefined),args:['--no-sandbox']});
  const errors=[];
  try {
    const context=await browser.newContext();
    const page=await context.newPage(); currentPage=page; page.on('pageerror',e=>errors.push(e.message));
    await check('desktop homepage, original assets, categories and promotions',async()=>{
      await page.setViewportSize({width:1440,height:1000});
      assert.equal((await page.goto(base)).status(),200);
      assert.match(await page.title(),/Garib Appliance World/);
      assert.equal(await page.locator('#category-menu a').count(),11);
      assert.equal(await page.locator('.category-tile').count(),6);
      assert.equal(await page.locator('li.product').count(),16);
      await page.locator('.slide-dot').nth(1).click();
      assert.equal(await page.locator('.hero-slide').nth(1).isVisible(),true);
      await page.locator('.slide-next').click();
      assert.equal(await page.locator('.hero-slide').nth(2).isVisible(),true);
      await page.locator('.slide-prev').click();
      assert.equal(await page.locator('.hero-slide').nth(1).isVisible(),true);
      await page.locator('.slide-next').focus();
      await page.keyboard.press('ArrowRight');
      assert.equal(await page.locator('.hero-slide').nth(2).isVisible(),true);
      await page.locator('.slide-dot').first().click();
      for (const id of ['featured-products','special-offers','new-arrivals','best-sellers']) {
        const section=page.locator('#'+id);
        assert.equal(await section.locator('li.product').count(),4);
        assert.equal(await section.locator('.price').count(),4);
        assert.match(await section.innerText(),/SRD/);
        assert.equal(await section.locator('li.product a.woocommerce-LoopProduct-link[href*="/product/"]').count(),4);
        assert.ok(await section.locator('a.add_to_cart_button').count());
      }
      assert.equal(await page.locator('#special-offers .price del').count(),4);
      assert.equal(await page.locator('#special-offers .price ins').count(),4);
      assert.equal(await page.locator('.hero-slide').count(),3);
      assert.equal(await page.locator('.social-links span').count(),3);
      assert.match(await page.locator('.footer-contact').innerText(),/to be confirmed/);
      assert.match(await page.locator('#featured-products li.product img').first().getAttribute('src'),/gaw-demo-/);

      await page.screenshot({path:'test-results/desktop.png',fullPage:true});
    });
    await check('mobile and tablet layout, category menu and keyboard dismissal',async()=>{
      for(const width of [320,390,768]){
        await page.setViewportSize({width,height:844});
        assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth <= innerWidth),true,`horizontal overflow at ${width}px`);
        await page.locator('.menu-toggle').click();
        assert.equal(await page.locator('#category-menu').isVisible(),true);
        await page.keyboard.press('Escape');
        assert.equal(await page.locator('#category-menu').isVisible(),false);
        await page.screenshot({path:`test-results/layout-${width}.png`,fullPage:true});
      }
      await page.setViewportSize({width:1440,height:1000});
    });
    await check('all category pages and product search',async()=>{
      const urls=await page.locator('#category-menu a').evaluateAll(es=>es.map(e=>e.href));
      for(const url of urls){const response=await page.goto(url);assert.equal(response.status(),200,url);assert.ok(await page.locator('li.product').count(),url);}
      await page.locator('#product-search').fill('Frostline');
      await Promise.all([page.waitForURL(/post_type=product/),page.locator('.product-search button').click()]);
      assert.equal(await page.locator('li.product').count(),2);
    });
    await check('catalogue AJAX add-to-cart refreshes bag count',async()=>{
      await page.goto(base+'/shop/');
      await page.locator('a.ajax_add_to_cart').first().click();
      await page.waitForFunction(()=>document.querySelector('.cart-count')?.textContent.trim()==='1');
      await page.goto(base+'/cart/');
      await page.locator('.remove').click();
      await page.waitForSelector('.cart-empty');
    });
    let productURL;
    await check('product description, specifications, quantity and add to cart',async()=>{
      await page.goto(base+'/shop/');
      productURL=await page.locator('li.product a.woocommerce-LoopProduct-link').first().getAttribute('href');
      await page.goto(productURL);
      assert.ok(await page.locator('.woocommerce-product-gallery img').count());
      await page.locator('#tab-title-additional_information a').click();
      assert.match(await page.locator('#tab-additional_information').innerText(),/Capacity/);
      await page.locator('input.qty').fill('2');
      await page.locator('button.single_add_to_cart_button').click();
      await page.goto(base+'/cart/');
      assert.equal(await page.locator('.cart_item input.qty').inputValue(),'2');
      assert.match(await page.locator('.order-total').innerText(),/SRD/);
    });
    await check('cart quantity update and removal',async()=>{
      await page.locator('.cart_item input.qty').fill('3');
      await page.locator('[name="update_cart"]').click();
      await page.waitForFunction(()=>document.querySelector('.cart_item input.qty')?.value==='3'&&!document.querySelector('.blockUI'));
      await page.reload();
      assert.equal(await page.locator('.cart_item input.qty').inputValue(),'3');
      await page.locator('.remove').click();
      await page.waitForSelector('.cart-empty');
      assert.match(await page.locator('.cart-empty').innerText(),/empty/i);
    });
    for(const method of ['cod','bacs']) await check(`checkout order submission using ${method}`,async()=>{
      await page.goto(productURL);
      await page.locator('button.single_add_to_cart_button').click();
      await page.goto(base+'/checkout/');
      await page.locator('#billing_first_name').fill('Demo');
      await page.locator('#billing_last_name').fill('Customer');
      await page.locator('#billing_address_1').fill('123 Demonstration Street');
      await page.locator('#billing_city').fill('Paramaribo');
      await page.locator('#billing_state').fill('Paramaribo');
      await page.locator('#billing_phone').fill('5977000000');
      await page.locator('#billing_email').fill(`demo-${method}-${Date.now()}@example.test`);
      await page.locator('#payment_method_'+method).check();
      await page.waitForTimeout(700);
      await page.locator('#place_order').click();
      await page.waitForURL(/order-received/, {timeout:30000});
      assert.match(await page.locator('main').innerText(),/Thank you/);
      assert.match(await page.locator('main').innerText(),/SRD/);
      assert.match(await page.locator('main').innerText(),method==='cod'?/Cash on delivery/:/Direct bank transfer/);
    });
    await check('account login, registration and password-reset forms',async()=>{
      await page.goto(base+'/my-account/');
      assert.equal(await page.locator('form.login').count(),1);
      assert.equal(await page.locator('form.register').count(),1);
      await page.locator('.lost_password a').click();
      assert.equal(await page.locator('input#user_login').count(),1);
      // Email transport is deliberately not configured; do not submit a reset email.
    });
    await check('customer registration, logout, login and account editing',async()=>{
      const email=`demo-account-${Date.now()}@example.test`;
      const password='LocalOnly!'+require('node:crypto').randomBytes(20).toString('hex');
      await page.goto(base+'/my-account/');
      await page.locator('#reg_email').fill(email);
      await page.locator('#reg_password').fill(password);
      await page.locator('#reg_password').press('Tab');
      await page.waitForSelector('.woocommerce-password-strength.strong');
      await page.locator('[name="register"]').click();
      await page.waitForSelector('.woocommerce-MyAccount-navigation');
      await page.waitForLoadState('networkidle');
      await page.locator('.woocommerce-MyAccount-navigation-link--edit-account a').click();
      await page.waitForLoadState('networkidle');
      await page.locator('#account_first_name').fill('Demo');
      await page.locator('#account_last_name').fill('Customer');
      await page.locator('#account_display_name').fill('Demo Customer');
      await Promise.all([page.waitForNavigation(), page.locator('[name="save_account_details"]').click()]);
      assert.match(await page.locator('.woocommerce-message').innerText(),/changed successfully/i);
      await page.reload();
      assert.equal(await page.locator('#account_first_name').inputValue(),'Demo');
      assert.equal(await page.locator('#account_last_name').inputValue(),'Customer');
      await page.locator('.woocommerce-MyAccount-navigation-link--customer-logout a').click();
      await page.waitForSelector('form.login');
      await page.locator('#username').fill(email);
      await page.locator('#password').fill(password);
      await Promise.all([page.waitForNavigation(), page.locator('[name="login"]').click()]);
      await page.waitForSelector('.woocommerce-MyAccount-navigation');
      await page.waitForLoadState('networkidle');
      await page.locator('.woocommerce-MyAccount-navigation-link--orders a').click();
      assert.match(await page.locator('main').innerText(),/No order has been made yet/i);
      await page.locator('.woocommerce-MyAccount-navigation-link--customer-logout a').click();
    });
    await check('newsletter consent request and validation',async()=>{
      await page.goto(base);
      const rejected=await page.request.post(base+'/wp-admin/admin-post.php',{form:{action:'gaw_newsletter',email:'invalid',gaw_nonce:'invalid'}});
      assert.equal(rejected.status(),403);
      await page.locator('#newsletter-email').fill(`demo-${Date.now()}@example.test`);
      await page.locator('[name="consent"]').check();
      await page.locator('.newsletter-form button').click();
      await page.waitForURL(/newsletter=received/);
      assert.match(await page.locator('#newsletter [role="status"]').innerText(),/request has been saved/);
    });
    assert.deepEqual(errors,[],'browser JavaScript errors');
    console.log(`${results.length} checks passed. Two demonstration orders created.`);
  } finally { await browser.close(); }
})().catch(error=>{ console.error(error); process.exitCode=1; });
