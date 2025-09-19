document.addEventListener('DOMContentLoaded', function() {

  // Auto-submit form when dropdowns change
  const autoSubmitDropdowns = document.querySelectorAll('select.auto-submit');
  autoSubmitDropdowns.forEach(element => {
    element.addEventListener('change', function() {
      document.querySelector('form').submit();
    });
  });
  
  // Handle checkboxes separately to ensure proper values are sent
  const checkboxes = document.querySelectorAll('input[type="checkbox"].auto-submit');
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      // Create hidden input to ensure unchecked boxes send a value of 0
      if (!this.checked) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = this.name;
        hiddenInput.value = '0';
        document.querySelector('form').appendChild(hiddenInput);
      }
      document.querySelector('form').submit();
    });
  });

  // Modal logic
  const overlay = document.getElementById('price-modal-overlay');
  const priceInput = document.getElementById('price-input');
  const dealInput = document.getElementById('dealprice-input');
  const modalTitle = document.getElementById('price-modal-title');
  const btnCancel = document.getElementById('price-cancel');
  const btnSave = document.getElementById('price-save');
  let currentName = '';

  function openModal(name) {
    currentName = name;
    modalTitle.textContent = `Enter prices — ${name}`;
    // Prefer the currently displayed values in the clicked row (existing entry prices)
    // Fallback to any staged values present in importMap
    const row = document.querySelector(`.list-row[data-name="${CSS.escape(name)}"]`);
    let priceVal = '';
    let dealVal = '';
    if( row ) {
      const regEl = row.querySelector('.price-regular');
      const dealEl = row.querySelector('.price-deal');
      const t1 = regEl ? regEl.textContent.trim() : '';
      const t2 = dealEl ? dealEl.textContent.trim() : '';
      // Keep as-is unless it's clearly not available
      priceVal = (t1 && t1.toLowerCase() !== 'n/a') ? t1 : '';
      dealVal  = (t2 && t2.toLowerCase() !== 'n/a') ? t2 : '';
    }
    if( priceVal === '' || dealVal === '' ) {
      const staged = importMap[name] || {};
      if( priceVal === '' && staged.price != null ) priceVal = staged.price;
      if( dealVal === '' && staged.dealPrice != null ) dealVal = staged.dealPrice;
    }
    priceInput.value = priceVal;
    dealInput.value = dealVal;
    overlay.style.display = 'flex';
    priceInput.focus();
  }
  function closeModal() {
    overlay.style.display = 'none';
    currentName = '';
  }
  btnCancel.addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => { if( e.target === overlay) closeModal(); });

  // Click row to open modal
  document.querySelectorAll('.list-row').forEach(row => {
    row.addEventListener('click', function(e) {
      // Avoid clicks on form elements
      if( e.target.closest('select, input, a, button')) return;
      const name = this.getAttribute('data-name');
      if( name) openModal(name);
    });
  });

  // Save via ajax router
  btnSave.addEventListener('click', async function() {
    const payload = {
      action: 'save_import',
      name: currentName,
      price: priceInput.value.trim(),
      dealPrice: dealInput.value.trim()
    };
    // Remove empty strings so backend can treat as removal when both empty
    if( payload.price === '') delete payload.price;
    if( payload.dealPrice === '') delete payload.dealPrice;

    try {
      const res = await fetch('ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if( json.status !== 'success') throw new Error(json.message || 'Save failed');

      // Update local map and UI highlight
      if( json.data && Object.keys(json.data).length) {
        importMap[currentName] = json.data;
        const row = document.querySelector(`.list-row[data-name="${CSS.escape(currentName)}"]`);
        if( row) row.classList.add('has-import');
      }
      else {
        delete importMap[currentName];
        const row = document.querySelector(`.list-row[data-name="${CSS.escape(currentName)}"]`);
        if( row) row.classList.remove('has-import');
      }

      closeModal();
    }
    catch(err) {
      alert(err.message || 'Error saving');
    }
  });

  // Client-side search filter by name
  const searchInput = document.getElementById('search-filter');
  if( searchInput )
  {
    const rows = Array.from(document.querySelectorAll('.list-row'));
    const getName = (row) => (row.getAttribute('data-name') || row.querySelector('.col-name')?.textContent || '').toLowerCase();
    const names = new Map(rows.map(r => [r, getName(r)]));

    function applyFilter()
    {
      const q = searchInput.value.trim().toLowerCase();
      rows.forEach(row => {
        const match = q === '' || names.get(row).includes(q);
        row.style.display = match ? '' : 'none';
      });
    }

    // Debounce to avoid excessive reflow on fast typing
    let t = null;
    searchInput.addEventListener('input', function() {
      clearTimeout(t);
      t = setTimeout(applyFilter, 80);
    });
  }
});
