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
            mobileMenu.classList.toggle('is-open');
        });
    }

    // Dropdown utilisateur
    var dropdownToggle = document.getElementById('dropdown-toggle');
    var dropdownMenu = document.getElementById('dropdown-menu');

    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('is-open');
        });

        document.addEventListener('click', function () {
            dropdownMenu.classList.remove('is-open');
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
