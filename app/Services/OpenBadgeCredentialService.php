<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Support\Str;
use RuntimeException;

class OpenBadgeCredentialService
{
    public function compactCredential(UserBadge $userBadge): string
    {
        $userBadge->loadMissing(['user', 'badge']);

        if ($userBadge->credential_jws) {
            return $userBadge->credential_jws;
        }

        if (! $userBadge->credential_uid || ! $userBadge->user || ! $userBadge->badge || ! $userBadge->user->email) {
            throw new RuntimeException('This badge is missing information required for a portable credential.');
        }

        $privateKey = $this->privateKey();
        $publicDetails = openssl_pkey_get_details($privateKey);

        if (! is_array($publicDetails) || ! isset($publicDetails['rsa']['n'], $publicDetails['rsa']['e'])) {
            throw new RuntimeException('The Open Badges signing key is not a valid RSA key.');
        }

        $jwk = [
            'kty' => 'RSA',
            'n' => $this->base64Url($publicDetails['rsa']['n']),
            'e' => $this->base64Url($publicDetails['rsa']['e']),
        ];
        $keyId = hash('sha256', json_encode($jwk, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        $issuerId = route('openbadges.issuer');
        $credentialId = route('openbadges.credentials.show', $userBadge->credential_uid);
        $achievementId = route('openbadges.achievements.show', $userBadge->badge_id);
        $salt = strtolower($userBadge->credential_uid);
        $email = mb_strtolower(trim($userBadge->user->email));
        $emailHash = hash('sha256', $email.$salt);
        $subjectId = 'urn:sha256:'.hash('sha256', $salt.$email);
        $badge = $userBadge->badge;
        $institution = (string) config('open_badges.issuer_name');
        $issuedAt = ($userBadge->earned_at ?? $userBadge->created_at ?? now())->utc();

        $credential = [
            '@context' => [
                'https://www.w3.org/ns/credentials/v2',
                'https://purl.imsglobal.org/spec/ob/v3p0/context-3.0.3.json',
                'https://purl.imsglobal.org/spec/ob/v3p0/extensions.json',
            ],
            'id' => $credentialId,
            'type' => ['VerifiableCredential', 'OpenBadgeCredential'],
            'issuer' => [
                'id' => $issuerId,
                'type' => ['Profile'],
                'name' => $institution,
                'url' => config('app.url'),
            ],
            'validFrom' => $issuedAt->toIso8601ZuluString(),
            'name' => $badge->name,
            'credentialSubject' => [
                'id' => $subjectId,
                'type' => ['AchievementSubject'],
                'identifier' => [[
                    'type' => 'IdentityObject',
                    'hashed' => true,
                    'identityHash' => 'sha256$'.$emailHash,
                    'identityType' => 'emailAddress',
                    'salt' => $salt,
                ]],
                'achievement' => $this->achievement($badge, $achievementId),
            ],
            'credentialSchema' => [[
                'id' => 'https://purl.imsglobal.org/spec/ob/v3p0/schema/json/ob_v3p0_achievementcredential_schema.json',
                'type' => '1EdTechJsonSchemaValidator2019',
            ]],
            'iss' => $issuerId,
            'jti' => $credentialId,
            'nbf' => $issuedAt->timestamp,
            'sub' => $subjectId,
        ];

        $header = ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => route('openbadges.keys.show', $keyId)];
        $signingInput = $this->base64Url(json_encode($header, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)).'.'
            .$this->base64Url(json_encode($credential, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        if (! openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('The Open Badges credential could not be signed.');
        }

        $compactCredential = $signingInput.'.'.$this->base64Url($signature);
        $userBadge->forceFill(['credential_jws' => $compactCredential])->save();

        return $compactCredential;
    }

    /** @return array{keys: array<int, array<string, string>>} */
    public function jwks(): array
    {
        return ['keys' => [$this->publicJwk()]];
    }

    /** @return array<string, string> */
    public function publicJwk(): array
    {
        $details = openssl_pkey_get_details($this->privateKey());

        if (! is_array($details) || ! isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException('The Open Badges public key is not a valid RSA key.');
        }

        $jwk = [
            'kty' => 'RSA',
            'n' => $this->base64Url($details['rsa']['n']),
            'e' => $this->base64Url($details['rsa']['e']),
        ];
        $jwk['kid'] = hash('sha256', json_encode($jwk, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $jwk['alg'] = 'RS256';
        $jwk['use'] = 'sig';

        return $jwk;
    }

    /** @return array<string, mixed> */
    public function issuerProfile(): array
    {
        return [
            'id' => route('openbadges.issuer'),
            'type' => ['Profile'],
            'name' => (string) config('open_badges.issuer_name'),
            'url' => (string) config('app.url'),
        ];
    }

    /** @return array<string, mixed> */
    public function achievement(Badge $badge, ?string $id = null): array
    {
        $data = [
            'id' => $id ?: route('openbadges.achievements.show', $badge->id),
            'type' => ['Achievement'],
            'name' => $badge->name,
            'description' => trim(strip_tags((string) $badge->description)) ?: $badge->name,
            'criteria' => [
                'narrative' => 'Awarded after the learner completes the course requirements and institutional verification recorded by '.((string) config('open_badges.issuer_name')).'.',
            ],
            'creator' => [
                'id' => route('openbadges.issuer'),
                'type' => ['Profile'],
                'name' => $badge->issuing_institution ?: (string) config('open_badges.issuer_name'),
            ],
        ];

        if ($badge->icon_url && Str::startsWith($badge->icon_url, ['https://', 'http://', 'data:image/'])) {
            $data['image'] = ['id' => $badge->icon_url, 'type' => 'Image'];
        }

        if ($badge->badge_level) {
            $data['tag'][] = $badge->badge_level;
        }

        if ($badge->pqf_level) {
            $data['tag'][] = 'PQF Level '.$badge->pqf_level;
        }

        return $data;
    }

    private function privateKey(): mixed
    {
        $privateKeyPath = config('open_badges.private_key_path');

        if (! is_string($privateKeyPath) || ! is_file($privateKeyPath)) {
            throw new RuntimeException('Open Badges signing keys have not been generated. Run php artisan openbadges:generate-key.');
        }

        $privateKey = openssl_pkey_get_private('file://'.$privateKeyPath);

        if (! $privateKey) {
            throw new RuntimeException('The Open Badges private key could not be loaded.');
        }

        return $privateKey;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
