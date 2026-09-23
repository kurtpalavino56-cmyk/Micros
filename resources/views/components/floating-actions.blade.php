{{--
    resources/views/components/floating-actions.blade.php

    Floating back-to-top button, shared by every page.

    Include this ONCE from components/responsive.blade.php:

        @include('components.floating-actions')

    Because every Admin_*, Faculty_*, Student_* and public view already ends
    with @include('components.responsive'), that single line puts the button
    on all of them — no per-page edits needed.

    21 pages currently paste this button inline with id="back-to-top-btn".
    Two elements sharing an id breaks getElementById, so the script below
    checks for an existing button first and does nothing if one is present.
    That means this partial is safe to add before cleaning up the inline
    copies, and it keeps working after you delete them.
--}}

<style>
    #shared-back-to-top {
        position: fixed;
        right: 26px;
        bottom: 26px;
        z-index: 2000;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        border: none;
        background: #13176b;
        color: #fff;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(19, 23, 107, .35);
        transition: transform .15s ease, background .2s ease;
    }
    #shared-back-to-top:hover {
        background: #dba617;
        transform: translateY(-3px);
    }
    #shared-back-to-top svg { width: 22px; height: 22px; }

    /* Sit clear of the drawer-nav toggle from components/responsive,
       which occupies the bottom-right corner on small screens. */
    @media (max-width: 980px) {
        #shared-back-to-top { right: 26px; bottom: 88px; }
    }
</style>

<script>
(function () {
    // A page that still has its own inline copy keeps using that one.
    if (document.getElementById('back-to-top-btn')) return;

    var btn = document.createElement('button');
    btn.id = 'shared-back-to-top';
    btn.type = 'button';
    btn.title = 'Back to top';
    btn.setAttribute('aria-label', 'Back to top');
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
                    'stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>';
    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.body.appendChild(btn);

    function toggle() {
        var y = window.scrollY || document.documentElement.scrollTop;
        btn.style.display = y > 400 ? 'flex' : 'none';
    }

    window.addEventListener('scroll', toggle, { passive: true });
    toggle();
})();
</script>
