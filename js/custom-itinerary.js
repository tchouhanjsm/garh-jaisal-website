(() => {
  const root = document.querySelector('[data-custom-itinerary-builder]');
  if (!root) return;
  const form = root.querySelector('#custom-itinerary-form');
  const preview = root.querySelector('#custom-itinerary-draft');
  const status = root.querySelector('#custom-itinerary-status');
  const whatsappLink = root.querySelector('#custom-itinerary-whatsapp');
  const whatsappBase = root.dataset.whatsappUrl;
  if (!form || !preview || !status || !whatsappLink || !whatsappBase) return;

  const isRajasthan = root.dataset.itineraryMode === 'rajasthan';
  const rajasthanFields = [
    ['rajasthan-dates', 'Travel dates or month'],
    ['rajasthan-duration', 'Duration'],
    ['custom-group-size', 'Travellers'],
    ['custom-places', 'Preferred route or stops'],
    ['rajasthan-pickup', 'Pickup'],
    ['rajasthan-dropoff', 'Drop-off'],
    ['rajasthan-inclusions', 'Requested inclusions'],
    ['rajasthan-budget', 'Budget or pricing questions'],
  ];

  const valueOf = (id) => {
    const field = root.querySelector('#' + id);
    return field ? field.value.trim() : '';
  };
  const selectedLabel = (id) => {
    const field = root.querySelector('#' + id);
    if (!field || !field.value) return 'Not specified';
    const option = field.options[field.selectedIndex];
    return option ? option.textContent.trim() : 'Not specified';
  };
  const buildMessage = () => {
    if (isRajasthan) {
      return [
        'Hello, I would like to enquire about a Rajasthan tour.',
        ...rajasthanFields.map(([id, label]) => label + ': ' + (valueOf(id) || 'Not specified')),
        '',
        'Please confirm a feasible route and duration, pickup and drop-off arrangements, availability, everything the quote includes or excludes, and the total price.',
        'I understand this is an enquiry only and does not reserve a tour.'
      ].join('\n');
    }

    const interests = Array.from(form.querySelectorAll('input[name="interests"]:checked'))
      .map((field) => field.value.trim()).filter(Boolean);
    return [
      'Hello, I would like to enquire about a custom Jaisalmer itinerary.',
      'Travel date or period: ' + (valueOf('custom-travel-date') || 'Not specified'),
      'Time available: ' + selectedLabel('custom-time-available'),
      'Group size: ' + (valueOf('custom-group-size') || 'Not specified'),
      'Preferred travel style: ' + selectedLabel('custom-travel-style'),
      'Interests: ' + (interests.length ? interests.join(', ') : 'Open to discussing suitable interests'),
      'Places or ideas to consider: ' + (valueOf('custom-places') || 'Open to discussing other suitable ideas'),
      'Preferred pace or other requests: ' + (valueOf('custom-preferences') || 'None added'),
      '',
      'Please confirm which route or stops can be arranged for my date, including current visitor access and rules, guide and transport availability, timings, applicable fees and the total price.',
      'I understand this is an enquiry only, not a booking. Nothing is reserved until the hotel confirms.'
    ].join('\n');
  };
  const travelDate = root.querySelector('#custom-travel-date');
  const updateDraft = () => {
    if (travelDate) {
      const parts = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kolkata', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date());
      const part = (type) => parts.find(item => item.type === type).value;
      travelDate.min = part('year') + '-' + part('month') + '-' + part('day');
    }
    const hasInterests = form.querySelector('input[name="interests"]:checked') !== null;
    const hasPlaces = valueOf('custom-places') !== '';
    const hasPreferences = valueOf('custom-preferences') !== '';
    const isReady = isRajasthan
      ? rajasthanFields.some(([id]) => valueOf(id) !== '') && form.checkValidity()
      : (hasInterests || hasPlaces || hasPreferences) && form.checkValidity();
    const message = buildMessage();
    preview.value = message;
    whatsappLink.setAttribute('aria-disabled', String(!isReady));
    whatsappLink.tabIndex = isReady ? 0 : -1;
    if (isReady) {
      try {
        const target = new URL(whatsappBase);
        target.searchParams.set('text', message);
        whatsappLink.href = target.toString();
        status.textContent = 'Draft ready. Review the message, then click Open WhatsApp to continue.';
      } catch (error) {
        whatsappLink.href = '#';
        whatsappLink.setAttribute('aria-disabled', 'true');
        whatsappLink.tabIndex = -1;
        status.textContent = 'The WhatsApp link is unavailable. Use the contact page instead.';
      }
    } else {
      whatsappLink.href = '#';
      status.textContent = isRajasthan
        ? 'Add at least one valid trip detail to prepare the message link.'
        : 'Select an interest or add a place/idea, and check any dates or traveller count to prepare the message link.';
    }
  };
  form.addEventListener('input', updateDraft);
  form.addEventListener('change', updateDraft);
  form.addEventListener('submit', (event) => event.preventDefault());
  whatsappLink.addEventListener('click', (event) => {
    updateDraft();
    if (whatsappLink.getAttribute('aria-disabled') === 'true') {
      event.preventDefault();
      form.reportValidity();
    }
  });
  updateDraft();
})();
