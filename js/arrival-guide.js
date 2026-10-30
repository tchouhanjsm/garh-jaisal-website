(() => {
  'use strict';
  const button = document.querySelector('[data-arrival-copy]');
  const status = document.querySelector('[data-arrival-copy-status]');
  if (!button || !status) return;
  const arrivalNote = button.getAttribute('data-arrival-copy') || '';
  if (!arrivalNote.trim()) return;

  const legacyCopy = (value) => {
    const field = document.createElement('textarea');
    field.value = value;
    field.setAttribute('readonly', '');
    field.setAttribute('aria-hidden', 'true');
    field.style.position = 'fixed';
    field.style.inset = '0 auto auto -9999px';
    document.body.appendChild(field);
    field.select();
    let copied = false;
    try { copied = document.execCommand('copy'); } catch { copied = false; }
    field.remove();
    return copied;
  };

  button.hidden = false;
  button.addEventListener('click', async () => {
    button.disabled = true;
    status.textContent = '';
    let copied = false;
    try {
      if (!navigator.clipboard || !window.isSecureContext) throw new Error('Clipboard API unavailable');
      await navigator.clipboard.writeText(arrivalNote);
      copied = true;
    } catch {
      copied = legacyCopy(arrivalNote);
    }
    status.textContent = copied
      ? 'Arrival note copied. Share it with your driver before you set out.'
      : 'Copy is unavailable in this browser. Use the address above and open the exact hotel pin instead.';
    button.disabled = false;
  });
})();
