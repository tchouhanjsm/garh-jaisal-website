(() => {
  const root = document.querySelector('[data-custom-itinerary-builder]');
  if (!root) return;
  const form = root.querySelector('#custom-itinerary-form');
  const preview = root.querySelector('#custom-itinerary-draft');
  const status = root.querySelector('#custom-itinerary-status');
  const whatsappLink = root.querySelector('#custom-itinerary-whatsapp');
  const whatsappBase = root.dataset.whatsappUrl;
  if (!form || !preview || !status || !whatsappLink || !whatsappBase) return;

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
  const updateDraft = () => {
    const hasInterests = form.querySelector('input[name="interests"]:checked') !== null;
    const hasPlaces = valueOf('custom-places') !== '';
    const hasPreferences = valueOf('custom-preferences') !== '';
    const isReady = hasInterests || hasPlaces || hasPreferences;
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
      status.textContent = 'Select an interest or add a place/idea to prepare the message link.';
    }
  };
  form.addEventListener('input', updateDraft);
  form.addEventListener('change', updateDraft);
  form.addEventListener('submit', (event) => event.preventDefault());
  whatsappLink.addEventListener('click', (event) => {
    if (whatsappLink.getAttribute('aria-disabled') === 'true') event.preventDefault();
  });
  updateDraft();
})();
