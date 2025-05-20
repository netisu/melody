<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ App::isLocale('he') ? 'rtl' : 'ltr' }}"
    prefix="og: https://ogp.me/ns#">

<head>
    <!--
    Copyright © 2025 Netisu All Rights Reserved.
    Unauthorized use of this code (Not Mendcore) is strictly prohibited.
    -->
    <title inertia>{{ config('Values.name') }}</title>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="theme-color" content="#03a9f4" />
    <meta http-equiv="Content-Type" content="application/x-html+html; charset=UTF-8" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="msapplication-TileColor" content="#03a9f4" />
    <meta name="robots" content="index,follow" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ config('Values.icon') }}" />
    <meta name="msapplication-config" content="{{ asset('browserconfig.ae3u4gn3.xml') }}" />
    <link rel="manifest" href="{{ asset('Aeow2mf3.webmanifest') }}" crossorigin="use-credentials" />
    <meta name="google-adsense-account" content="ca-pub-2113156566128044">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://kit-pro.fontawesome.com/releases/v6.1.2/css/pro.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link id="theme-style" rel="stylesheet" defer>
    @routes()
    @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body style="margin:0;padding:0">
    <noscript>
        <strong>We're sorry but {{ config(key: 'Values.name') }} doesn't work properly without JavaScript enabled.
            Please enable it to continue.</strong>
    </noscript>
    @inertia
</body>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.slim.min.js"></script>
<script type="text/javascript" src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
<script>
    function applyTheme(theme) {
        const lowercaseTheme = theme.toLowerCase();

        let style = document.getElementById("theme-style");

        if (!style) {
            style = document.createElement("link");
            style.id = "theme-style";
            style.rel = "stylesheet";
            document.head.appendChild(style);
        }

        if (style) {
            style.href = `/assets/css/themes/variables-${lowercaseTheme}.css`;
        } else {
            console.log("Theme Error");
            return; // Early exit if style is still not found
        }

        // Save the selected theme in Storage
        try {
            if (typeof localStorage !== 'undefined') {
                localStorage.setItem('theme', theme);
            } else if (typeof sessionStorage !== 'undefined') {
                sessionStorage.setItem('theme', theme);
            } else {
                console.log('Web Storage is not supported in this environment.');
            }
        } catch (error) {
            console.error("Error saving theme to storage:", error);
        }
    }


    function initializeTheme() {
        let theme = 'dark'; // Default theme

        // Use Promise to handle asynchronous nature of storage access if needed in your context.
        // If not needed, you can remove the Promise wrapper.
        Promise.resolve().then(() => {
            try {
                if (typeof localStorage !== 'undefined') {
                    theme = localStorage.getItem('theme') || theme;
                } else if (typeof sessionStorage !== 'undefined') {
                    theme = sessionStorage.getItem('theme') || theme;
                } else {
                    console.log('Web Storage is not supported in this environment.');
                }
            } catch (error) {
                console.error("Error accessing storage:", error);
            }
            return theme; // Return the theme value for the next.then
        }).then(theme => {
            // DOMContentLoaded ensures the DOM is fully parsed before applying the theme.
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    applyTheme(theme);
                });
            } else {
                applyTheme(theme); // Apply immediately if DOM is already ready.
            }
        });
    }

    // Call initializeTheme
    initializeTheme();
</script>

</html>
