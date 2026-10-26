<div class="announcement-modal" id="announcement-modal" hidden>
    <section class="announcement-dialog" role="dialog" aria-modal="true" aria-labelledby="announcement-dialog-title" tabindex="-1">
        <header class="announcement-dialog-head">
            <div><h3 id="announcement-dialog-title"></h3><time id="announcement-dialog-time"></time></div>
            <button class="announcement-dialog-close" type="button" data-close-announcement aria-label="Close announcement">&times;</button>
        </header>
        <div class="announcement-dialog-content ck-content rich-content" id="announcement-dialog-content"></div>
        <footer class="announcement-dialog-footer"><button type="button" data-close-announcement>Close</button></footer>
    </section>
</div>

<style>
    .announcement-modal{--navy:#13176b;--gold:#FFD501;--line:#e5e7eb;--muted:#6b7280;position:fixed;inset:0;z-index:4000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(12,15,45,.56);font-family:"Inter",sans-serif;}
    .announcement-modal[hidden]{display:none;}
    .announcement-dialog{display:flex;flex-direction:column;width:min(680px,100%);max-height:min(80vh,760px);overflow:hidden;border:1px solid var(--line);border-radius:18px;background:#fff;box-shadow:0 24px 70px rgba(12,15,77,.28);}
    .announcement-dialog-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:1px solid var(--line);}
    .announcement-dialog-head h3{margin:0;color:var(--navy);font-size:19px;}
    .announcement-dialog-head time{display:block;margin-top:5px;color:var(--muted);font-size:12px;}
    .announcement-dialog-close{width:34px;height:34px;flex:0 0 34px;border:0;border-radius:9px;background:#f1f3f8;color:var(--navy);font-size:21px;}
    .announcement-dialog-content{overflow:auto;padding:22px;color:#374151;font-size:14px;line-height:1.65;}
    .announcement-dialog-content p{margin:0 0 12px;}
    .announcement-dialog-content p:last-child{margin-bottom:0;}
    .announcement-dialog-content h1,.announcement-dialog-content h2,.announcement-dialog-content h3,.announcement-dialog-content h4{margin:18px 0 9px;line-height:1.3;color:var(--navy);}
    .announcement-dialog-content ul,.announcement-dialog-content ol{padding-left:24px;}
    .announcement-dialog-content blockquote{margin:12px 0;padding-left:14px;border-left:3px solid var(--gold);color:#5f6b85;}
    .announcement-dialog-content img{max-width:100%;height:auto;}
    .announcement-dialog-content a{color:var(--navy);text-decoration:underline;}
    .announcement-dialog-read-error{margin:12px 22px 0;padding:10px 12px;border-radius:8px;background:#fef2f2;color:#991b1b;font-size:12px;}
    .announcement-dialog-footer{display:flex;justify-content:flex-end;padding:14px 22px;border-top:1px solid var(--line);}
    .announcement-dialog-footer button{padding:8px 16px;border:0;border-radius:8px;background:var(--navy);color:#fff;font-size:13px;font-weight:700;}
    body.announcement-modal-open{overflow:hidden;}
    @media(max-width:700px){.announcement-dialog{width:100%;max-height:85vh;}}
</style>

<script>
    (function () {
        var modal = document.getElementById('announcement-modal');
        if (!modal) return;

        var title = document.getElementById('announcement-dialog-title');
        var time = document.getElementById('announcement-dialog-time');
        var content = document.getElementById('announcement-dialog-content');
        var previousFocus = null;

        function updateUnreadCount(count) {
            document.querySelectorAll('[data-unread-notification-count]').forEach(function (element) {
                element.textContent = count;
            });

            var trigger = document.getElementById('auth-notifications-trigger');
            if (!trigger) return;

            var badge = trigger.querySelector('.auth-topbar-badge');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'auth-topbar-badge';
                    trigger.appendChild(badge);
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        }

        function markAnnouncementRead(announcementId, item) {
            if (!announcementId || (!item.classList.contains('is-unread') && !item.closest('.notif-item.unread'))) {
                return;
            }

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            var readUrl = @json(route('notifications.announcements.read', ['announcement' => '__ANNOUNCEMENT__']));

            fetch(readUrl.replace('__ANNOUNCEMENT__', encodeURIComponent(announcementId)), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Announcement could not be marked as read.');
                    return response.json();
                })
                .then(function (data) {
                    item.classList.remove('is-unread');
                    var notificationRow = item.closest('.notif-item');
                    if (notificationRow) {
                        notificationRow.classList.remove('unread');
                        notificationRow.querySelector('.unread-dot')?.remove();
                    }
                    updateUnreadCount(data.unread_count);
                })
                .catch(function () {
                    var message = modal.querySelector('[data-announcement-read-error]');
                    if (!message) {
                        message = document.createElement('p');
                        message.className = 'announcement-dialog-read-error';
                        message.dataset.announcementReadError = '';
                        message.setAttribute('role', 'alert');
                        modal.querySelector('.announcement-dialog').insertBefore(message, content);
                    }
                    message.textContent = 'Unable to mark this announcement as read. Please try again.';
                });
        }

        function openAnnouncement(announcementTitle, announcementTime, announcementContent, trigger, announcementId) {
            previousFocus = trigger;
            title.textContent = announcementTitle || 'Announcement';
            time.textContent = announcementTime || '';
            content.innerHTML = announcementContent || '';
            modal.hidden = false;
            document.body.classList.add('announcement-modal-open');
            modal.querySelector('.announcement-dialog').focus();
            markAnnouncementRead(announcementId, trigger);
        }

        function closeAnnouncement() {
            modal.hidden = true;
            content.replaceChildren();
            modal.querySelector('[data-announcement-read-error]')?.remove();
            document.body.classList.remove('announcement-modal-open');
            previousFocus?.focus();
        }

        document.addEventListener('click', function (event) {
            var target = event.target instanceof Element ? event.target : event.target.parentElement;
            var previewItem = target && target.closest('[data-notification-type="announcement"]');
            if (previewItem) {
                event.preventDefault();
                openAnnouncement(
                    previewItem.dataset.announcementTitle,
                    previewItem.dataset.announcementTime,
                    previewItem.dataset.announcementContent,
                    previewItem,
                    previewItem.dataset.announcementId
                );
                return;
            }

            var viewButton = target && target.closest('[data-view-announcement]');
            if (!viewButton) return;

            var template = document.getElementById('announcement-content-' + viewButton.dataset.viewAnnouncement);
            if (!template) return;

            event.preventDefault();
            openAnnouncement(
                viewButton.dataset.title,
                viewButton.dataset.time,
                template.innerHTML,
                viewButton,
                viewButton.dataset.viewAnnouncement
            );
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeAnnouncement();
                return;
            }

            if (event.key !== 'Enter' && event.key !== ' ') return;
            var target = event.target instanceof Element ? event.target : event.target.parentElement;
            var previewItem = target && target.closest('[data-notification-type="announcement"]');
            if (!previewItem) return;

            event.preventDefault();
            openAnnouncement(
                previewItem.dataset.announcementTitle,
                previewItem.dataset.announcementTime,
                previewItem.dataset.announcementContent,
                previewItem,
                previewItem.dataset.announcementId
            );
        });

        modal.querySelectorAll('[data-close-announcement]').forEach(function (button) {
            button.addEventListener('click', closeAnnouncement);
        });
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeAnnouncement();
        });

    })();
</script>
