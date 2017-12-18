import axios from 'axios';
import debounce from 'lodash.debounce';
import Dropdown from './dropdown';

class NZPostAutocomplete {

  constructor(el, config) {
    const defaultConfig = {
      source: null,
      maxResults: 10,
      charactersRequired: 5,
      ajaxDelay: 250,
      debug: false,
    };

    this.config = Object.assign({}, defaultConfig, config);

    try {
      this.setInput(el);
    } catch (err) {
      window.console.error(err);
      return;
    }

    if (this.config.debug) console.debug('Initialise dropdown menu');
    this.dropdown = new Dropdown(this.input, this.config);

    this.search = debounce(this.search.bind(this), this.config.ajaxDelay);
    this.bindInputEvents();

    this.onClickOutside = this.onClickOutside.bind(this);

    this.handleEvent = (e) => {
      switch (e.type) {
        case 'focus':
          this.onFocus();
          break;
        case 'input':
          this.search(this.input.value);
          break;
        case 'keydown':
          this.onKeyDown(e);
          break;
        default:
          break;
      }
    };

    this.lastSearch = null;
    this.selected = undefined;
  }

  onKeyDown(e) {
    if (this.dropdown.isVisible) this.dropdown.keyDownEvent(e);
    if (e.keyCode === 9 || e.keyCode === 13) { // tab or enter
      this.finishTyping();
      this.focusToNextControl();
    }
  }

  onFocus() {
    if (this.config.debug) console.debug('Focus input');
    document.body.addEventListener('click', this.onClickOutside);
    document.addEventListener('keydown', this);
  }

  focusToNextControl() {
    if (this.config.debug) console.debug('Tab to next input');
    this.input.blur();
  }

  onClickOutside(e) {
    if (e.target === this.input) return;
    if (this.config.debug) console.debug('Close dropdown');
    this.finishTyping();
  }

  finishTyping() {
    this.dropdown.remove();
    document.body.removeEventListener('click', this.onClickOutside);
    document.removeEventListener('keydown', this);
    this.input.removeEventListener('click', this);
  }

  setInput(el) {
    if (this.config.debug) console.debug(`Assign to ${el}`);
    if (typeof el === 'string') this.input = document.querySelector(el);
    else if (el.tagName && el.tagName.toLowerCase() === 'input') this.input = el;
    else throw new Error('1 arguement should be String or instance of Element with tagName input');
  }

  bindInputEvents() {
    this.input.addEventListener('input', this, false);
    this.input.addEventListener('focus', this, false);
  }

  search(value) {
    if (this.config.debug) console.debug(`Searching for ${value}`);
    const that = this;
    this.getResults(value)
      .then((response) => {
        that.dropdown.showResults(response.data.addresses);
      })
      .catch((err) => {
        console.error(err);
      });
  }

  getResults(value) {
    return axios.get(this.config.source, {
      params: {
        q: value,
      },
    });
  }
}

export default NZPostAutocomplete;
