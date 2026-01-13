// script.js — hero slideshow, mobile nav, reveal on scroll, basic validation
document.addEventListener('DOMContentLoaded', () => {
  // mobile nav toggle
  const ham = document.querySelector('.hamburger');
  const nav = document.querySelector('.nav');
  if (ham) ham.addEventListener('click', () => {
    nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    nav.style.flexDirection = 'column';
    nav.style.position = 'absolute';
    nav.style.top = '64px';
    nav.style.right = '20px';
    nav.style.padding = '12px';
    nav.style.borderRadius = '12px';
    nav.style.background = 'rgba(0,0,0,0.45)';
  });

  // reveal on scroll
  const reveals = document.querySelectorAll('.reveal');
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
  }, { threshold: 0.12 });
  reveals.forEach(r => obs.observe(r));

  // hero slideshow (uses data-images attr if present, otherwise remote Unsplash queries)
  const heroPanel = document.querySelector('.hero-panel');
  if (heroPanel) {
    // image sources: prefers local assets if present else random Unsplash queries
    const local = [
      'assets/hero-1.jpg',
      'assets/hero-2.jpg',
      'assets/hero-3.jpg',
      'assets/hero-4.jpg'
    ];
    // detect if first local image exists by attempting to fetch HEAD (graceful fallback)
    const useLocal = (url) => fetch(url, {method:'HEAD'}).then(r=>r.ok).catch(()=>false);
    (async () => {
      let imgs = [];
      const ok = await useLocal(local[0]);
      if (ok) imgs = local;
      else imgs = [
        'https://source.unsplash.com/1600x900/?college,neon,concert',
        'https://source.unsplash.com/1600x900/?students,party,neon',
        'https://source.unsplash.com/1600x900/?hackathon,students,tech',
        'https://source.unsplash.com/1600x900/?stage,lights,neon'
      ];
      // create bg layers
      imgs.forEach((src,i) => {
        const div = document.createElement('div');
        div.className = 'hero-bg';
        div.style.opacity = i===0 ? '1' : '0';
        div.style.backgroundImage = `url('${src}')`;
        heroPanel.appendChild(div);
      });
      // cycle
      const layers = heroPanel.querySelectorAll('.hero-bg');
      let idx = 0;
      setInterval(()=> {
        layers.forEach((l,i)=> l.style.opacity = (i === idx ? '1' : '0'));
        idx = (idx + 1) % layers.length;
      }, 5000);
    })();
  }

  // contact form validation
  const contact = document.querySelector('#contact-form');
  if(contact){
    contact.addEventListener('submit', (e) => {
      const name = contact.querySelector('[name=name]').value.trim();
      const email = contact.querySelector('[name=email]').value.trim();
      const message = contact.querySelector('[name=message]').value.trim();
      if (!name || !email || !message) {
        e.preventDefault();
        showToast('Please fill all fields');
        return;
      }
      if(!/^\S+@\S+\.\S+$/.test(email)){ e.preventDefault(); showToast('Enter valid email'); return; }
      showToast('Sending message...');
    });
  }

  // simple toast
  function showToast(text, t=2000){
    const el = document.createElement('div');
    el.className='toast';
    el.style.position='fixed'; el.style.right='20px'; el.style.bottom='20px';
    el.style.background='rgba(0,0,0,0.7)'; el.style.color='#fff'; el.style.padding='10px 14px';
    el.style.borderRadius='10px'; el.style.zIndex=9999; el.textContent=text;
    document.body.appendChild(el);
    setTimeout(()=> el.style.opacity=1,20);
    setTimeout(()=> el.remove(), t);
  }
});
