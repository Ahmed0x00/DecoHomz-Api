<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = null;

        // 1. Query parameter 'lang' or 'locale' overrides
        if ($request->has('lang') && in_array($request->query('lang'), ['ar', 'en'])) {
            $lang = $request->query('lang');
            cookie()->queue('locale', $lang, 60 * 24 * 365);
            cookie()->queue('lang', $lang, 60 * 24 * 365);
        } elseif ($request->has('locale') && in_array($request->query('locale'), ['ar', 'en'])) {
            $lang = $request->query('locale');
            cookie()->queue('locale', $lang, 60 * 24 * 365);
            cookie()->queue('lang', $lang, 60 * 24 * 365);
        }

        // If not specified in query, read from cookies
        if (!$lang) {
            // 2. Cookie 'locale'
            if ($request->cookie('locale')) {
                $lang = $request->cookie('locale');
            } 
            // 3. Cookie 'lang' fallback
            elseif ($request->cookie('lang')) {
                $lang = $request->cookie('lang');
            }
            // 4. Parse raw Cookie header if the cookies bag is not populated (e.g. in some API setups/test requests)
            elseif ($request->hasHeader('Cookie')) {
                $cookieHeader = $request->header('Cookie');
                if (preg_match('/locale=([^;]+)/', $cookieHeader, $matches)) {
                    $lang = trim($matches[1]);
                } elseif (preg_match('/lang=([^;]+)/', $cookieHeader, $matches)) {
                    $lang = trim($matches[1]);
                }
            }
        }

        if (in_array($lang, ['ar', 'en'])) {
            app()->setLocale($lang);
        } else {
            app()->setLocale(config('app.locale', 'en'));
        }

        return $next($request);
    }
}
