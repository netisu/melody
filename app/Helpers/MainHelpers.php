<?php

use App\Models\Item;
use App\Models\CrateItem;
use App\Models\SiteSettings;
use Illuminate\Support\Facades\Cache;
function translations($json)
{
    if (!file_exists($json)) {
        return [];
    }

    return json_decode(file_get_contents($json), true);
}

function getIp(): string|null {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
    return request()->ip(); // it will return the server IP if the client IP is not found using this method.
}

function truncate($text, $length): mixed
{
    if ($length >= \strlen(string: $text)) {
      return $text;
    }

  return preg_replace(
        pattern: "/^(.{1,$length})(\s.*|$)/s",
        replacement: '\\1...',
        subject: $text
    );
}

function crateRarity($data, $rarity)
{
    switch ($data) {
        case 'rank':
            return CrateItem::rarityRank($rarity);
        case 'name':
            return CrateItem::rarityName($rarity);
    }
}

function pluralization($type, $plural = false): mixed
{
    // Get item types configuration (consider error handling if not defined)
    $types = config(key: 'PermittedItemTypes');

    // Validate type and plural flag (optional)
    if (!is_string(value: $type) || !is_bool(value: $plural)) {
        throw new InvalidArgumentException(message: 'Invalid arguments for itemType');
    }

    $type = array_key_exists(key: $type, array: $types) ? $types[$type][$plural ? 1 : 0] : ucfirst(string: $type);

    // Handle missing plural form (optional)
    if (!$plural && !isset($types[$type][1])) {
        $type .= 's'; // Generic pluralization
    }

    return $type;
}


function customRaritySort($a, $b)
{
    $key = 'rarity';

    if ($a[$key] < $b[$key])
        return 1;
    else if ($a[$key] > $b[$key])
        return -1;

    return 0;
}

function siteSetting($key): mixed
{
    // Check if the app is in production environment
    $isProduction = app()->environment('production');

    if ($isProduction) {
        // Check if the settings are cached
        $settings = Cache::remember('siteSettings', now()->addMinutes(value: 30), callback: function () {
            return SiteSettings::find(id: 1); // Change to use the find() method instead of where() + first()
        });
    } else {
        // If not in production, retrieve settings without caching
        $settings = SiteSettings::find(id: 1);
    }

    // If settings are not found, return a default value or handle the error
    if (!$settings) {
        return null; // or return a default value or throw an exception
    }

    // Use optional chaining to access the property safely
    return optional(value: $settings)->$key;
}

function getItemHash($itemID): ?string
{
    $item = Item::where(column: 'id', operator: '=', value: $itemID)->firstOrFail();

    return $item->hash;
}

function remote_file_exists($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // handles 301/2 redirects
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        return true;
    } else {
        return false;
    }
}

function shortNum($num) {
    if ($num < 1000) {
        return $num;
    }

    $suffixes = ['', 'K', 'M', 'B', 'T']; // standard money suffexes
    $exponent = floor(log10($num) / 3);
    $divisor = pow(10, $exponent * 3);
    $shortened = $num / $divisor;

    // Format to one decimal place.
    if ($shortened < 10) {
        $formatted = number_format($shortened, 1);
    } else {
        $formatted = number_format($shortened, 0); // no decimals
    }


    return $formatted . $suffixes[$exponent];
}
