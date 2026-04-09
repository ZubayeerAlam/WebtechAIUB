const darkBtn = document.getElementById('darkMode-icon');
const body = document.body;
const menuIcon = document.getElementById('menuIcon');
const navbar = document.getElementById('navbar');
const progressBar = document.getElementById('progressBar');
const backTop = document.getElementById('backTop');

// ── Dark Mode ──────────────────────────────────────────────
const saved = localStorage.getItem('theme');
if (saved === 'dark') {
  body.classList.add('dark');
  darkBtn.classList.replace('bx-moon', 'bx-sun');
}
darkBtn.addEventListener('click', () => {
  body.classList.toggle('dark');
  const isDark = body.classList.contains('dark');
  darkBtn.classList.toggle('bx-sun', isDark);
  darkBtn.classList.toggle('bx-moon', !isDark);
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
});

// ── Mobile Menu ────────────────────────────────────────────
menuIcon.addEventListener('click', () => {
  navbar.classList.toggle('open');
  menuIcon.classList.toggle('bx-menu');
  menuIcon.classList.toggle('bx-x');
});
navbar.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  navbar.classList.remove('open');
  menuIcon.classList.add('bx-menu');
  menuIcon.classList.remove('bx-x');
}));

// ── Scroll: Progress Bar + Back-to-Top + Active Nav ────────
window.addEventListener('scroll', () => {
  const pct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
  progressBar.style.width = pct + '%';
  backTop.classList.toggle('show', window.scrollY > 400);

  // Active nav link
  const sections = document.querySelectorAll('section');
  sections.forEach(sec => {
    const top = sec.offsetTop - 120;
    const bot = top + sec.offsetHeight;
    const id  = sec.getAttribute('id');
    const link = document.querySelector('.navbar a[href="#' + id + '"]');
    if (link) {
      link.classList.toggle('active', window.scrollY >= top && window.scrollY < bot);
    }
  });
});

backTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ── Reveal on Scroll ───────────────────────────────────────
const reveals = document.querySelectorAll('.reveal');
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.15 });
reveals.forEach(el => revealObserver.observe(el));

// ── Skill Bars ─────────────────────────────────────────────
const skillObserver = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.querySelectorAll('.skill-fill').forEach(bar => {
        bar.style.width = bar.dataset.width + '%';
      });
    }
  });
}, { threshold: 0.3 });
document.querySelectorAll('.skill-card').forEach(c => skillObserver.observe(c));

// ── Counters ───────────────────────────────────────────────
const counterObserver = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const el     = e.target;
      const target = +el.dataset.target;
      let count    = 0;
      const step   = Math.ceil(target / 60);
      const timer  = setInterval(() => {
        count += step;
        if (count >= target) { count = target; clearInterval(timer); }
        el.textContent = count + '+';
      }, 25);
      counterObserver.unobserve(el);
    }
  });
}, { threshold: 0.5 });
document.querySelectorAll('.counter').forEach(c => counterObserver.observe(c));

// ── Typewriter ─────────────────────────────────────────────
const words = ['Web Developer', 'UI/UX Designer', 'Photographer', 'Videographer', 'Freelancer'];
let wIdx = 0, cIdx = 0, deleting = false;

function type() {
  const word = words[wIdx];
  document.getElementById('typeText').textContent = deleting
    ? word.substring(0, cIdx--)
    : word.substring(0, cIdx++);

  if (!deleting && cIdx > word.length) {
    setTimeout(() => { deleting = true; }, 1600);
    setTimeout(type, 120);
    return;
  }
  if (deleting && cIdx < 0) {
    deleting = false;
    wIdx = (wIdx + 1) % words.length;
  }
  setTimeout(type, deleting ? 60 : 110);
}
type();

// ── Portfolio Filter ───────────────────────────────────────
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('.portfolio-card').forEach(card => {
      const match = filter === 'all' || card.dataset.cat === filter;
      card.style.display = match ? '' : 'none';
    });
  });
});

// ── Contact Form ───────────────────────────────────────────
function submitForm() {
  const fields = ['fname', 'lname', 'email', 'subject', 'message'];
  const empty  = fields.some(id => !document.getElementById(id).value.trim());
  if (empty) { alert('Please fill in all fields.'); return; }

  const btn = document.querySelector('.contact-form .btn');
  btn.innerHTML  = '<i class="bx bx-loader-alt bx-spin"></i> Sending...';
  btn.disabled   = true;

  setTimeout(() => {
    btn.style.display = 'none';
    document.getElementById('formSuccess').style.display = 'block';
    fields.forEach(id => (document.getElementById(id).value = ''));
  }, 1500);
}

// Make submitForm globally accessible (called via onclick in HTML)
window.submitForm = submitForm;
