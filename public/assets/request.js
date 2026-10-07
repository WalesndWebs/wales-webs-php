(() => {
  const form = document.querySelector('[data-request-wizard]');
  if (!form) return;
  const sections = [...form.querySelectorAll('[data-step]')];
  const back = form.querySelector('[data-step-back]');
  const next = form.querySelector('[data-step-next]');
  const send = form.querySelector('[data-request-submit]');
  let step = 0;
  form.classList.add('enhanced');
  const review = () => {
    const dl = form.querySelector('[data-request-review]');
    dl.replaceChildren();
    for (const control of form.querySelectorAll('input[name],textarea[name],select[name]')) {
      if (control.name.startsWith('_') || control.type === 'checkbox' || !control.value) continue;
      const row = document.createElement('div');
      const term = document.createElement('dt');
      const definition = document.createElement('dd');
      term.textContent = control.name.replaceAll('_', ' ');
      definition.textContent = control.tagName === 'SELECT' ? control.selectedOptions[0].textContent : control.value;
      row.append(term, definition); dl.append(row);
    }
  };
  const show = () => {
    sections.forEach((section, index) => { section.hidden = index !== step; });
    form.querySelectorAll('[data-step-label]').forEach((label, index) => {
      label.classList.toggle('active', index === step);
      label.setAttribute('aria-current', index === step ? 'step' : 'false');
    });
    back.hidden = step === 0; next.hidden = step === 2; send.hidden = step !== 2;
    if (step === 2) review();
  };
  const validateStep = () => {
    const invalid = [...sections[step].querySelectorAll('input,textarea,select')].find(control => !control.checkValidity());
    if (invalid) { invalid.reportValidity(); return false; }
    return true;
  };
  next.addEventListener('click', () => { if (validateStep()) { step++; show(); form.scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start'}); } });
  back.addEventListener('click', () => { step--; show(); });
  form.addEventListener('submit', event => {
    if (step !== 2) { event.preventDefault(); if (validateStep()) { step++; show(); } return; }
    if (!validateStep()) event.preventDefault();
  });
  show();
  if (form.hasAttribute('data-has-errors')) {
    const invalidStep = sections.findIndex(section => [...section.querySelectorAll('input,textarea,select')].some(control => !control.checkValidity()));
    step = invalidStep < 0 ? 0 : invalidStep; show();
  }
})();