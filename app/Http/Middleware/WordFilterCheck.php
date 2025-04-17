<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WordFilterCheck
{
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $route = $request->route()->getName();
    $url = str_replace("{$request->root()}/api", '', $request->url());
    $enabled = (Auth::check() && Auth::user()->isStaff()) ? false : true;

    if ($enabled && $request->isMethod('post') && 
        (array_key_exists($url, config('filter.api_urls')) || 
         array_key_exists($route, config('filter.routes')))) {

      $api = $request->is('api/*');
      $params = (!$api) ? config('filter.routes')[$route] : config('filter.api_urls')[$url];

      $blacklist = config('filter.blacklist');

      foreach ($params as $param) {
        if ($this->isProfanity($request->$param, $blacklist)) {
          if (!$api) {
            return back()
              ->withErrors(['One or more words have triggered our profanity filter. Please update and try again.']);
          } else {
            return response()->json([
              'error' => 'One or more words have triggered our profanity filter. Please update and try again.',
            ]);
          }
        }
      }
    }

    return $next($request);
  }

  /**
   * Check for profanity in content.
   *
   * @param string $content
   * @param array $blacklist
   * @return bool
   */
  public function isProfanity($content, $blacklist)
  {
    $tempContent = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $content));
    $tempContent = preg_replace('/\s+/', ' ', $tempContent);
    $wordsRegex = '/(' . implode('|', $blacklist) . ')/i';

    $violatingWords = [];
    $flag = false;
    $domainOk = true;

    foreach ($blacklist as $blword) {
      if (stripos($tempContent, $blword) !== false) {
        $flag = true;
        $violatingWords[] = $blword;
      }
    }

    $domainContent = str_replace(['https://', 'http://'], '', $content);
    $domainContent = preg_replace('/[^a-z0-9.-]/', '', $domainContent);
    $whitelistCheck = false;

    $whitelistDomains = config('filter.whitelist_domains');

    foreach ($whitelistDomains as $whitelistDomain) {
      if (substr_compare($domainContent, $whitelistDomain, -strlen($whitelistDomain)) === 0) {
        $whitelistCheck = true;
        break;
      }
    }

    $domainOk = $whitelistCheck;

    if (!$domainOk) {
      $flag = true;
      $violatingWords[] = $domainContent; // Flag suspicious domains
    }

    if ($flag) {
      Log::warning("Profanity detected in request: " . implode(', ', $violatingWords)); // Log for later review
    }

    return $flag;
  }
}
