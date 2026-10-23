
<x-admin.layout title="Stacking Frameworks | UPSKILL" page="admin-stacking-frameworks" :user="$user" :use-auth-user="false">
    <x-slot:head>
        <link rel="icon" type="image/png" href="{{ asset('Images/PSU-Logo.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('Images/PSU-Logo.png') }}">
    </x-slot:head>
<div class="layout admin-layout">
        <main class="main framework-main">
            <div class="framework-content">
                <header class="framework-header">
                    <div>
                        <h1>Stacking frameworks</h1>
                        <p>Define approved microcredential combinations without creating academic credit automatically.</p>
                    </div>
                    <div class="framework-header-actions">
                        <button type="button" class="framework-button primary" data-open-framework-modal="create-framework-modal">+ Create Framework</button>
                    </div>
                </header>

                <x-flash-toast :message="session('success')" />
                @if($errors->any())
                    <div class="notice notice-error">{{ $errors->first() }}</div>
                @endif

                <div class="framework-section-title">
                    <h2>Configured frameworks</h2>
                    <span>{{ $frameworks->count() }} configured</span>
                </div>

                <section class="framework-list">
                    @forelse($frameworks as $framework)
                        @php
                            $statusClass = match ($framework->status) {
                                'approved' => 'approved',
                                'inactive' => 'inactive',
                                'pending_approval' => 'pending',
                                default => '',
                            };
                            $description = \Illuminate\Support\Str::limit(strip_tags((string) $framework->description), 150);
                        @endphp

                        <article class="framework-card">
                            <div class="framework-card-top">
                                <div class="framework-card-title">
                                    <h3>{{ $framework->name }}</h3>
                                    <p class="framework-description">{{ $description !== '' ? $description : 'No description provided.' }}</p>
                                    <div class="framework-badges">
                                        <span class="framework-pill {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($framework->status)) }}</span>
                                        <span class="framework-pill">{{ $framework->is_active ? 'Active' : 'Inactive' }}</span>
                                        @if($framework->pqf_level)<span class="framework-pill">PQF {{ $framework->pqf_level }}</span>@endif
                                    </div>
                                </div>
                                <div class="framework-card-action">
                                    <button type="button" class="framework-button secondary" data-open-framework-modal="edit-framework-modal-{{ $framework->id }}">
                                        {{ $framework->status === 'approved' ? 'View' : 'Edit' }}
                                    </button>
                                </div>
                            </div>

                            <div class="framework-info">
                                <div class="framework-info-item"><span>Recognition</span><strong>{{ $framework->target_recognition ?: '—' }}</strong></div>
                                <div class="framework-info-item"><span>Equivalent course</span><strong>{{ $framework->equivalent_course ?: '—' }}</strong></div>
                                <div class="framework-info-item"><span>Completion rule</span><strong>{{ $framework->completion_mode === 'minimum_required' ? 'Minimum '.$framework->required_count.' required' : 'All required' }}</strong></div>
                                <div class="framework-info-item"><span>Academic unit</span><strong>{{ $framework->approving_academic_unit ?: '—' }}</strong></div>
                                <div class="framework-info-item"><span>Sequence</span><strong>{{ $framework->sequence_required ? 'Required' : 'Not required' }}</strong></div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">No stacking frameworks have been configured yet.</div>
                    @endforelse
                </section>
            </div>
        </main>
    </div>

    {{-- Create modal --}}
    <div class="framework-modal" id="create-framework-modal" aria-hidden="true">
        <div class="framework-modal-card" role="dialog" aria-modal="true" aria-labelledby="create-framework-modal-title">
            <div class="framework-modal-head">
                <h2 id="create-framework-modal-title">Create framework draft</h2>
                <button type="button" class="framework-modal-close" data-close-framework-modal aria-label="Close">&times;</button>
            </div>
            <div class="framework-modal-body">
                <form method="POST" action="{{ route('admin.stacking-frameworks.store') }}" id="create-framework-form">
                    @csrf
                    <input type="hidden" name="form_context" value="create-framework-modal">
                    @include('admin.stacking-framework-fields', ['framework' => null, 'formId' => 'create-framework-form'])
                    <div class="actions">
                        <button class="framework-button primary" type="submit">Save draft</button>
                        <button class="framework-button secondary" type="button" data-close-framework-modal>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit/view modals --}}
    @foreach($frameworks as $framework)
        <div class="framework-modal" id="edit-framework-modal-{{ $framework->id }}" aria-hidden="true">
            <div class="framework-modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-framework-modal-title-{{ $framework->id }}">
                <div class="framework-modal-head">
                    <h2 id="edit-framework-modal-title-{{ $framework->id }}">{{ $framework->status === 'approved' ? 'View' : 'Edit' }} framework</h2>
                    <button type="button" class="framework-modal-close" data-close-framework-modal aria-label="Close">&times;</button>
                </div>
                <div class="framework-modal-body">
                    <form method="POST" action="{{ route('admin.stacking-frameworks.update', $framework->id) }}" id="framework-form-{{ $framework->id }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="form_context" value="edit-framework-modal-{{ $framework->id }}">
                        <fieldset @disabled($framework->status === 'approved') class="adm-inline-admin-stacking-frameworks-1">
                            @include('admin.stacking-framework-fields', ['framework' => $framework, 'formId' => 'framework-'.$framework->id])
                        </fieldset>

                        <div class="actions">
                            @if($framework->status !== 'approved')
                                <button class="framework-button primary" type="submit">Save draft</button>
                            @endif
                            @if(in_array($framework->status, ['draft', 'pending_approval', 'inactive'], true))
                                <button class="framework-button primary" type="submit" form="activate-framework-form-{{ $framework->id }}">Activate</button>
                            @endif
                            @if($framework->status === 'approved')
                                <button class="framework-button warning" type="submit" form="deactivate-framework-form-{{ $framework->id }}">Deactivate</button>
                            @endif
                            <button class="framework-button secondary" type="button" data-close-framework-modal>Close</button>
                        </div>
                    </form>

                    @if(in_array($framework->status, ['draft', 'pending_approval', 'inactive'], true))
                        <form method="POST" action="{{ route('admin.stacking-frameworks.approve', $framework->id) }}" id="activate-framework-form-{{ $framework->id }}" hidden>
                            @csrf
                        </form>
                    @endif

                    @if($framework->status === 'approved')
                        <form method="POST" action="{{ route('admin.stacking-frameworks.deactivate', $framework->id) }}" id="deactivate-framework-form-{{ $framework->id }}" hidden>
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <script>
        (function () {
            function openModal(id) {
                var modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal(modal) {
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                if (!document.querySelector('.framework-modal.is-open')) document.body.style.overflow = '';
            }

            document.addEventListener('click', function (event) {
                var openButton = event.target.closest('[data-open-framework-modal]');
                if (openButton) {
                    openModal(openButton.getAttribute('data-open-framework-modal'));
                    return;
                }

                var closeButton = event.target.closest('[data-close-framework-modal]');
                if (closeButton) {
                    closeModal(closeButton.closest('.framework-modal'));
                    return;
                }

                if (event.target.classList.contains('framework-modal')) closeModal(event.target);
            });

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                var modal = document.querySelector('.framework-modal.is-open');
                if (modal) closeModal(modal);
            });

            @if($errors->any())
                openModal(@js(old('form_context', 'create-framework-modal')));
            @endif
        })();
    </script>
</x-admin.layout>
