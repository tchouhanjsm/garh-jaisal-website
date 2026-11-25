(() => {
  const form = document.querySelector('[data-car-quote-form]');
  if (!form) return;

  const target = new URL(form.dataset.whatsappUrl);
  const fields = form.elements;
  const value = (name) => fields.namedItem(name).value.trim();
  const pickupDate = fields.namedItem('pickupDate');
  const dropoffDate = fields.namedItem('dropoffDate');

  function validateDates() {
    dropoffDate.min = pickupDate.value;
    dropoffDate.setCustomValidity(dropoffDate.validity.rangeUnderflow ? 'Choose a drop-off date on or after pickup.' : '');
  }

  pickupDate.addEventListener('input', validateDates);
  dropoffDate.addEventListener('input', validateDates);
  for (const name of ['pickup', 'dropoff']) {
    const field = fields.namedItem(name);
    field.addEventListener('input', () => {
      field.setCustomValidity(field.value && !field.value.trim() ? 'Enter a location.' : '');
    });
  }

  document.querySelectorAll('[data-car-kind]').forEach((link) => {
    link.addEventListener('click', () => {
      fields.namedItem('kind').value = link.dataset.carKind;
    });
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    validateDates();
    if (!form.reportValidity()) return;
    target.searchParams.set('text', [
      'Hello, I would like a quote for a Rajasthan trip.',
      'Package: ' + value('kind'),
      'Pickup: ' + value('pickup') + ' on ' + value('pickupDate'),
      'Drop-off: ' + value('dropoff') + ' on ' + value('dropoffDate'),
      'Passengers: ' + value('passengers'),
      'Luggage: ' + (value('luggage') || 'Not specified'),
      'Stops or other details: ' + (value('stops') || 'Not specified'),
      'Please confirm availability, what the quote includes, and the total price.'
    ].join('\n'));
    window.location.assign(target.href);
  });

  validateDates();
  form.hidden = false;
})();
