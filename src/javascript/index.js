import NZPostAutocomplete from './autocomplete';

// Initialise the class
const input = document.querySelectorAll('input.addressfinder')[0];
const url = input.getAttribute('data-suggest');

const options = {
  source: url,
  debug: false,
};

// eslint-disable-next-line no-unused-vars
const autocomplete = new NZPostAutocomplete('input.addressfinder', options);
