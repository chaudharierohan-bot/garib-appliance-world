document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.menu-toggle');
  const menu = document.querySelector('#category-menu');
  const close = () => { if(menu) menu.hidden = true; toggle?.setAttribute('aria-expanded', 'false'); };
  toggle?.addEventListener('click', () => { const open = toggle.getAttribute('aria-expanded') !== 'true'; toggle.setAttribute('aria-expanded', String(open)); menu.hidden = !open; });
  document.addEventListener('keydown', e => { if(e.key === 'Escape') { close(); toggle?.focus(); } });
  document.addEventListener('click', e => { if(!toggle?.contains(e.target) && !menu?.contains(e.target)) close(); });
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.slide-dot');
  dots.forEach((dot, i) => dot.addEventListener('click', () => { slides.forEach((s,j) => { s.hidden = i !== j; s.classList.toggle('active',i===j); }); dots.forEach((d,j) => { d.classList.toggle('active', i===j); d.setAttribute('aria-pressed', String(i===j)); }); }));
});
