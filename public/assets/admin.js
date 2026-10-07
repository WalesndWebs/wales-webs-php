(() => {
  document.documentElement.classList.add('js');
  const error = document.querySelector('[data-error-summary]');
  if (error) error.focus();
  document.querySelectorAll('[data-confirm-delete]').forEach(form => form.addEventListener('submit', event => {
    if (!confirm('Permanently delete this story? This cannot be undone.')) event.preventDefault();
  }));
  const editor = document.querySelector('[data-proposal-editor]');
  if (editor) {
    const lines = editor.querySelector('[data-line-items]');
    const currency = editor.querySelector('[data-currency]');
    const formatted = amount => (amount / 100n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '.' + (amount % 100n).toString().padStart(2, '0');
    const recalculate = () => {
      let total = 0n;
      const rows = [...lines.querySelectorAll('[data-line]')];
      rows.forEach((line, index) => {
        line.querySelectorAll('[data-line-field]').forEach(input => { input.name = `lines[${index}][${input.dataset.lineField}]`; });
        const price = line.querySelector('[data-line-field="unitPrice"]').value;
        const qty = line.querySelector('[data-line-field="quantity"]').value;
        let amount = 0n;
        if (/^[0-9]{1,10}(\.[0-9]{1,2})?$/.test(price) && /^[0-9]+$/.test(qty) && Number(qty) >= 1 && Number(qty) <= 10000) {
          const [integer, decimal = ''] = price.split('.');
          amount = (BigInt(integer) * 100n + BigInt(decimal.padEnd(2, '0'))) * BigInt(qty);
        }
        total += amount;
        line.querySelector('[data-line-total]').textContent = currency.value.trim() + ' ' + formatted(amount);
        line.querySelector('[data-remove-line]').disabled = rows.length === 1;
      });
      editor.querySelector('[data-proposal-total]').textContent = currency.value.trim() + ' ' + formatted(total);
      editor.querySelector('[data-add-line]').disabled = rows.length >= 40;
    };
    editor.addEventListener('input', recalculate);
    editor.addEventListener('click', event => {
      if (event.target.closest('[data-add-line]')) {
        if (lines.children.length >= 40) return;
        lines.append(editor.querySelector('[data-line-template]').content.cloneNode(true));
        recalculate(); lines.lastElementChild.querySelector('input').focus();
      }
      const remove = event.target.closest('[data-remove-line]');
      if (remove && lines.children.length > 1) { remove.closest('[data-line]').remove(); recalculate(); }
    });
    recalculate();
  }
  if (document.body.dataset.printOnLoad === '1') window.addEventListener('load', () => window.print(), {once:true});
})();