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

    // Contrôle du mot de passe (page d'inscription)
    var formInscription = document.getElementById('form-inscription');
    if (formInscription) {
        initVerificationMotDePasse(formInscription);
    }

});

// Affiche l'erreur sous le champ, au même endroit que les erreurs renvoyées par Symfony
function afficherErreurChamp(champ, message) {
    effacerErreurChamp(champ);

    const liste = document.createElement('ul');
    liste.className = 'form-error';
    liste.id = `${champ.id}-erreur-js`;

    const ligne = document.createElement('li');
    ligne.textContent = message;
    liste.append(ligne);

    champ.after(liste);
    // aria-describedby fait lire le message d'erreur par le lecteur d'écran avec le champ
    champ.setAttribute('aria-invalid', 'true');
    champ.setAttribute('aria-describedby', liste.id);
}

function effacerErreurChamp(champ) {
    document.getElementById(`${champ.id}-erreur-js`)?.remove();
    champ.removeAttribute('aria-invalid');
    champ.removeAttribute('aria-describedby');
}

const EMAIL_VALIDE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Mêmes règles que ContactType : le navigateur prévient tout de suite, le serveur revérifie toujours
const messageErreurContact = (nom, valeur) => {
    if (nom === 'email') {
        if (valeur === '') return 'Veuillez indiquer votre adresse e-mail.';
        if (valeur.length > 180 || !EMAIL_VALIDE.test(valeur)) return 'Adresse e-mail invalide.';
    }
    if (nom === 'sujet') {
        if (valeur === '') return 'Veuillez indiquer un sujet.';
        if (valeur.length > 150) return 'Le sujet ne peut pas dépasser 150 caractères.';
    }
    if (nom === 'message') {
        if (valeur === '') return 'Veuillez écrire votre message.';
        if (valeur.length < 10) return 'Le message doit contenir au moins 10 caractères.';
        if (valeur.length > 2000) return 'Le message ne peut pas dépasser 2000 caractères.';
    }
    return '';
};

// Envoi du formulaire de contact sans rechargement de page
function initContactForm(form) {
    const bouton = document.getElementById('contact-submit');
    const champs = ['email', 'sujet', 'message'].map((nom) => ({
        nom,
        element: form.querySelector(`[name$="[${nom}]"]`),
    }));

    // Vérifie tous les champs et renvoie true si le formulaire peut partir
    const formulaireValide = () => {
        let valide = true;
        champs.forEach(({ nom, element }) => {
            const erreur = messageErreurContact(nom, element.value.trim());
            if (erreur) {
                afficherErreurChamp(element, erreur);
                valide = false;
            } else {
                effacerErreurChamp(element);
            }
        });
        return valide;
    };

    // L'erreur disparaît dès que le champ redevient correct
    champs.forEach(({ nom, element }) => {
        element.addEventListener('input', () => {
            if (element.hasAttribute('aria-invalid') && !messageErreurContact(nom, element.value.trim())) {
                effacerErreurChamp(element);
            }
        });
    });

    // async/await remplace la chaîne de .then() : le code se lit de haut en bas
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        document.getElementById('contact-feedback').replaceChildren();

        if (!formulaireValide()) {
            form.querySelector('[aria-invalid="true"]').focus();
            return;
        }

        bouton.classList.add('is-loading');
        bouton.disabled = true;

        try {
            const reponse = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await reponse.json();

            if (reponse.ok && data.success) {
                form.reset();
                afficherMessageContact('success', data.message);
            } else {
                const texte = data.errors?.length
                    ? data.errors.join(' ')
                    : 'Veuillez vérifier les champs du formulaire.';
                afficherMessageContact('error', texte);
            }
        } catch {
            afficherMessageContact('error', "L'envoi a échoué. Merci de réessayer dans un instant.");
        } finally {
            bouton.classList.remove('is-loading');
            bouton.disabled = false;
        }
    });
}

// Le message est construit avec textContent : il est affiché comme du texte, jamais interprété comme du HTML
function afficherMessageContact(type, texte) {
    const alerte = document.createElement('div');
    alerte.className = `alert alert--${type}`;
    alerte.setAttribute('role', 'alert');

    const icone = document.createElement('span');
    icone.className = 'material-symbols-outlined';
    icone.setAttribute('aria-hidden', 'true');
    icone.textContent = type === 'success' ? 'check_circle' : 'error';

    alerte.append(icone, ` ${texte}`);
    document.getElementById('contact-feedback').replaceChildren(alerte);
}

// Mêmes règles que RegistrationFormType
const REGLES_MOT_DE_PASSE = [
    { respectee: (mdp) => mdp.length >= 10, manque: '10 caractères minimum' },
    { respectee: (mdp) => /[A-Z]/.test(mdp), manque: 'une majuscule' },
    { respectee: (mdp) => /[a-z]/.test(mdp), manque: 'une minuscule' },
    { respectee: (mdp) => /[0-9]/.test(mdp), manque: 'un chiffre' },
    { respectee: (mdp) => /[^a-zA-Z0-9]/.test(mdp), manque: 'un caractère spécial' },
];

// Contrôle du mot de passe à l'inscription, avant l'envoi du formulaire
function initVerificationMotDePasse(form) {
    const motDePasse = form.querySelector('[name$="[plainPassword][first]"]');
    const confirmation = form.querySelector('[name$="[plainPassword][second]"]');
    if (!motDePasse || !confirmation) return;

    const verifier = () => {
        const manques = REGLES_MOT_DE_PASSE
            .filter((regle) => !regle.respectee(motDePasse.value))
            .map((regle) => regle.manque);

        if (manques.length > 0) {
            afficherErreurChamp(motDePasse, `Il manque : ${manques.join(', ')}.`);
        } else {
            effacerErreurChamp(motDePasse);
        }

        const identiques = confirmation.value === motDePasse.value;
        if (identiques) {
            effacerErreurChamp(confirmation);
        } else {
            afficherErreurChamp(confirmation, 'Les mots de passe ne correspondent pas.');
        }

        return manques.length === 0 && identiques;
    };

    form.addEventListener('submit', (e) => {
        if (!verifier()) {
            e.preventDefault();
            form.querySelector('[aria-invalid="true"]').focus();
        }
    });
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
