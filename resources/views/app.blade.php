<!--
Copyright © 2025 Netisu All Rights Reserved.
Unauthorized use of this code (Not Mendcore) is strictly prohibited.
-->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ App::isLocale('ar') ? 'rtl' : 'ltr' }}" prefix="og: https://ogp.me/ns#">

<head>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link id="theme-style" rel="stylesheet" defer>

    @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <style>
        /* customizable snowflake styling */
        .snowflake {
            color: #fff;
            content: '&#11044;';
            font-size: 1em;
            font-family: Arial;
            text-shadow: 0 0 1px #000;
        }

        @-webkit-keyframes snowflakes-fall {
            0% {
                top: -10%;
            }

            100% {
                top: 100%;
            }
        }

        @-webkit-keyframes snowflakes-shake {
            0% {
                -webkit-transform: translateX(0px);
                transform: translateX(0px);
            }

            50% {
                -webkit-transform: translateX(80px);
                transform: translateX(80px);
            }

            100% {
                -webkit-transform: translateX(0px);
                transform: translateX(0px);
            }
        }

        @keyframes snowflakes-fall {
            0% {
                top: -10%;
            }

            100% {
                top: 100%;
            }
        }

        @keyframes snowflakes-shake {
            0% {
                transform: translateX(0px);
            }

            50% {
                transform: translateX(80px);
            }

            100% {
                transform: translateX(0px);
            }
        }

        .snowflake {
            position: fixed;
            top: -10%;
            z-index: 9999;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            cursor: default;
            -webkit-animation-name: snowflakes-fall, snowflakes-shake;
            -webkit-animation-duration: 10s, 3s;
            -webkit-animation-timing-function: linear, ease-in-out;
            -webkit-animation-iteration-count: infinite, infinite;
            -webkit-animation-play-state: running, running;
            animation-name: snowflakes-fall, snowflakes-shake;
            animation-duration: 10s, 3s;
            animation-timing-function: linear, ease-in-out;
            animation-iteration-count: infinite, infinite;
            animation-play-state: running, running;
        }

        .snowflake:nth-of-type(0) {
            left: 1%;
            -webkit-animation-delay: 0s, 0s;
            animation-delay: 0s, 0s;
        }

        .snowflake:nth-of-type(1) {
            left: 10%;
            -webkit-animation-delay: 1s, 1s;
            animation-delay: 1s, 1s;
        }

        .snowflake:nth-of-type(2) {
            left: 20%;
            -webkit-animation-delay: 6s, 0.5s;
            animation-delay: 6s, 0.5s;
        }

        .snowflake:nth-of-type(3) {
            left: 30%;
            -webkit-animation-delay: 4s, 2s;
            animation-delay: 4s, 2s;
        }

        .snowflake:nth-of-type(4) {
            left: 40%;
            -webkit-animation-delay: 2s, 2s;
            animation-delay: 2s, 2s;
        }

        .snowflake:nth-of-type(5) {
            left: 50%;
            -webkit-animation-delay: 8s, 3s;
            animation-delay: 8s, 3s;
        }

        .snowflake:nth-of-type(6) {
            left: 60%;
            -webkit-animation-delay: 6s, 2s;
            animation-delay: 6s, 2s;
        }

        .snowflake:nth-of-type(7) {
            left: 70%;
            -webkit-animation-delay: 2.5s, 1s;
            animation-delay: 2.5s, 1s;
        }

        .snowflake:nth-of-type(8) {
            left: 80%;
            -webkit-animation-delay: 1s, 0s;
            animation-delay: 1s, 0s;
        }

        .snowflake:nth-of-type(9) {
            left: 90%;
            -webkit-animation-delay: 3s, 1.5s;
            animation-delay: 3s, 1.5s;
        }
    </style>
    <style>
        #snowflakeContainer {
            position: absolute;
            left: 0px;
            top: 0px;
            display: none;
        }

        .snowflake {
            position: fixed;
            background-color: #CCC;
            user-select: none;
            z-index: 1000;
            pointer-events: none;
            border-radius: 50%;
            width: 10px;
            height: 10px;
        }
    </style>
</head>
<body style="margin:0;padding:0">
<div id="snowflakeContainer">
    <span class="snowflake"></span>
</div>
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
    if (typeof localStorage!== 'undefined') {
      localStorage.setItem('theme', theme);
    } else if (typeof sessionStorage!== 'undefined') {
      sessionStorage.setItem('theme', theme);
    } else {
      console.log('Web Storage is not supported in this environment.');
    }
  } catch (error) {
    console.error("Error saving theme to storage:", error);
  }
}


function initializeTheme() {
  let theme = 'light'; // Default theme

  // Use Promise to handle asynchronous nature of storage access if needed in your context.
  // If not needed, you can remove the Promise wrapper.
  Promise.resolve().then(() => {
    try {
      if (typeof localStorage!== 'undefined') {
        theme = localStorage.getItem('theme') || theme;
      } else if (typeof sessionStorage!== 'undefined') {
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
<script>
    // Array to store our Snowflake objects
    var snowflakes = [];

    // Global variables to store our browser's window size
    var browserWidth;
    var browserHeight;

    // Specify the number of snowflakes you want visible
    var numberOfSnowflakes = 50;

    // Flag to reset the position of the snowflakes
    var resetPosition = false;

    // Handle accessibility
    var enableAnimations = false;
    var reduceMotionQuery = matchMedia("(prefers-reduced-motion)");

    // Handle animation accessibility preferences
    function setAccessibilityState() {
        if (reduceMotionQuery.matches) {
            enableAnimations = false;
        } else {
            enableAnimations = true;
        }
    }
    setAccessibilityState();

    reduceMotionQuery.addListener(setAccessibilityState);

    //
    // It all starts here...
    //
    function setup() {
        if (enableAnimations) {
            window.addEventListener("DOMContentLoaded", generateSnowflakes, false);
            window.addEventListener("resize", setResetFlag, false);
        }
    }
    setup();

    //
    // Constructor for our Snowflake object
    //
    function Snowflake(element, speed, xPos, yPos) {
        // set initial snowflake properties
        this.element = element;
        this.speed = speed;
        this.xPos = xPos;
        this.yPos = yPos;
        this.scale = 1;

        // declare variables used for snowflake's motion
        this.counter = 0;
        this.sign = Math.random() < 0.5 ? 1 : -1;

        // setting an initial opacity and size for our snowflake
        this.element.style.opacity = (.1 + Math.random()) / 3;
    }

    //
    // The function responsible for actually moving our snowflake
    //
    Snowflake.prototype.update = function() {
        // using some trigonometry to determine our x and y position
        this.counter += this.speed / 5000;
        this.xPos += this.sign * this.speed * Math.cos(this.counter) / 40;
        this.yPos += Math.sin(this.counter) / 40 + this.speed / 30;
        this.scale = .5 + Math.abs(10 * Math.cos(this.counter) / 20);

        // setting our snowflake's position
        setTransform(Math.round(this.xPos), Math.round(this.yPos), this.scale, this.element);

        // if snowflake goes below the browser window, move it back to the top
        if (this.yPos > browserHeight) {
            this.yPos = -50;
        }
    }

    //
    // A performant way to set your snowflake's position and size
    //
    function setTransform(xPos, yPos, scale, el) {
        el.style.transform = `translate3d(${xPos}px, ${yPos}px, 0) scale(${scale}, ${scale})`;
    }

    //
    // The function responsible for creating the snowflake
    //
    function generateSnowflakes() {

        // get our snowflake element from the DOM and store it
        var originalSnowflake = document.querySelector(".snowflake");

        // access our snowflake element's parent container
        var snowflakeContainer = originalSnowflake.parentNode;
        snowflakeContainer.style.display = "block";

        // get our browser's size
        browserWidth = document.documentElement.clientWidth;
        browserHeight = document.documentElement.clientHeight;

        // create each individual snowflake
        for (var i = 0; i < numberOfSnowflakes; i++) {

            // clone our original snowflake and add it to snowflakeContainer
            var snowflakeClone = originalSnowflake.cloneNode(true);
            snowflakeContainer.appendChild(snowflakeClone);

            // set our snowflake's initial position and related properties
            var initialXPos = getPosition(50, browserWidth);
            var initialYPos = getPosition(50, browserHeight);
            var speed = 5 + Math.random() * 40;

            // create our Snowflake object
            var snowflakeObject = new Snowflake(snowflakeClone,
                speed,
                initialXPos,
                initialYPos);
            snowflakes.push(snowflakeObject);
        }

        // remove the original snowflake because we no longer need it visible
        snowflakeContainer.removeChild(originalSnowflake);

        moveSnowflakes();
    }

    //
    // Responsible for moving each snowflake by calling its update function
    //
    function moveSnowflakes() {

        if (enableAnimations) {
            for (var i = 0; i < snowflakes.length; i++) {
                var snowflake = snowflakes[i];
                snowflake.update();
            }
        }

        // Reset the position of all the snowflakes to a new value
        if (resetPosition) {
            browserWidth = document.documentElement.clientWidth;
            browserHeight = document.documentElement.clientHeight;

            for (var i = 0; i < snowflakes.length; i++) {
                var snowflake = snowflakes[i];

                snowflake.xPos = getPosition(50, browserWidth);
                snowflake.yPos = getPosition(50, browserHeight);
            }

            resetPosition = false;
        }

        requestAnimationFrame(moveSnowflakes);
    }

    //
    // This function returns a number between (maximum - offset) and (maximum + offset)
    //
    function getPosition(offset, size) {
        return Math.round(-1 * offset + Math.random() * (size + 2 * offset));
    }

    //
    // Trigger a reset of all the snowflakes' positions
    //
    function setResetFlag(e) {
        resetPosition = true;
    }
</script>
</html>
