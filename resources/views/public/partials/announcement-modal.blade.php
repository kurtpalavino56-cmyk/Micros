<div class="announcement-modal" id="announcement-modal" hidden>
    <div class="announcement-modal__backdrop" data-announcement-modal-close></div>
    <section class="announcement-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="announcement-modal-title" tabindex="-1">
        <header class="announcement-modal__header">
            <div>
                <time class="announcement-modal__date" id="announcement-modal-date"></time>
                <h2 id="announcement-modal-title"></h2>
            </div>
            <button class="announcement-modal__close" type="button" data-announcement-modal-close aria-label="Close announcement">&times;</button>
        </header>
        <div class="announcement-modal__body ck-content rich-content" id="announcement-modal-body"></div>
    </section>
</div>

<style>
    .announcement-modal[hidden] { display: none; }
    .announcement-modal { position: fixed; inset: 0; z-index: 3000; display: grid; place-items: center; padding: 1rem; }
    .announcement-modal__backdrop { position: absolute; inset: 0; background: rgba(7,20,63,.68); backdrop-filter: blur(4px); }
    .announcement-modal__dialog { position: relative; width: min(100%, 760px); max-height: min(85vh, 820px); overflow-y: auto; padding: 2rem; border-radius: 20px; background: #fff; box-shadow: 0 24px 80px rgba(7,20,63,.28); }
    .announcement-modal__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; padding-bottom: 1.1rem; border-bottom: 1px solid #e6eaf2; }
    .announcement-modal__date { display: block; margin-bottom: .4rem; color: #687694; font-size: .8rem; }
    .announcement-modal__header h2 { color: #142b75; font: 800 clamp(1.35rem, 3vw, 1.8rem)/1.25 var(--font-display); }
    .announcement-modal__close { flex: 0 0 auto; width: 38px; height: 38px; border: 0; border-radius: 50%; background: #f1f3f8; color: #142b75; font-size: 1.6rem; line-height: 1; cursor: pointer; }
    .announcement-modal__body { padding-top: 1.25rem; color: #43547f; font-size: 1rem; line-height: 1.75; overflow-wrap: anywhere; }
    .announcement-modal__body > :first-child { margin-top: 0; }
    .announcement-modal__body img { max-width: 100%; height: auto; border-radius: 12px; }
    .announcement-modal__body figure { margin: 1rem 0; }
    .announcement-modal__body figcaption { color: #687694; font-size: .85rem; text-align: center; }
    body.announcement-modal-open { overflow: hidden; }
    @media (max-width: 600px) { .announcement-modal__dialog { padding: 1.25rem; } }
</style>

<script>
    (() => {
        const modal = document.getElementById('announcement-modal');
        if (!modal) return;

        const dialog = modal.querySelector('.announcement-modal__dialog');
        const title = document.getElementById('announcement-modal-title');
        const date = document.getElementById('announcement-modal-date');
        const body = document.getElementById('announcement-modal-body');
        let previousFocus = null;

        const closeModal = () => {
            modal.hidden = true;
            document.body.classList.remove('announcement-modal-open');
            previousFocus?.focus();
        };

        const openModal = (card) => {
            previousFocus = card;
            title.textContent = card.querySelector('.ann-card__title').textContent;
            date.textContent = card.querySelector('.ann-card__date').textContent;
            body.innerHTML = card.querySelector('.ann-card__modal-content').innerHTML;
            modal.hidden = false;
            document.body.classList.add('announcement-modal-open');
            dialog.focus();
        };

        document.querySelectorAll('[data-announcement-card]').forEach((card) => {
            card.addEventListener('click', () => openModal(card));
            card.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openModal(card);
                }
            });
        });

        modal.querySelectorAll('[data-announcement-modal-close]').forEach((control) => {
            control.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
    })();
</script>
