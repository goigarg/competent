<?php

namespace App\Http\Middleware;

use App\Enums\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the citizen's chosen language (stored in the session) to every web request,
 * including Livewire update requests, so __() and the views use it.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        App::setLocale(Language::fromLocale(is_string($locale) ? $locale : null)->value);

        return $next($request);
    }
}
