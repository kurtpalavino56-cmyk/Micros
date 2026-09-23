{{--
    resources/views/privacy.blade.php

    Public Privacy Policy, linked from the footer.

    Extends layouts.app so it carries the same navbar and footer as the
    homepage. The .legal styles below are shared with terms.blade.php —
    keep the two in step if you change one.
--}}
@extends('layouts.app')

@section('title', 'Privacy Policy – UpSkill PSU')

@push('styles')
<style>
    .legal { padding: 3.5rem 0 4.5rem; background: #f4f6fb; }

    .legal__card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        padding: 2.6rem 2.8rem 3rem;
        max-width: 880px;
        margin: 0 auto;
    }

    .legal__title {
        font-family: var(--font-display);
        font-size: clamp(1.6rem, 3vw, 2.1rem);
        font-weight: 800;
        color: var(--navy);
        line-height: 1.25;
        margin-bottom: 0.9rem;
    }

    .legal__dates {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1.5rem;
        padding-bottom: 1.4rem;
        margin-bottom: 1.8rem;
        border-bottom: 1px solid #e5e7eb;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .legal__dates strong { color: var(--navy); font-weight: 700; }

    .legal h2 {
        font-family: var(--font-display);
        font-size: 1.12rem;
        font-weight: 800;
        color: var(--navy);
        margin: 2rem 0 0.7rem;
    }
    .legal h3 {
        font-size: 0.97rem;
        font-weight: 700;
        color: var(--navy);
        margin: 1.3rem 0 0.5rem;
    }
    .legal p {
        font-size: 0.93rem;
        line-height: 1.75;
        color: #3f4a63;
        margin-bottom: 0.9rem;
    }
    .legal ul {
        margin: 0 0 1.1rem 1.15rem;
        padding: 0;
    }
    .legal li {
        font-size: 0.93rem;
        line-height: 1.7;
        color: #3f4a63;
        margin-bottom: 0.38rem;
        padding-left: 0.2rem;
    }
    .legal li::marker { color: var(--gold); }

    .legal__note {
        background: #f7f9ff;
        border: 1px solid #dfe4fb;
        border-left: 4px solid var(--navy);
        border-radius: 10px;
        padding: 1rem 1.15rem;
        margin: 1.6rem 0;
    }
    .legal__note p { margin: 0; font-size: 0.9rem; }


    @media (max-width: 640px) {
        .legal { padding: 2.2rem 0 3rem; }
        .legal__card { padding: 1.6rem 1.3rem 2rem; }
    }
</style>
@endpush

@section('content')
<section class="legal">
    <div class="container">
        <div class="legal__card">

            <h1 class="legal__title">UPSKILL: Micro-Credentials</h1>

            <div class="legal__dates">
                <span><strong>Effective Date:</strong> August 30, 2026</span>
                <span><strong>Last Updated:</strong> August 31, 2026</span>
            </div>

            <h2>Introduction</h2>
            <p>
                Pangasinan State University (“we,” “our,” or “us”) respects your privacy and is
                committed to protecting the personal information you provide when using our
                Micro-Credentials Program, platform, website, or related services (“Services”).
            </p>
            <p>
                This Privacy Policy explains how we collect, use, store, disclose, and protect your
                personal information in connection with the issuance, management, verification, and
                sharing of micro-credentials.
            </p>
            <p>
                By using our Services, you acknowledge that you have read and understood this
                Privacy Policy.
            </p>

            <h2>Information We Collect</h2>
            <p>We may collect the following types of information:</p>

            <h3>A. Personal Information</h3>
            <p>Depending on the Services you use, we may collect:</p>
            <ul>
                <li>Full name</li>
                <li>Email address</li>
                <li>Contact information</li>
                <li>Student, Faculty, Administrator's identification number</li>
                <li>Organization, school, department, or affiliation</li>
                <li>Date of birth, where necessary</li>
                <li>Profile photograph, if voluntarily provided</li>
                <li>Login credentials and account information</li>
            </ul>

            <h3>B. Micro-Credentials and Achievement Information</h3>
            <p>We may collect and maintain information related to your micro-credentials, including:</p>
            <ul>
                <li>Name and description of the micro-credential</li>
                <li>Date of issuance</li>
                <li>Completion or assessment status</li>
                <li>Skills and competencies demonstrated</li>
                <li>Assessment results or evidence of achievement</li>
                <li>Credential ID or unique identifier</li>
                <li>Expiration or renewal information, where applicable</li>
                <li>Verification status</li>
            </ul>

            <h3>C. Technical and Usage Information</h3>
            <p>
                When you use our platform, we may automatically collect certain technical
                information, such as:
            </p>
            <ul>
                <li>IP address</li>
                <li>Browser and device information</li>
                <li>Operating system</li>
                <li>Login dates and times</li>
                <li>Pages or features accessed</li>
                <li>Platform usage and activity logs</li>
                <li>Security and diagnostic information</li>
            </ul>

            <h2>How We Use Your Information</h2>
            <p>We may use your information to:</p>
            <ul>
                <li>Create and manage your micro-credential account;</li>
                <li>Process and issue micro-credentials;</li>
                <li>Verify the authenticity of credentials;</li>
                <li>Maintain records of completed courses, assessments, and competencies;</li>
                <li>Provide access to credential certificates, badges, or digital records;</li>
                <li>Communicate with you regarding your credentials and account;</li>
                <li>Provide technical support;</li>
                <li>Improve our programs, platform, and services;</li>
                <li>Protect the security and integrity of our systems;</li>
                <li>Comply with applicable laws, regulations, and institutional requirements; and</li>
                <li>Perform other purposes that you have been informed of and, where required, have consented to.</li>
            </ul>

            <h2>Sharing and Disclosure of Information</h2>
            <div class="legal__note">
                <p><strong>We do not sell your personal information.</strong></p>
            </div>
            <p>We may disclose information to the following parties when necessary:</p>
            <ul>
                <li>Authorized personnel within [Organization/Institution Name];</li>
                <li>Credentialing or technology service providers that help us operate the micro-credential platform;</li>
                <li>Educational institutions, employers, or partner organizations, where necessary and permitted;</li>
                <li>Credential verification services, when you request or authorize verification;</li>
                <li>Government authorities or regulators, when disclosure is required by law; and</li>
                <li>Other parties where you have provided appropriate consent or where disclosure is otherwise permitted by applicable law.</li>
            </ul>
            <p>
                We require third-party service providers that process personal information on our
                behalf to implement appropriate privacy and security measures.
            </p>

            <h2>Publicly Shareable Credential Information</h2>
            <p>Some micro-credentials may be designed to be publicly verifiable or shareable.</p>
            <p>
                If you choose to publish or share your micro-credential, certain information may
                become accessible to others, such as:
            </p>
            <ul>
                <li>Your name;</li>
                <li>Credential title;</li>
                <li>Issuing organization;</li>
                <li>Date of issuance;</li>
                <li>Credential ID;</li>
                <li>Skills or competencies associated with the credential;</li>
                <li>Credential verification status.</li>
            </ul>
            <p>
                You should review the privacy and sharing settings available through the platform
                before making a credential publicly accessible.
            </p>

            <h2>Consent</h2>
            <p>
                Where required by applicable privacy laws, we will obtain your consent before
                collecting, using, or disclosing your personal information.
            </p>
            <p>
                You may withdraw your consent where permitted by law. However, withdrawing consent
                may affect our ability to provide certain Services, including issuing, maintaining,
                or verifying your micro-credentials.
            </p>

            <h2>Retention of Personal Information</h2>
            <p>
                We retain personal information only for as long as reasonably necessary to fulfill
                the purposes described in this Privacy Policy, including maintaining appropriate
                records of micro-credential issuance and verification.
            </p>
            <p>
                Certain credential records may need to be retained for longer periods to preserve
                the integrity and authenticity of credentials, comply with legal or institutional
                requirements, or prevent fraudulent credential claims.
            </p>

            <h2>Security</h2>
            <p>
                We take reasonable administrative, technical, and organizational measures to protect
                personal information against unauthorized access, disclosure, alteration, loss, or
                destruction.
            </p>
            <p>
                However, no electronic system or method of transmitting information over the
                Internet can be guaranteed to be completely secure.
            </p>

            <h2>Your Privacy Rights</h2>
            <p>Subject to applicable laws and regulations, you may have the right to:</p>
            <ul>
                <li>Request access to your personal information;</li>
                <li>Request correction of inaccurate or incomplete information;</li>
                <li>Request deletion of personal information, where legally permitted;</li>
                <li>Object to or restrict certain processing activities;</li>
                <li>Withdraw consent where processing is based on consent;</li>
                <li>Request a copy of certain personal information; and</li>
                <li>Lodge a complaint with the appropriate privacy or data protection authority.</li>
            </ul>
            <p>To exercise your rights, please contact us using the information provided below.</p>

            <h2>User's Privacy</h2>
            <p>
                Our Services are not intended to collect personal information from Users without
                appropriate authorization or consent where required by applicable law.
            </p>
            <p>
                If our Micro-Credentials Program is intended for minors or students under the
                applicable age of consent, we will implement appropriate safeguards and obtain
                consent or authorization when required.
            </p>

            <h2>Third-Party Services and Links</h2>
            <p>
                Our platform may contain links to third-party websites, applications, or services.
                We are not responsible for the privacy practices or content of third-party services.
            </p>
            <p>
                We encourage you to review the privacy policies of third-party services before
                providing personal information.
            </p>

            <h2>Changes to This Privacy Policy</h2>
            <p>
                We may update this Privacy Policy from time to time to reflect changes in our
                Services, legal requirements, or privacy practices.
            </p>
            <p>
                When we make significant changes, we will provide appropriate notice through our
                website, platform, email, or other reasonable means.
            </p>
            <p>The updated Privacy Policy will indicate the date on which it was last revised.</p>


        </div>
    </div>
</section>
@endsection
