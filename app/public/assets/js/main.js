document.addEventListener('DOMContentLoaded', function () {

    // Scroll reveal
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.reveal').forEach(function (el) {
        observer.observe(el);
    });

    // Menu mobile - toggle
    var toggle = document.getElementById('nav-toggle');
    var mobileMenu = document.getElementById('nav-mobile');

    if (toggle && mobileMenu) {
        toggle.addEventListener('click', function () {
            var open = mobileMenu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Dropdown utilisateur
    var dropdownToggle = document.getElementById('dropdown-toggle');
    var dropdownMenu = document.getElementById('dropdown-menu');

    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = dropdownMenu.classList.toggle('is-open');
            dropdownToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function () {
            dropdownMenu.classList.remove('is-open');
            dropdownToggle.setAttribute('aria-expanded', 'false');
        });

        // Fermeture au clavier (touche Échap)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && dropdownMenu.classList.contains('is-open')) {
                dropdownMenu.classList.remove('is-open');
                dropdownToggle.setAttribute('aria-expanded', 'false');
                dropdownToggle.focus();
            }
        });
    }

    // Formulaire de contact en AJAX (si présent sur la page)
    var contactForm = document.getElementById('contact-form');
    if (contactForm) {
        initContactForm(contactForm);
    }

});

// Envoi du formulaire de contact sans rechargement de page
function initContactForm(form) {
    var feedback = document.getElementById('contact-feedback');
    var bouton   = document.getElementById('contact-submit');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        feedback.innerHTML = '';
        bouton.classList.add('is-loading');
        bouton.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (reponse) {
            return reponse.json().then(function (data) {
                return { ok: reponse.ok, data: data };
            });
        })
        .then(function (resultat) {
            if (resultat.ok && resultat.data.success) {
                form.reset();
                afficherMessageContact('success', resultat.data.message);
            } else {
                var texte = (resultat.data.errors && resultat.data.errors.length)
                    ? resultat.data.errors.join(' ')
                    : 'Veuillez vérifier les champs du formulaire.';
                afficherMessageContact('error', texte);
            }
        })
        .catch(function () {
            afficherMessageContact('error', "L'envoi a échoué. Merci de réessayer dans un instant.");
        })
        .then(function () {
            bouton.classList.remove('is-loading');
            bouton.disabled = false;
        });
    });
}

function afficherMessageContact(type, texte) {
    var feedback = document.getElementById('contact-feedback');
    var icone = type === 'success' ? 'check_circle' : 'error';
    feedback.innerHTML =
        '<div class="alert alert--' + type + '" role="alert">' +
        '<span class="material-symbols-outlined" aria-hidden="true">' + icone + '</span> ' +
        texte + '</div>';
}

// Compteur invités (page détail menu)
function initGuestCounter(pricePerGuest, minGuests) {
    var guests = minGuests;
    var display = document.getElementById('guest-count');
    var total   = document.getElementById('total-price');

    function update() {
        if (!display || !total) return;
        display.textContent = guests;
        var price = guests * pricePerGuest;
        if (guests >= minGuests + 5) price = price * 0.9;
        total.textContent = price.toFixed(2) + ' €';
    }

    var btnPlus  = document.getElementById('btn-increment');
    var btnMoins = document.getElementById('btn-decrement');

    if (btnPlus)  btnPlus.addEventListener('click', function () { guests++; update(); });
    if (btnMoins) btnMoins.addEventListener('click', function () { if (guests > minGuests) { guests--; update(); } });

    update();
}
