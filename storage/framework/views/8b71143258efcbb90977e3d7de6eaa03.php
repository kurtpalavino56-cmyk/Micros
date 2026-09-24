<footer class="footer">
    <div class="container footer__grid">

        <div class="footer__brand-col">
            <a href="<?php echo e(url('/')); ?>" class="footer__brand">
                
                <span>UPSKILL</span>
            </a>
            <p class="footer__tagline">The Official Platform for PSU Microcredentials</p>
            <p class="footer__copy">&copy; <?php echo e(date('Y')); ?> Pangasinan State University. All rights reserved.</p>
        </div>

        <div class="footer__col">
            <h4 class="footer__heading">Platform</h4>
            <ul class="footer__list">
                <li><a href="<?php echo e(url('/explore')); ?>">Explore Courses</a></li>
                <li><a href="<?php echo e(url('/microcredentials')); ?>">Microcredentials</a></li>
                <li><a href="<?php echo e(url('/announcements')); ?>">Announcements</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4 class="footer__heading">Account</h4>
            <ul class="footer__list">
                <li><a href="<?php echo e(url('/login')); ?>">Login</a></li>
                <li><a href="<?php echo e(url('/register')); ?>">Enroll Now</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4 class="footer__heading">Support</h4>
            <ul class="footer__list">
                <li><a href="<?php echo e(url('/help')); ?>">Help Center</a></li>
                <li><a href="<?php echo e(url('/privacy')); ?>">Privacy Policy</a></li>
                <li><a href="<?php echo e(url('/terms')); ?>">Terms and Conditions</a></li>
            </ul>
        </div>

    </div>
</footer>

<style>
.footer {
    background: var(--navy-dark);
    color: rgba(255,255,255,0.7);
    padding: 3.5rem 0 2.5rem;
    font-size: 0.88rem;
}
.footer__grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 2.5rem;
}
.footer__brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--white);
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.1rem;
    letter-spacing: 0.04em;
    margin-bottom: 0.75rem;
}
.footer__tagline { color: rgba(255,255,255,0.55); line-height: 1.5; margin-bottom: 1rem; max-width: 260px; }
.footer__copy    { color: rgba(255,255,255,0.35); font-size: 0.78rem; }
.footer__heading { color: var(--gold); font-weight: 700; margin-bottom: 0.85rem; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.08em; }
.footer__list    { list-style: none; display: flex; flex-direction: column; gap: 0.55rem; }
.footer__list a  { color: rgba(255,255,255,0.6); transition: color var(--transition); }
.footer__list a:hover { color: var(--gold); }

/* The three link columns stay side by side on every screen size — only the
   brand block goes full width above them. Previously this collapsed to two
   columns at 768px and a single stacked column at 480px, which made the
   footer very tall on phones. */
@media (max-width: 768px) {
    .footer__grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.75rem 1.25rem;
    }
    .footer__brand-col { grid-column: 1 / -1; }
    .footer__tagline   { max-width: none; }
}

@media (max-width: 520px) {
    .footer { padding: 2.5rem 0 2rem; }
    .footer__grid    { gap: 1.5rem 0.9rem; }
    .footer__heading { font-size: 0.72rem; letter-spacing: 0.05em; margin-bottom: 0.6rem; }
    .footer__list    { gap: 0.45rem; font-size: 0.8rem; }
    /* "Microcredentials" is long — let it wrap rather than widen the column */
    .footer__list a  { overflow-wrap: anywhere; line-height: 1.35; display: inline-block; }
}
</style><?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/components/footer.blade.php ENDPATH**/ ?>