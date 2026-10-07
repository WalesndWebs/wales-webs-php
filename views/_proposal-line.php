<div class="proposal-line" data-line>
<label class="field line-description"><span>Description</span><input name="lines[<?= e($index) ?>][description]" value="<?= e($line['description']) ?>" required maxlength="300" placeholder="Work or deliverable" data-line-field="description"></label>
<label class="field"><span>Qty</span><input type="number" name="lines[<?= e($index) ?>][quantity]" value="<?= e($line['quantity']) ?>" required min="1" max="10000" step="1" data-line-field="quantity"></label>
<label class="field"><span>Unit price</span><input name="lines[<?= e($index) ?>][unitPrice]" value="<?= e($line['unitPrice']) ?>" required pattern="[0-9]{1,10}(\.[0-9]{1,2})?" maxlength="13" inputmode="decimal" placeholder="0.00" data-line-field="unitPrice"></label>
<div class="line-subtotal"><span>Line total</span><strong data-line-total>—</strong></div><button type="button" class="remove-line js-only" data-remove-line aria-label="Remove line item">×</button>
</div>