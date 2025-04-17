document.addEventListener('DOMContentLoaded', function() {
// theme-switcher.js
function applyTheme(theme) {
    let style = document.getElementById('theme-style');

    if (!style) {
      style = document.createElement('link');
      style.id = 'theme-style';
      style.rel = 'stylesheet';
      document.head.appendChild(style);
    }

    style.href = `/assets/css/themes/variables-${theme}.css`;

    // Save the selected theme in localStorage
    localStorage.setItem('theme', theme);
  }


  function setTheme(theme) {
    applyTheme(theme);
  }

  document.addEventListener('DOMContentLoaded', function () {
    const theme = localStorage.getItem('theme') || 's08nj';
    applyTheme(theme);

    const lightThemeBtn = document.getElementById('light-theme-btn');
    const darkThemeBtn = document.getElementById('dark-theme-btn');

    if (lightThemeBtn && darkThemeBtn) {
      lightThemeBtn.addEventListener('click', function () {
        setTheme('s08nj');
      });

      darkThemeBtn.addEventListener('click', function () {
        setTheme('t4ri1');
      });
    }
  });
});
