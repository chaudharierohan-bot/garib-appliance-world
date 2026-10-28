document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.menu-toggle');
  const menu = document.querySelector('#category-menu');
  const close = () => { if (menu) menu.hidden = true; toggle?.setAttribute('aria-expanded', 'false'); };
  toggle?.addEventListener('click', () => { const open = toggle.getAttribute('aria-expanded') !== 'true'; toggle.setAttribute('aria-expanded', String(open)); menu.hidden = !open; });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && menu && !menu.hidden) { close(); toggle?.focus(); } });
  document.addEventListener('click', event => { if (!toggle?.contains(event.target) && !menu?.contains(event.target)) close(); });
  const hero = document.querySelector('.hero');
  const slides = [...document.querySelectorAll('.hero-slide')];
  const dots = [...document.querySelectorAll('.slide-dot')];
  let current = 0;
  const show = index => {
    if (!slides.length) return;
    current = (index + slides.length) % slides.length;
    slides.forEach((slide, i) => { slide.hidden = i !== current; slide.classList.toggle('active', i === current); });
    dots.forEach((dot, i) => { dot.classList.toggle('active', i === current); dot.setAttribute('aria-pressed', String(i === current)); });
    const counter = document.querySelector('.slide-counter');
    if (counter) counter.textContent = `${String(current + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
  };
  dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));
  document.querySelector('.slide-prev')?.addEventListener('click', () => show(current - 1));
  document.querySelector('.slide-next')?.addEventListener('click', () => show(current + 1));
  hero?.addEventListener('keydown', event => {
    if (event.target.tagName !== 'BUTTON') return;
    if (event.key === 'ArrowLeft') { event.preventDefault(); show(current - 1); }
    if (event.key === 'ArrowRight') { event.preventDefault(); show(current + 1); }
  });
});
