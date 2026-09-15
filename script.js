const body = document.body;
const header = document.querySelector('.site-header');
const menuButton = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
const themeButton = document.querySelector('.theme-button');

document.getElementById('year').textContent = new Date().getFullYear();

window.addEventListener('scroll', () => header.classList.toggle('scrolled', window.scrollY > 20), { passive: true });

menuButton.addEventListener('click', () => {
  const open = navLinks.classList.toggle('open');
  menuButton.setAttribute('aria-expanded', String(open));
});

document.querySelectorAll('.nav-links a').forEach(link => link.addEventListener('click', () => {
  navLinks.classList.remove('open');
  menuButton.setAttribute('aria-expanded', 'false');
}));

themeButton.addEventListener('click', () => {
  body.classList.toggle('dark');
  themeButton.textContent = body.classList.contains('dark') ? '☀' : '◐';
});

const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    entry.target.classList.add('visible');
    if (entry.target.classList.contains('stat')) animateCount(entry.target.querySelector('strong'));
    observer.unobserve(entry.target);
  });
}, { threshold: 0.14 });

document.querySelectorAll('.reveal').forEach(element => observer.observe(element));

function animateCount(element) {
  const target = Number(element.dataset.count);
  const duration = 1100;
  const start = performance.now();
  const tick = now => {
    const progress = Math.min((now - start) / duration, 1);
    element.textContent = Math.floor(target * (1 - Math.pow(1 - progress, 3)));
    if (progress < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

const sections = [...document.querySelectorAll('main section[id]')];
const mainNavLinks = [...document.querySelectorAll('.nav-links a')];
const sectionObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    mainNavLinks.forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${entry.target.id}`));
  });
}, { rootMargin: '-30% 0px -60% 0px' });
sections.forEach(section => sectionObserver.observe(section));

document.querySelectorAll('.filter').forEach(button => button.addEventListener('click', () => {
  document.querySelectorAll('.filter').forEach(item => item.classList.remove('active'));
  button.classList.add('active');
  const filter = button.dataset.filter;
  document.querySelectorAll('.project-card').forEach(card => {
    const visible = filter === 'all' || card.dataset.category === filter;
    card.classList.toggle('hidden', !visible);
    if (visible) requestAnimationFrame(() => card.classList.add('visible'));
  });
}));

document.querySelectorAll('.role-toggle').forEach(button => button.addEventListener('click', () => {
  const card = button.closest('.role-card');
  card.classList.toggle('open');
  button.firstChild.textContent = card.classList.contains('open') ? 'Hide highlights ' : 'View highlights ';
}));

document.querySelector('.shuffle-tools').addEventListener('click', () => {
  const grid = document.querySelector('.tools-grid');
  [...grid.children].sort(() => Math.random() - .5).forEach(item => grid.appendChild(item));
});

const toast = document.querySelector('.toast');
document.querySelector('.copy-email').addEventListener('click', async event => {
  try {
    await navigator.clipboard.writeText(event.currentTarget.dataset.email);
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2200);
  } catch {
    window.location.href = `mailto:${event.currentTarget.dataset.email}`;
  }
});
