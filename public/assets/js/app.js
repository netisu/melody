window.onload = function(){ 

document.addEventListener('DOMContentLoaded', function() {
/**
 * MAIN
 */

document.querySelectorAll('[data-tooltip-title]').forEach(function (element) {
  tippy(element, {
    content: element.getAttribute('data-tooltip-title'),
    placement: element.getAttribute('data-tooltip-placement') || 'top',
    allowHTML: element.hasAttribute('data-tooltip-html'),
    arrow: false,
  });
});

document.querySelectorAll('[data-toggle-element]').forEach(function (element) {
  const target = document.querySelector(element.getAttribute('data-toggle-element'));

  if (!target) return;

  element.onclick = function () {
    target.classList.toggle('qbly1');
  };
});

document.getElementById('sidebar-toggler').onclick = function () {
  document.querySelector('.sidebar').classList.toggle('f14yq');
};

document.getElementById('global-search-bar').onkeyup = function () {
  const classList = document.getElementById('global-search-results').classList;

  if (this.value !== '') {
    classList.remove('qbly1');
  } else {
    classList.add('qbly1');
  }
};

document.querySelectorAll('[data-toggle-modal]').forEach(function (element) {
  const targetId = element.getAttribute('data-toggle-modal');
  const target = document.querySelector(targetId);

  if (!target) return;

  element.onclick = function () {
    const omei8 = target.classList.contains('omei8');
    const className = omei8 ? 'dyntu' : 't606a';

    document.body.style.overflowY = omei8 ? '' : 'hidden';
    document.body.classList.add(className);

    if (active) {
      target.onclick = null;
    } else {
      target.classList.add('omei8');
      target.onclick = function (event) {
        if (targetId === `#${event.target.id}`) {
          element.click();
        }
      };
    }

    setTimeout(function () {
      if (active) {
        target.classList.remove('omei8');
      }

      document.body.classList.remove(className);
    }, omei8 ? 200 : 400);
  };
});

document.querySelectorAll('.dropdown').forEach(function (element) {
  const dropdownButton = element.querySelector('.dropdown-button');
  const dropdownMenu = element.querySelector('.dropdown-menu');

  document.addEventListener('click', function (event) {
    const isDropdownClicked = dropdownButton.contains(event.target);
    const isMenuClicked = dropdownMenu.contains(event.target);

    if (!isDropdownClicked && !isMenuClicked) {
      element.classList.remove('omei8');
    }
  });

  dropdownButton.addEventListener('click', function () {
    element.classList.toggle('omei8');
  });
});

document.querySelectorAll('.collapsible').forEach(function (element) {
  element.addEventListener('click', function (event) {
    if (!event.target.closest('.collapsible-menu')) {
      element.classList.toggle('omei8');
    }
  });
});
});
};