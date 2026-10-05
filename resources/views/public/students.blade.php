@extends('layouts.app')

@section('title', 'Credential Verification | UpSkill')

@push('styles')
<style>
    .credential-lookup-page {
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 150px);
        background: linear-gradient(170deg, var(--navy) 0%, var(--navy-dark) 55%, #0e2b8e 100%);
    }

    .students-hero {
        display: flex;
        flex: 1 0 auto;
        align-items: center;
        justify-content: center;
        padding: 3rem 20px;
        background: transparent;
        text-align: center;
        width: 100%;
    }

    .credential-lookup {
        width: min(100%, 760px);
        margin: 0 auto;
        text-align: center;
    }

    .credential-lookup h1 {
        margin: 0;
        color: var(--white);
        font-family: var(--font-display);
        font-size: 2.4rem;
        font-weight: 800;
        line-height: 1.25;
    }

    .credential-lookup__intro {
        margin: 0 0 2rem;
        color: rgba(255,255,255,.75);
        font-size: .98rem;
    }

    .credential-search {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .45rem;
        padding-left: 1.4rem;
        border: 0;
        border-radius: 50px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,.22);
        text-align: left;
    }

    .credential-search:focus-within {
        box-shadow: 0 0 0 3px rgba(255, 213, 1, .45), 0 10px 30px rgba(0, 0, 0, .22);
    }

    .credential-search__icon {
        width: 20px;
        height: 20px;
        margin-left: 0;
        color: var(--text-muted);
        flex: 0 0 auto;
    }

    .credential-search__input {
        width: 100%;
        min-width: 0;
        height: 46px;
        padding: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: var(--navy);
        font: 400 .95rem var(--font-body);
    }

    .credential-lookup-page .credential-search input.credential-search__input[type="search"],
    .credential-lookup-page .credential-search input.credential-search__input[type="search"]:focus {
        border: 0 !important;
        border-radius: 0;
        outline: 0 !important;
        box-shadow: none !important;
        background: transparent;
    }

    .credential-search__input::placeholder {
        color: #929db0;
        opacity: .7;
    }

    .credential-search__button {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex: 0 0 auto;
        padding: 0 18px;
        border: 0;
        border-radius: 50px;
        background: var(--gold);
        color: var(--navy-dark);
        cursor: pointer;
        font: 700 .88rem var(--font-body);
        transition: background var(--transition), box-shadow var(--transition);
    }

    .credential-search__button:hover {
        background: var(--gold-light);
        box-shadow: 0 6px 18px rgba(232,168,0,.4);
    }

    .credential-search__button svg {
        width: 17px;
        height: 17px;
    }

    .credential-search__tools {
        display: flex;
        min-height: 20px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 8px;
        color: rgba(255,255,255,.72);
        font-size: 12px;
    }

    .students-hero__hint {
        margin: .85rem 0 0;
        color: rgba(255,255,255,.55);
        font-size: .8rem;
    }

    .students-hero__hint code {
        padding: .1rem .45rem;
        border-radius: 6px;
        background: rgba(255,255,255,.12);
        color: var(--gold-light);
        font-size: .78rem;
    }

    .credential-dropzone {
        display: flex;
        width: min(100%, 520px);
        min-height: 76px;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 1.1rem auto 0;
        padding: 14px 18px;
        border: 1px dashed rgba(255,255,255,.5);
        border-radius: 12px;
        background: rgba(255,255,255,.06);
        color: rgba(255,255,255,.9);
        cursor: pointer;
        text-align: left;
        transition: background var(--transition), border-color var(--transition);
    }

    .credential-dropzone:hover,
    .credential-dropzone.is-dragging {
        border-color: var(--gold-light);
        background: rgba(255,255,255,.12);
    }

    .credential-dropzone:focus-within {
        outline: 2px solid var(--gold-light);
        outline-offset: 3px;
    }

    .credential-dropzone__icon {
        width: 25px;
        height: 25px;
        flex: 0 0 auto;
        color: var(--gold-light);
    }

    .credential-dropzone__copy { display: grid; gap: 3px; }
    .credential-dropzone__copy strong { color: #fff; font-size: .9rem; }
    .credential-dropzone__copy span { color: rgba(255,255,255,.68); font-size: .78rem; }

    .credential-dropzone input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        clip-path: inset(50%);
    }

    .credential-scan-status {
        min-height: 18px;
    }

    .credential-lookup-modal {
        position: fixed;
        inset: 0;
        z-index: 5000;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(7, 21, 80, .68);
        backdrop-filter: blur(3px);
    }

    .credential-lookup-modal__dialog {
        position: relative;
        width: min(100%, 440px);
        padding: 30px 28px 26px;
        border: 1px solid rgba(10, 31, 110, .1);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 64px rgba(0, 0, 0, .3);
        text-align: center;
    }

    .credential-lookup-modal__icon {
        display: grid;
        width: 48px;
        height: 48px;
        margin: 0 auto 14px;
        place-items: center;
        border-radius: 50%;
        background: #fff4cf;
        color: #806208;
    }

    .credential-lookup-modal__icon svg { width: 24px; height: 24px; }
    .credential-lookup-modal__dialog h2 {
        margin: 0 0 8px;
        color: var(--navy);
        font-family: var(--font-display);
        font-size: 20px;
        font-weight: 800;
    }

    .credential-lookup-modal__dialog p {
        margin: 0 0 22px;
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .credential-lookup-modal__close {
        position: absolute;
        top: 10px;
        right: 12px;
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: #65718a;
        cursor: pointer;
        font-size: 25px;
        line-height: 1;
    }

    .credential-lookup-modal__close:hover { background: #f1f4fa; color: var(--navy); }

    .credential-lookup-modal__button {
        min-height: 42px;
        padding: 0 22px;
        border: 0;
        border-radius: 50px;
        background: var(--gold);
        color: var(--navy-dark);
        cursor: pointer;
        font: 700 14px var(--font-body);
    }

    .credential-lookup-modal__button:hover { background: var(--gold-light); }

    .certfloat-overlay {
        position: fixed;
        inset: 0;
        z-index: 6000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        overflow-y: auto;
        background: rgba(6, 13, 46, .76);
        backdrop-filter: blur(4px);
    }

    .certfloat {
        position: relative;
        width: min(100%, 720px);
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        padding: 22px;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 30px 80px rgba(0, 0, 0, .5);
    }

    .certfloat__close {
        position: absolute;
        top: 12px;
        right: 14px;
        z-index: 2;
        display: grid;
        width: 34px;
        height: 34px;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, .92);
        color: #64748b;
        cursor: pointer;
        font-size: 25px;
        line-height: 1;
    }

    .certfloat__close:hover { color: var(--navy); }
    .certfloat__verified {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 16px;
        padding: 6px 14px;
        border: 1px solid #a7f3d0;
        border-radius: 999px;
        background: #ecfdf3;
        color: #065f46;
        font-size: 12.5px;
        font-weight: 800;
    }

    .certfloat__verified.is-revoked { border-color: #fecaca; background: #fff1f2; color: #b42318; }
    .certfloat__verified svg { width: 14px; height: 14px; }
    .certfloat__btn {
        width: 100%;
        min-height: 44px;
        margin-top: 18px;
        border: 0;
        border-radius: 10px;
        background: #0a1f6e;
        color: #fff;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
    }

    .certfloat__btn:hover { background: #071550; }

    @media (max-width: 600px) {
        .students-hero { padding: 2.5rem 16px; }
        .credential-lookup h1 { font-size: 1.8rem; }
        .credential-search { flex-wrap: wrap; }
        .credential-search__icon { margin-left: 2px; }
        .credential-search__input { flex: 1 1 calc(100% - 40px); }
        .credential-search__button { flex: 1 1 0; }
        .credential-dropzone { min-height: 72px; padding: 12px; }
        .certfloat { padding: 14px; }
        .certfloat__verified { margin-bottom: 10px; }
        .certfloat__btn { margin-top: 12px; }
    }
</style>
@endpush

@section('content')
<main class="credential-lookup-page">
    <section class="students-hero">
        <div class="credential-lookup" aria-labelledby="credential-lookup-title">
        <h1 id="credential-lookup-title">Verification</h1>
        <p class="credential-lookup__intro">Verify a certificate by Credential ID or scan its QR code.</p>

        <form class="credential-search" id="credentialLookupForm" method="GET" action="{{ route('students.index') }}">
            <svg class="credential-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input class="credential-search__input" id="credentialIdInput" type="search" name="credential_id"
                value="{{ $credentialId }}" placeholder="PSU-LC-MC-2026-123456" autocomplete="off" required
                aria-label="Certificate Credential ID">
            <button class="credential-search__button" id="credentialSearchButton" type="submit">Search</button>
        </form>
        <label class="credential-dropzone" id="credentialDropzone" for="credentialImageInput">
            <svg class="credential-dropzone__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 16.5v1A2.5 2.5 0 0 0 6.5 20h11a2.5 2.5 0 0 0 2.5-2.5v-1"/><path d="m8 9 4-4 4 4M12 5v10"/></svg>
            <span class="credential-dropzone__copy">
                <strong>Drop a QR image here or click to upload</strong>
                <span>Choose an image of the certificate QR code</span>
            </span>
            <input id="credentialImageInput" type="file" accept="image/*" aria-label="Upload an image of a certificate QR code">
        </label>
        <p class="students-hero__hint">Credential ID format: <code>PSU-LC-MC-2026-123456</code></p>
        <div class="credential-search__tools">
            <span class="credential-scan-status" id="credentialScanStatus" role="status" aria-live="polite"></span>
        </div>
        </div>
    </section>

</main>
@if ($credential)
    <div class="certfloat-overlay" id="credentialCertificateModal" role="presentation">
        <section class="certfloat" role="dialog" aria-modal="true" aria-labelledby="credentialCertificateTitle" tabindex="-1">
            <button class="certfloat__close" type="button" data-close-certificate-modal aria-label="Close">&times;</button>
            @if(($credential['status'] ?? 'active') === 'revoked')
                <div class="certfloat__verified is-revoked" id="credentialCertificateTitle" role="status">Revoked Certificate</div>
            @else
                <div class="certfloat__verified" id="credentialCertificateTitle" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    Verified Certificate
                </div>
            @endif
            @include('components.certificate', ['cert' => $credential])
            <button class="certfloat__btn" type="button" data-close-certificate-modal>Continue to UPSKILL</button>
        </section>
    </div>
@endif
@if ($hasSearched && !$credential)
    <div class="credential-lookup-modal" id="credentialNotFoundModal" role="presentation">
        <section class="credential-lookup-modal__dialog" role="alertdialog" aria-modal="true" aria-labelledby="credentialNotFoundTitle" aria-describedby="credentialNotFoundMessage" tabindex="-1">
            <button class="credential-lookup-modal__close" type="button" data-close-credential-modal aria-label="Close">&times;</button>
            <div class="credential-lookup-modal__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01"/><path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
            </div>
            <h2 id="credentialNotFoundTitle">Certificate not found</h2>
            <p id="credentialNotFoundMessage">No certificate matches that Credential ID. Check the ID printed on the certificate and try again.</p>
            <button class="credential-lookup-modal__button" type="button" data-close-credential-modal>Try again</button>
        </section>
    </div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('credentialLookupForm');
        const credentialInput = document.getElementById('credentialIdInput');
        const imageInput = document.getElementById('credentialImageInput');
        const dropzone = document.getElementById('credentialDropzone');
        const status = document.getElementById('credentialScanStatus');
        const notFoundModal = document.getElementById('credentialNotFoundModal');
        const certificateModal = document.getElementById('credentialCertificateModal');

        if (dropzone && imageInput) {
            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    dropzone.classList.add('is-dragging');
                });
            });

            dropzone.addEventListener('dragleave', function (event) {
                if (!event.relatedTarget || !dropzone.contains(event.relatedTarget)) {
                    dropzone.classList.remove('is-dragging');
                }
            });

            dropzone.addEventListener('drop', function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragging');

                const file = event.dataTransfer.files && event.dataTransfer.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    status.textContent = 'Choose an image file containing the certificate QR code.';
                    return;
                }

                const transfer = new DataTransfer();
                transfer.items.add(file);
                imageInput.files = transfer.files;
                imageInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        if (notFoundModal) {
            function closeNotFoundModal() {
                notFoundModal.remove();
                document.body.style.overflow = '';
                credentialInput.focus();
            }

            notFoundModal.querySelectorAll('[data-close-credential-modal]').forEach(function (button) {
                button.addEventListener('click', closeNotFoundModal);
            });
            notFoundModal.addEventListener('click', function (event) {
                if (event.target === notFoundModal) closeNotFoundModal();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && document.getElementById('credentialNotFoundModal')) {
                    closeNotFoundModal();
                }
            });
            document.body.style.overflow = 'hidden';
            notFoundModal.querySelector('.credential-lookup-modal__close').focus();
        }

        if (certificateModal) {
            function closeCertificateModal() {
                certificateModal.remove();
                document.body.style.overflow = '';
                if (window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
                credentialInput.focus();
            }

            certificateModal.querySelectorAll('[data-close-certificate-modal]').forEach(function (button) {
                button.addEventListener('click', closeCertificateModal);
            });
            certificateModal.addEventListener('click', function (event) {
                if (event.target === certificateModal) closeCertificateModal();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && document.getElementById('credentialCertificateModal')) {
                    closeCertificateModal();
                }
            });
            document.body.style.overflow = 'hidden';
            certificateModal.querySelector('.certfloat__close').focus();
        }

        function credentialIdFromQr(text) {
            const match = text.match(/PSU-LC-MC-\d{4}-\d{6}/i);
            return match ? match[0].toUpperCase() : null;
        }

        function detectWithBrowser(file) {
            if (!('BarcodeDetector' in window) || !('createImageBitmap' in window)) {
                return Promise.resolve(null);
            }

            return createImageBitmap(file).then(function (bitmap) {
                const detector = new BarcodeDetector({ formats: ['qr_code'] });
                return detector.detect(bitmap).then(function (codes) {
                    bitmap.close();
                    return codes.length ? codes[0].rawValue : null;
                }, function (error) {
                    bitmap.close();
                    throw error;
                });
            });
        }

        function loadQrDecoder() {
            if (window.jsQR) return Promise.resolve();

            return new Promise(function (resolve, reject) {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        function detectWithFallback(file) {
            return loadQrDecoder().then(function () {
                return new Promise(function (resolve, reject) {
                    const image = new Image();
                    const imageUrl = URL.createObjectURL(file);
                    image.onload = function () {
                        try {
                            const scale = Math.min(1, 2200 / Math.max(image.naturalWidth, image.naturalHeight));
                            const canvas = document.createElement('canvas');
                            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
                            const context = canvas.getContext('2d', { willReadFrequently: true });
                            context.drawImage(image, 0, 0, canvas.width, canvas.height);
                            const pixels = context.getImageData(0, 0, canvas.width, canvas.height);
                            const result = window.jsQR(pixels.data, pixels.width, pixels.height, { inversionAttempts: 'attemptBoth' });
                            URL.revokeObjectURL(imageUrl);
                            resolve(result ? result.data : null);
                        } catch (error) {
                            URL.revokeObjectURL(imageUrl);
                            reject(error);
                        }
                    };
                    image.onerror = function () {
                        URL.revokeObjectURL(imageUrl);
                        reject(new Error('The selected image could not be read.'));
                    };
                    image.src = imageUrl;
                });
            });
        }

        imageInput.addEventListener('change', async function () {
            const file = imageInput.files && imageInput.files[0];
            if (!file) return;

            status.textContent = 'Scanning the QR code…';

            try {
                let qrText = null;

                try {
                    qrText = await detectWithBrowser(file);
                } catch (error) {
                    qrText = null;
                }

                if (!qrText) qrText = await detectWithFallback(file);

                const credentialId = qrText ? credentialIdFromQr(qrText) : null;
                if (!credentialId) {
                    status.textContent = qrText
                        ? 'That QR code does not contain a recognized Credential ID.'
                        : 'No QR code was found. Enter the Credential ID printed on the certificate.';
                    imageInput.value = '';
                    return;
                }

                credentialInput.value = credentialId;
                status.textContent = 'Credential ID scanned. Searching…';
                form.requestSubmit();
            } catch (error) {
                status.textContent = 'Automatic scanning could not read this image. Enter the Credential ID manually.';
            } finally {
                imageInput.value = '';
            }
        });
    })();
</script>
@endpush
