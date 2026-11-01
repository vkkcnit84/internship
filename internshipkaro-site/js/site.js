(function () {
    var header = document.querySelector('.site-header');
    var toggle = document.querySelector('.nav-toggle');
    var links = document.getElementById('nav-links');

    toggle.addEventListener('click', function () {
        var open = links.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open);
    });
    links.addEventListener('click', function (e) {
        if (e.target.tagName === 'A') { links.classList.remove('open'); toggle.setAttribute('aria-expanded', false); }
    });
    window.addEventListener('scroll', function () {
        header.classList.toggle('scrolled', window.scrollY > 20);
    });

    var chips = document.querySelectorAll('.chip');
    var cards = document.querySelectorAll('.card');
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var f = chip.getAttribute('data-filter');
            cards.forEach(function (card) {
                card.style.display = (f === 'all' || card.getAttribute('data-cat') === f) ? '' : 'none';
            });
        });
    });

    var form = document.getElementById('contact-form');
    var note = document.getElementById('form-note');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        note.textContent = 'Sending...';
        note.className = 'form-note';
        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                note.textContent = d.message;
                note.className = 'form-note ' + (d.ok ? 'ok' : 'err');
                if (d.ok) { form.reset(); }
            })
            .catch(function () {
                note.textContent = 'Could not send. Please call or WhatsApp us.';
                note.className = 'form-note err';
            });
    });
})();
