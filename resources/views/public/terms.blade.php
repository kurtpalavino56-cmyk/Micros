{{--
    resources/views/terms.blade.php

    Public Terms and Conditions, linked from the footer.

    Extends layouts.app so it carries the same navbar and footer as the
    homepage. The .legal styles are duplicated from privacy.blade.php so
    each page stands alone — keep the two in step if you change one.
--}}
@extends('layouts.app')

@section('title', 'Terms and Conditions – UpSkill PSU')

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

    .legal__contact {
        background: #f7f9ff;
        border: 1px solid #dfe4fb;
        border-radius: 12px;
        padding: 1.2rem 1.4rem;
        margin: 1.2rem 0 0;
    }
    .legal__contact p { margin: 0 0 0.3rem; font-size: 0.9rem; }
    .legal__contact strong { color: var(--navy); }


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

            <h1 class="legal__title">Terms and Conditions<br>UPSKILL: Micro-Credentials</h1>

            <div class="legal__dates">
                <span><strong>Effective Date:</strong> August 30, 2026</span>
                <span><strong>Last Updated:</strong> August 31, 2026</span>
            </div>

            <h2>1. Acceptance of Terms</h2>
            <p>
                Welcome to UPSKILL: Micro-Credentials, a micro-credentialing program and platform of
                Pangasinan State University (PSU) (“PSU,” “we,” “our,” or “us”).
            </p>
            <p>
                These Terms and Conditions (“Terms”) govern your access to and use of the UPSKILL:
                Micro-Credentials platform, website, courses, assessments, digital credentials,
                certificates, badges, and related services (collectively, the “Services”).
            </p>
            <p>
                By registering for, accessing, or using the Services, you agree to be bound by these
                Terms and Conditions, as well as our applicable Privacy Policy and other policies or
                guidelines provided by PSU.
            </p>
            <p>If you do not agree with these Terms, you should not access or use the Services.</p>

            <h2>2. Eligibility</h2>
            <p>
                The UPSKILL: Micro-Credentials program may be available to students, faculty members,
                administrators, employees, alumni, professionals, industry partners, and other
                individuals who meet the eligibility requirements established by PSU or the specific
                micro-credential program.
            </p>
            <p>
                PSU reserves the right to establish eligibility requirements for individual
                micro-credentials, including educational qualifications, prerequisite courses, work
                experience, assessments, or other requirements.
            </p>
            <p>
                Where applicable, users below the age of legal consent must obtain the necessary
                authorization or consent from a parent, guardian, or authorized representative.
            </p>

            <h2>3. User Account and Registration</h2>
            <p>
                To access certain features of the Services, you may be required to create an account
                or provide registration information.
            </p>
            <p>You agree to:</p>
            <ul>
                <li>Provide accurate, complete, and current information;</li>
                <li>Maintain the confidentiality of your account credentials;</li>
                <li>Use your account only for legitimate and authorized purposes;</li>
                <li>Notify PSU promptly if you believe your account has been compromised; and</li>
                <li>Be responsible for activities conducted through your account.</li>
            </ul>
            <p>
                You must not create an account using another person's identity or provide false or
                misleading information.
            </p>
            <p>
                PSU reserves the right to suspend or terminate accounts that contain false
                information or are used in violation of these Terms.
            </p>

            <h2>4. Micro-Credentials</h2>
            <p>
                Micro-credentials are digital or electronic representations of demonstrated learning,
                skills, competencies, knowledge, or achievements.
            </p>
            <p>Each micro-credential may have specific requirements, including:</p>
            <ul>
                <li>Completion of designated learning activities;</li>
                <li>Submission of required outputs or evidence;</li>
                <li>Completion of assessments;</li>
                <li>Achievement of a required score or competency level;</li>
                <li>Participation in required activities; and</li>
                <li>Compliance with program-specific requirements.</li>
            </ul>
            <p>
                Meeting the requirements of one micro-credential does not automatically qualify a
                user for another micro-credential.
            </p>
            <p>
                PSU reserves the right to establish, modify, suspend, or discontinue micro-credential
                programs and their requirements, subject to applicable institutional policies and
                regulations.
            </p>

            <h2>5. Assessment and Verification</h2>
            <p>
                Users are expected to complete assessments and submit required evidence honestly and
                independently unless collaboration is expressly permitted by the specific program.
            </p>
            <p>PSU may verify the authenticity and validity of submitted work and credential claims.</p>
            <p>Users must not:</p>
            <ul>
                <li>Submit another person's work as their own;</li>
                <li>Falsify assessment results or supporting evidence;</li>
                <li>Manipulate or alter credential information;</li>
                <li>Misrepresent their qualifications or achievements; or</li>
                <li>Attempt to circumvent the platform's assessment or verification mechanisms.</li>
            </ul>
            <p>
                If academic dishonesty, fraud, misrepresentation, or other violations are established,
                PSU may withhold, revoke, suspend, or invalidate a micro-credential, subject to
                applicable university policies and appropriate procedures.
            </p>

            <h2>6. Issuance of Credentials</h2>
            <p>
                A micro-credential will be issued only after the user has satisfied all applicable
                requirements.
            </p>
            <p>
                PSU may issue credentials in digital or other authorized formats. Each credential may
                contain information necessary to establish its authenticity and validity, such as the
                recipient's name, credential title, issuing institution, date of issuance, credential
                ID, competencies, and verification status.
            </p>
            <p>
                PSU does not guarantee that a micro-credential will result in employment, promotion,
                academic credit, professional licensure, or any other particular outcome unless
                expressly stated by PSU or the applicable program.
            </p>

            <h2>7. Credential Sharing and Verification</h2>
            <p>
                Users may share their micro-credentials for legitimate educational, professional,
                employment, or other appropriate purposes.
            </p>
            <p>
                Where the platform provides a public verification feature, certain credential
                information may be accessible to individuals or organizations seeking to verify the
                credential.
            </p>
            <p>Users must not:</p>
            <ul>
                <li>Alter or falsify a credential;</li>
                <li>Create fraudulent credentials;</li>
                <li>Misrepresent an expired, suspended, revoked, or invalid credential as valid;</li>
                <li>Use another person's credential as their own; or</li>
                <li>Use PSU's name, logo, or credentialing materials in a misleading or unauthorized manner.</li>
            </ul>
            <p>
                PSU reserves the right to revoke or disable the verification of credentials that are
                determined to be fraudulent, improperly obtained, or otherwise invalid.
            </p>

            <h2>8. User Responsibilities and Acceptable Use</h2>
            <p>
                Users agree to use the Services responsibly and in accordance with applicable laws,
                university policies, and these Terms.
            </p>
            <p>Users must not:</p>
            <ul>
                <li>Use the platform for unlawful, fraudulent, or abusive activities;</li>
                <li>Attempt to gain unauthorized access to the platform or another user's account;</li>
                <li>Introduce malicious software, viruses, or other harmful code;</li>
                <li>Interfere with the operation or security of the platform;</li>
                <li>Copy, reproduce, modify, or distribute platform content without authorization;</li>
                <li>Harass, threaten, or impersonate other users or PSU personnel;</li>
                <li>Circumvent security, assessment, or verification mechanisms; or</li>
                <li>Use the Services in a manner that may damage the reputation, security, or operations of PSU.</li>
            </ul>

            <h2>9. Intellectual Property</h2>
            <p>
                Unless otherwise indicated, the UPSKILL: Micro-Credentials platform, including its
                software, design, branding, logos, text, graphics, instructional materials,
                assessments, digital badges, and other content, is owned by or licensed to Pangasinan
                State University and is protected by applicable intellectual property laws.
            </p>
            <p>
                Users are granted a limited, non-exclusive, non-transferable right to access and use
                the Services for their intended educational or professional purposes.
            </p>
            <p>
                Users may not reproduce, distribute, modify, sell, publish, or commercially exploit
                PSU-owned materials without prior written authorization.
            </p>
            <p>
                Users retain ownership of original works they independently create and submit,
                subject to any applicable course, program, university, funding, or intellectual
                property policies.
            </p>

            <h2>10. User-Submitted Content</h2>
            <p>
                When you submit assignments, evidence, projects, documents, or other materials
                through the Services (“User Content”), you represent that:
            </p>
            <ul>
                <li>You have the right to submit the material;</li>
                <li>The material does not unlawfully infringe the rights of another person or organization;</li>
                <li>The material is not knowingly fraudulent or misleading; and</li>
                <li>The submission complies with applicable PSU policies and program requirements.</li>
            </ul>
            <p>
                You grant PSU the limited rights necessary to receive, process, evaluate, store, and
                administer your User Content for purposes related to the micro-credential program,
                subject to the Privacy Policy and applicable law.
            </p>

            <h2>11. Privacy and Personal Information</h2>
            <p>
                Your use of the Services is also subject to the UPSKILL: Micro-Credentials
                <a href="{{ url('/privacy') }}" style="color:var(--navy);font-weight:700;text-decoration:underline;">Privacy Policy</a>.
            </p>
            <p>
                PSU may collect, process, store, and protect personal information in accordance with
                its Privacy Policy and applicable Philippine data protection laws and regulations.
            </p>
            <p>
                Users are encouraged to review the Privacy Policy to understand how their personal
                information is handled.
            </p>

            <h2>12. Third-Party Services and Links</h2>
            <p>
                The Services may contain links to or integrations with third-party websites,
                applications, platforms, or services.
            </p>
            <p>
                PSU does not necessarily control or endorse third-party services and is not
                responsible for their content, availability, security, or privacy practices.
            </p>
            <p>
                Your use of third-party services may be subject to separate terms and privacy
                policies established by those third parties.
            </p>

            <h2>13. Availability and Service Changes</h2>
            <p>
                PSU will make reasonable efforts to maintain the availability and functionality of
                the Services. However, the Services may occasionally be unavailable because of
                maintenance, upgrades, technical problems, security incidents, connectivity issues,
                or circumstances beyond PSU's reasonable control.
            </p>
            <p>
                PSU reserves the right to modify, suspend, or discontinue any part of the Services
                when necessary.
            </p>

            <h2>14. Suspension, Revocation, and Termination</h2>
            <p>
                PSU may suspend or terminate a user's access to the Services, or suspend or revoke a
                micro-credential, when there is reasonable basis to believe that the user:
            </p>
            <ul>
                <li>Violated these Terms;</li>
                <li>Violated applicable university policies;</li>
                <li>Engaged in academic dishonesty or fraudulent activity;</li>
                <li>Provided false or misleading information;</li>
                <li>Misused the platform or credential;</li>
                <li>Compromised the security of the Services; or</li>
                <li>Engaged in conduct that may adversely affect the integrity of the micro-credential program.</li>
            </ul>
            <p>
                Where appropriate, PSU will follow applicable institutional rules and procedures
                before taking disciplinary or credential-related action.
            </p>

            <h2>15. Disclaimer</h2>
            <p>
                The Services are provided for educational, professional development, credentialing,
                and related purposes.
            </p>
            <p>
                While PSU endeavors to maintain accurate and reliable information, it does not
                warrant that the Services will always be uninterrupted, error-free, completely
                secure, or free from technical defects.
            </p>
            <p>
                Micro-credentials represent specific demonstrated competencies or achievements and
                should not automatically be interpreted as equivalent to a degree, diploma,
                professional license, or formal academic qualification unless expressly recognized as
                such by an authorized institution or regulatory body.
            </p>

            <h2>16. Limitation of Liability</h2>
            <p>
                To the extent permitted by applicable law, PSU shall not be responsible for losses or
                damages arising from circumstances beyond its reasonable control, including technical
                interruptions, internet or telecommunications failures, unauthorized third-party
                actions, or temporary unavailability of the Services.
            </p>
            <p>
                Nothing in these Terms is intended to exclude or limit any liability that cannot
                lawfully be excluded or limited under applicable law.
            </p>

            <h2>17. Changes to These Terms</h2>
            <p>
                PSU may update these Terms and Conditions from time to time to reflect changes in the
                Services, institutional policies, technology, or applicable laws and regulations.
            </p>
            <p>
                When material changes are made, PSU may provide notice through the platform, website,
                email, or other appropriate means.
            </p>
            <p>
                The updated Terms will indicate the applicable effective date and last updated date.
            </p>
            <p>
                Your continued use of the Services after the updated Terms take effect constitutes
                your acceptance of the revised Terms, to the extent permitted by applicable law.
            </p>

            <h2>18. Governing Law</h2>
            <p>
                These Terms and Conditions shall be governed by and interpreted in accordance with
                the laws of the Republic of the Philippines, together with applicable rules,
                regulations, and institutional policies.
            </p>
            <p>
                Any dispute arising from or relating to the Services shall, where applicable, be
                addressed through the appropriate administrative, institutional, or legal procedures.
            </p>

            <h2>19. Severability</h2>
            <p>
                If any provision of these Terms is determined to be invalid, unlawful, or
                unenforceable, the remaining provisions shall continue to remain in full force and
                effect to the extent permitted by law.
            </p>

            <h2>20. Contact Information</h2>
            <p>
                For questions, concerns, or inquiries regarding these Terms and Conditions, please
                contact:
            </p>
            <div class="legal__contact">
                <p><strong>PANGASINAN STATE UNIVERSITY</strong></p>
                <p>UPSKILL: Micro-Credentials</p>
                <p>Data Protection Officer / Privacy Officer: Cristeta Tolentino</p>
                <p>Email: [Official Email Address]</p>
                <p>Telephone: [Telephone Number]</p>
                <p>Address: [Official Office Address]</p>
                <p>Website: [Official UPSKILL Website]</p>
            </div>

            <h2>21. Acknowledgment and Agreement</h2>
            <p>
                By registering for or using UPSKILL: Micro-Credentials, you acknowledge that you have
                read, understood, and agreed to these Terms and Conditions and the applicable Privacy
                Policy.
            </p>
            <p>
                If you do not agree to these Terms, please discontinue your use of the Services.
            </p>


        </div>
    </div>
</section>
@endsection
