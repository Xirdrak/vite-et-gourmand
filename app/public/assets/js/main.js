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

        // Fermeture au clavier (touche Echap)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && dropdownMenu.classList.contains('is-open')) {
                dropdownMenu.classList.remove('is-open');
                dropdownToggle.setAttribute('aria-expanded', 'false');
                dropdownToggle.focus();
            }
        });
    }

});

// Compteur invites (page detail menu)
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
