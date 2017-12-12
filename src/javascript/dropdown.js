import axios from 'axios';

const keynumCodes = {
  UP: 38,
  DOWN: 40,
  ENTER: 13,
  ESC: 27,
};

class Dropdown {
  constructor(el, config = null) {
    const defaultConfig = {
      debug: false,
      noResultsMessage: 'Sorry there were no results for that query',
    };

    this.config = Object.assign({}, defaultConfig, config);

    this.baseElement = el;
    this.isCreated = false;
    this.isVisible = false;
    this.dropdown = null;
    this.source = null;
    this.fields = null;
    this.activeElement = null;
    this.isLoaderShown = false;

    this.createElement();
    this.setupFieldMap();
  }

  createElement() {
    // Create our wrapper element
    const wrapper = document.createElement('div');
    wrapper.className = 'autocomplete-wrapper';

    // Wrap this around the input
    this.baseElement.parentNode.insertBefore(wrapper, this.baseElement);
    wrapper.appendChild(this.baseElement);

    // Create our dropdown element we will populate
    const dropdown = document.createElement('div');
    dropdown.className = 'autocomplete-dropdown';
    dropdown.style.visibility = 'hidden';
    dropdown.addEventListener('click', (e) => e.stopPropagation());

    wrapper.append(dropdown);

    this.dropdown = dropdown;
    this.isCreated = true;
  }

  setupFieldMap() {
    const fieldMap = this.baseElement.getAttribute('data-fields');
    // Check our fieldMap exists otherwise return
    if (typeof fieldMap !== 'string') return;
    const parsedFieldMap = JSON.parse(fieldMap.replace(/&quot;/g, '"'));
    if (this.config.debug) console.debug('Fetching field map', parsedFieldMap);

    this.fields = parsedFieldMap;
  }

  /**
   * Request details from NZ Post API
   * @param Array value
   * @return XMLHttpRequest
   */
  getResults(value) {
    return axios.get(this.source, {
      params: {
        q: value,
      },
    });
  }

  /**
   * Hide our dropdown and fetch results
   * @param Object item List item data
   */
  fetchDetails(item) {
    if (this.config.debug) console.debug('Fetch details for', item);
    this.hide();
    const dpid = item.DPID;
    this.source = this.baseElement.getAttribute('data-details');
    this.makeRequest(dpid);
  }

  makeRequest(value) {
    const that = this;
    if (this.config.debug) console.debug(`Getting results for NZ Post DPID: ${value}`);

    this.getResults(value)
      .then((response) => {
        that.fillDetails(response.data.details[0]);
      })
      .catch((err) => {
        console.error(err);
      });
  }

  fillDetails(details) {
    if (this.config.debug) console.debug(details);
    // Map results to corresponding fields
    this.mapResults(details);
    // Clear our autocomplete field value as we no longer need this.
    this.clear();
    // Set our default address field to populate our field with
    let fullAddress = '';
    if (details.AddressLine1) fullAddress += `${details.AddressLine1}`;
    if (details.AddressLine2) fullAddress += `, ${details.AddressLine2}`;
    if (details.AddressLine3) fullAddress += `, ${details.AddressLine3}`;
    if (details.AddressLine4) fullAddress += `, ${details.AddressLine4}`;
    if (details.AddressLine5) fullAddress += `, ${details.AddressLine5}`;
    this.baseElement.value = fullAddress;
  }

  mapResults(details) {
    // Map values to fields;
    if (this.fields) {
      Object.keys(this.fields).forEach((field) => {
        let fieldToSet = document.getElementById(field);
        if (this.config.debug) console.debug('Found form field', fieldToSet);

        if (fieldToSet.tagName.toLowerCase() === 'div') {
          fieldToSet = document.getElementsByName(field)[0];
        }

        const valueToMatch = this.fields[field];
        if (this.config.debug) console.debug(`Match NZ Address field for ${valueToMatch}`);

        const value = details[valueToMatch];
        if (this.config.debug) console.debug(`Value matched for ${valueToMatch}: ${value}`);
        if (this.config.debug) console.debug(`Filling field ${fieldToSet} with ${value}`);
        fieldToSet.value = value;
      });
    }
  }

  /* Handle our click event for each item */
  clickEvent(e) {
    this.fetchDetails(e.target.listRow);
  }

  /* Create each list item */
  createRow(item) {
    const li = document.createElement('li');
    li.innerHTML = item.FullAddress;
    li.id = item.DPID;
    li.addEventListener('click', this.clickEvent.bind(this));
    li.listRow = item;

    return li;
  }

  /* Create our list of elements */
  createElementsList(items) {
    const result = document.createElement('ul');

    for (const row of items) {
      result.appendChild(this.createRow(row));
    }

    return result;
  }

  /**
   * Show results
   * @param  {[type]} data [description]
   * @return {[type]}      [description]
   */
  showResults(data) {
    this.clear();
    if (this.config.debug) console.debug(data);
    if (!data || data.length === 0) this.dropdown.appendChild(this.createEmptyMessage());
    else {
      const ul = this.createElementsList(data);
      this.dropdown.appendChild(ul);
      this.setActiveElement(ul.firstChild);
      this.onEnterClick = () => this.fetchDetails(this.activeElement.listRow);
    }

    this.show();
  }

  createEmptyMessage() {
    const ul = document.createElement('ul');
    const li = document.createElement('li');
    li.innerHTML = this.config.noResultsMessage;
    ul.appendChild(li);

    return ul;
  }

  setActiveElement(elem) {
    if (this.config.debug) console.debug('Set active element');
    if (this.activeElement) this.activeElement.className = '';
    const el = elem;
    el.className = 'active';
    this.activeElement = el;
    if (this.config.debug) console.debug('Setting active element', el);
  }

  show() {
    this.dropdown.style.visibility = 'visible';
    this.isVisible = true;
  }

  hide() {
    this.dropdown.style.visibility = 'hidden';
    this.isVisible = false;
  }

  resetVars() {
    this.isCreated = false;
    this.isVisible = false;
    this.activeElement = null;
    this.isLoaderShown = false;
  }

  showLoader() {
    if (this.config.debug) console.debug('Loading...');
  }

  clear() {
    this.dropdown.innerHTML = '';
  }

  select() {
    console.log('selected');
  }

  remove() {
    if (this.config.debug) console.debug('Remove dropdown');
    this.hide();
    this.resetVars();
  }

  keyDownEvent(event) {
    let preventdef = true;

    switch (event.keyCode) {
      case keynumCodes.DOWN:
        if (!this.activeElement || !this.activeElement.nextSibling) return;
        this.setActiveElement(this.activeElement.nextSibling);
        break;
      case keynumCodes.UP:
        if (!this.activeElement || !this.activeElement.previousSibling) return;
        this.setActiveElement(this.activeElement.previousSibling);
        break;
      case keynumCodes.ENTER:
        if (this.onEnterClick) this.onEnterClick();
        break;
      case keynumCodes.ESC:
        this.remove();
        break;
      default:
        preventdef = false;
        break;
    }
    if (preventdef) event.preventDefault();
  }

}

export default Dropdown;
