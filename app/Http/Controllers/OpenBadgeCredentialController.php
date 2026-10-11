<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\UserBadge;
use App\Services\OpenBadgeCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class OpenBadgeCredentialController extends Controller
{
    public function download(string $credentialUid, OpenBadgeCredentialService $credentials): Response
    {
        $userBadge = UserBadge::query()
            ->where('credential_uid', strtolower($credentialUid))
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 'revoked');
            })
            ->firstOrFail();

        try {
            $signedCredential = $credentials->compactCredential($userBadge);
        } catch (RuntimeException $exception) {
            abort(503, $exception->getMessage());
        }

        return response($signedCredential, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$userBadge->credential_uid.'.jwt"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function show(string $credentialUid): Response
    {
        $userBadge = UserBadge::query()
            ->where('credential_uid', strtolower($credentialUid))
            ->firstOrFail();

        if ($userBadge->status === 'revoked') {
            abort(410, 'This credential has been revoked.');
        }

        if (! $userBadge->credential_jws) {
            abort(404);
        }

        return response($userBadge->credential_jws, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public function issuer(OpenBadgeCredentialService $credentials): JsonResponse
    {
        return response()->json($credentials->issuerProfile(), 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function jwks(OpenBadgeCredentialService $credentials): JsonResponse
    {
        try {
            return response()->json($credentials->jwks(), 200, [], JSON_UNESCAPED_SLASHES);
        } catch (RuntimeException $exception) {
            abort(503, $exception->getMessage());
        }
    }

    public function key(string $keyId, OpenBadgeCredentialService $credentials): JsonResponse
    {
        try {
            $jwk = $credentials->publicJwk();
        } catch (RuntimeException $exception) {
            abort(503, $exception->getMessage());
        }

        abort_unless(hash_equals($jwk['kid'], $keyId), 404);

        return response()->json($jwk, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function achievement(int $badgeId, OpenBadgeCredentialService $credentials): JsonResponse
    {
        $badge = Badge::query()->findOrFail($badgeId);

        return response()->json($credentials->achievement($badge), 200, [], JSON_UNESCAPED_SLASHES);
    }
}
