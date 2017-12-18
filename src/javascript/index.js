import NZPostAutocomplete from './autocomplete';

// Initialise the class
function init(el) {
  const url = el.getAttribute('data-suggest');

  const options = {
    source: url,
    debug: false,
  };
  return new NZPostAutocomplete(el, options);
}

// Get all of our address finder elements
const input = document.querySelectorAll('input.addressfinder');
for (const el of input) {
  init(el);
}
