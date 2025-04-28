<?php

namespace App\Http;

use Exception;
use Inertia\Ssr\Gateway;
use Inertia\Ssr\Response;
use Inertia\Ssr\BundleDetector;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InertiaHttpGateway implements Gateway
{
    /**
     * Dispatch the Inertia page to the Server Side Rendering engine.
     */
    public function dispatch(array $page): ?Response
    {
        if (!config('inertia.ssr.enabled', true) || !(new BundleDetector())->detect()) {
            return null;
        }

        $url = str_replace('/render', '', config('inertia.ssr.url', 'http://127.0.0.1:13714')) . '/render';

        try {
            $cacheKey = md5(serialize($page));
            $response = Cache::remember($cacheKey, 60, fn () => Http::post($url, $page)->throw()->json());
        } catch (Exception $e) {
            return null;
        }

        if (is_null($response)) {
            return null;
        }

        return new Response(
            implode("\n", $response['head']),
            $response['body']
        );
    }
}
