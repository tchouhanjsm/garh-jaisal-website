(() => {
  const form = document.querySelector('[data-car-quote-form]');
  if (!form) return;

  const target = new URL(form.dataset.whatsappUrl);
  const fields = form.elements;
  const value = (name) => fields.namedItem(name).value.trim();
  const pickupDate = fields.namedItem('pickupDate');
  const dropoffDate = fields.namedItem('dropoffDate');

  function validateDates() {
    const today = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
    const part = (type) => today.find((p) => p.type === type).value;
    pickupDate.min = `${part('year')}-${part('month')}-${part('day')}`;
    pickupDate.setCustomValidity(pickupDate.validity.rangeUnderflow ? 'Choose today or a future pickup date.' : '');
    dropoffDate.min = pickupDate.value > pickupDate.min ? pickupDate.value : pickupDate.min;
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

  document.querySelectorAll('[data-car-route]').forEach((link) => {
    link.previousElementSibling.hidden = true;
    link.hidden = false;
    link.addEventListener('click', () => {
      fields.namedItem('route').value = link.dataset.carRoute;
      for (const name of ['pickup', 'dropoff']) {
        const field = fields.namedItem(name);
        if (!value(name)) {
          field.value = 'Delhi Airport';
          field.setCustomValidity('');
        }
      }
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
      value('route') && 'Sample route: ' + value('route'),
      'Stops or other details: ' + (value('stops') || 'Not specified'),
      'Please confirm availability, what the quote includes, and the total price.'
    ].filter(Boolean).join('\n'));
    form.dispatchEvent(new Event('garh:car-quote-ready', { bubbles: true }));
    window.location.assign(target.href);
  });

  validateDates();
  form.hidden = false;
})();
