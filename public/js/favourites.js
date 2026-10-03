/* ------------------------------------------------------------------
   FrexCore: favourite menu items.

   Puts a pinned section at the top of the sidebar holding the pages a
   person actually uses, in the manner of ServiceNow. The product ships
   seventy nine menu destinations across six collapsed menus, and most
   people use five of them every day.

   Scope note: this stores favourites in the browser, per user, per
   device. That is deliberate for a first version. Storing them server
   side would mean a database table, which would mean install and
   uninstall migrations, which would break the property that makes this
   plugin safe to disable during an incident: nothing of it is left
   behind. If favourites need to follow a person between devices, that
   is a considered change with its own migration, not a default.

   Everything is wrapped. A failure here must cost the favourites bar
   and nothing else, because this runs on every page of the product.
   ------------------------------------------------------------------ */
(function () {
    'use strict';

    var PREFIX = 'frexcore.favourites.';
    var MAX = 12;

    function sidebar() {
        return document.querySelector('aside.navbar.navbar-vertical, .navbar-vertical.sidebar');
    }

    /* Key on the signed-in user, so two people sharing a machine do not
       inherit each other's shortcuts. */
    function storeKey() {
        var who = document.querySelector('.user-menu-dropdown-toggle div > div');
        return PREFIX + ((who && who.textContent.trim()) || 'default');
    }

    function read() {
        try {
            var raw = window.localStorage.getItem(storeKey());
            var list = raw ? JSON.parse(raw) : [];
            return Array.isArray(list) ? list : [];
        } catch (e) {
            // Private browsing, blocked storage, or corrupt content. The
            // menu still has to work.
            return [];
        }
    }

    function write(list) {
        try {
            window.localStorage.setItem(storeKey(), JSON.stringify(list.slice(0, MAX)));
        } catch (e) {
            /* Not being able to remember is survivable. */
        }
    }

    function has(list, href) {
        return list.some(function (f) { return f.href === href; });
    }

    /* Menu destinations, excluding the user menu, anchors that go nowhere
       and links off to other sites. */
    function destinations() {
        var side = sidebar();
        if (!side) { return []; }
        return Array.prototype.slice
            .call(side.querySelectorAll('li.nav-item.dropdown .dropdown-menu a.dropdown-item[href]'))
            .filter(function (a) {
                var href = a.getAttribute('href');
                return href && href !== '#' && href.charAt(0) === '/';
            });
    }

    function labelFor(a) {
        var own = a.cloneNode(true);
        Array.prototype.forEach.call(own.querySelectorAll('.fx-star'), function (n) { n.remove(); });
        return own.textContent.replace(/\s+/g, ' ').trim();
    }

    /* The menu this destination sits under, so "Tickets" and "Computers"
       are still distinguishable once they are out of their menus. */
    function sectionFor(a) {
        var item = a.closest('li.nav-item');
        var label = item && item.querySelector('.menu-label');
        return label ? label.textContent.trim() : '';
    }

    function iconFor(a) {
        var i = a.querySelector('i.ti');
        return i ? i.className : 'ti ti-point';
    }

    function renderBar() {
        var side = sidebar();
        if (!side) { return; }

        var anchorItem = side.querySelector('li.nav-item');
        var list = anchorItem && anchorItem.parentElement;
        if (!list) { return; }

        var existing = list.querySelector('.fx-favourites');
        if (existing) { existing.remove(); }

        var favs = read();
        if (!favs.length) { return; }

        var li = document.createElement('li');
        li.className = 'nav-item fx-favourites';

        var head = document.createElement('div');
        head.className = 'fx-favourites__head';
        head.textContent = 'Favourites';
        li.appendChild(head);

        favs.forEach(function (f) {
            var a = document.createElement('a');
            a.className = 'nav-link fx-favourites__link';
            a.href = f.href;
            a.title = f.section ? f.section + ' / ' + f.label : f.label;

            var icon = document.createElement('i');
            icon.className = f.icon || 'ti ti-point';
            a.appendChild(icon);

            var span = document.createElement('span');
            span.className = 'menu-label';
            span.textContent = f.label;
            a.appendChild(span);

            var drop = document.createElement('button');
            drop.type = 'button';
            drop.className = 'fx-star fx-star--on';
            drop.setAttribute('aria-label', 'Remove ' + f.label + ' from favourites');
            drop.innerHTML = '&#9733;';
            drop.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                write(read().filter(function (x) { return x.href !== f.href; }));
                renderBar();
                paintStars();
            });
            a.appendChild(drop);

            li.appendChild(a);
        });

        list.insertBefore(li, list.firstChild);
    }

    function paintStars() {
        var favs = read();
        destinations().forEach(function (a) {
            var star = a.querySelector('.fx-star');
            if (!star) { return; }
            var on = has(favs, a.getAttribute('href'));
            star.className = 'fx-star' + (on ? ' fx-star--on' : '');
            star.innerHTML = on ? '&#9733;' : '&#9734;';
            star.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    function attachStars() {
        destinations().forEach(function (a) {
            if (a.querySelector('.fx-star')) { return; }

            var star = document.createElement('button');
            star.type = 'button';
            star.className = 'fx-star';
            star.innerHTML = '&#9734;';
            star.setAttribute('aria-label', 'Add ' + labelFor(a) + ' to favourites');

            star.addEventListener('click', function (ev) {
                // Without this the click follows the link and the page
                // navigates away before anything is saved.
                ev.preventDefault();
                ev.stopPropagation();

                var href = a.getAttribute('href');
                var favs = read();

                if (has(favs, href)) {
                    favs = favs.filter(function (x) { return x.href !== href; });
                } else {
                    if (favs.length >= MAX) {
                        // A favourites list that holds everything is just
                        // the menu again.
                        favs = favs.slice(0, MAX - 1);
                    }
                    favs.push({
                        href: href,
                        label: labelFor(a),
                        section: sectionFor(a),
                        icon: iconFor(a)
                    });
                }
                write(favs);
                renderBar();
                paintStars();
            });

            a.appendChild(star);
        });
    }

    function init() {
        try {
            attachStars();
            paintStars();
            renderBar();
        } catch (e) {
            if (window.console && console.warn) {
                console.warn('[FrexCore] favourites unavailable:', e);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
