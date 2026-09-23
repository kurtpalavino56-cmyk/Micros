<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PreventBackHistory — stops the browser from re-displaying a signed-in page
 * after the session has ended.
 *
 * Without this, pressing Back after logging out (or after the session times
 * out) shows the dashboard again: the browser is not asking the server for
 * anything, it is redrawing a page it already had in its cache. The session
 * really is gone — any link or form on that stale page fails — but a
 * bystander can still read whatever was on screen.
 *
 * The headers below tell the browser it may not store the page at all, so
 * Back has to re-request it, which then hits the auth check and redirects to
 * the login screen.
 *
 *   no-store          do not write this response to disk or memory
 *   no-cache          always revalidate with the server before reusing
 *   must-revalidate   never serve it stale
 *   max-age=0         it is stale immediately
 *   Pragma / Expires  the same instruction for older browsers and proxies
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0'
        );
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}
