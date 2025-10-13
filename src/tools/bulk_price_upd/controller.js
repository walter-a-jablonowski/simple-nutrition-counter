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
      if( ! this.checked) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type  = 'hidden';
        hiddenInput.name  = this.name;
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
  const unavailableCheckbox = document.getElementById('unavailable-checkbox');
  const modalTitle = document.getElementById('price-modal-title');
  const modalDetails = document.getElementById('price-modal-details');
  const btnCancel = document.getElementById('price-cancel');
  const btnSave = document.getElementById('price-save');
  const btnReset = document.getElementById('price-reset');
  let currentName = '';

  function openModal(name)
  {
    currentName = name;
    modalTitle.textContent = `Enter prices — ${name}`;
    
    // Prefer the currently displayed values in the clicked row (existing entry prices)
    // Fallback to any staged values present in importMap
    const row = document.querySelector(`.list-row[data-name="${CSS.escape(name)}"]`);
    let priceVal = '';
    let dealVal = '';
    
    // Build details line from data attributes
    if( row ) {
      const productName = row.getAttribute('data-product-name') || '';
      const weight = row.getAttribute('data-weight') || '';
      const pieces = row.getAttribute('data-pieces') || '';
      
      const detailsParts = [];
      if( productName ) detailsParts.push(productName);
      if( weight ) detailsParts.push(weight);
      if( pieces ) detailsParts.push(`${pieces} pieces`);
      
      modalDetails.textContent = detailsParts.join(' • ');
      
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

    // Set unavailable checkbox state
    const staged = importMap[name] || {};
    unavailableCheckbox.checked = staged.state === 'unavailable';

    priceInput.value = priceVal;
    dealInput.value = dealVal;
    overlay.style.display = 'flex';
    priceInput.focus();
  }

  function closeModal()
  {
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
      dealPrice: dealInput.value.trim(),
      unavailable: unavailableCheckbox.checked
    };

    // Remove empty strings so backend can treat as removal when both empty
    if( payload.price === '')      delete payload.price;
    if( payload.dealPrice === '')  delete payload.dealPrice;

    try {

      const res = await fetch('ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if( json.status !== 'success') throw new Error(json.message || 'Save failed');

      // Update local map and UI highlight
      if( json.data && Object.keys(json.data).length) {
        importMap[currentName] = json.data;
        const row = document.querySelector(`.list-row[data-name="${CSS.escape(currentName)}"]`);
        if( row) row.classList.add('has-import');

        // Reflect new values in the visible list immediately
        if( row )
        {
          const setOrRemove = (selector, cls, value) => {
            let el = row.querySelector(selector);
            if( value === undefined || value === '' ) {
              if( el ) el.remove();
              return;
            }
            if( ! el ) {
              el = document.createElement('span');
              el.className = cls;
              const container = row.querySelector('.col-price') || row.querySelector('.mobile-price') || row;
              // Insert regular before deal if possible
              if( cls === 'price-regular') {
                const before = row.querySelector('.price-deal');
                if( before && before.parentNode === container) container.insertBefore(el, before);
                else container.appendChild(el);
              } else {
                container.appendChild(el);
              }
            }
            el.textContent = value;
          };

          setOrRemove('.price-regular', 'price-regular', json.data.price);
          setOrRemove('.price-deal', 'price-deal', json.data.dealPrice);
        }
      }
      else {
        delete importMap[currentName];
        const row = document.querySelector(`.list-row[data-name="${CSS.escape(currentName)}"]`);
        if( row) row.classList.remove('has-import');

        // Remove displayed values if none are staged now (fall back to original remains in markup only if present)
        // Here we do nothing further; on next page load PHP will render source prices.
      }

      closeModal();
    }
    catch(err) {
      alert(err.message || 'Error saving');
    }
  });

  // Reset button - removes entire food entry from import.yml
  btnReset.addEventListener('click', async function() {
    if( ! confirm(`Reset all entered data for "${currentName}"?`)) return;

    const payload = {
      action: 'reset_import',
      name: currentName
    };

    try {
      const res = await fetch('ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if( json.status !== 'success') throw new Error(json.message || 'Reset failed');

      // Remove from local map
      delete importMap[currentName];
      
      // Update UI
      const row = document.querySelector(`.list-row[data-name="${CSS.escape(currentName)}"]`);
      if( row ) {
        row.classList.remove('has-import');
        
        // Remove price displays (will show original or n/a)
        const priceRegular = row.querySelector('.price-regular');
        const priceDeal = row.querySelector('.price-deal');
        if( priceRegular ) priceRegular.remove();
        if( priceDeal ) priceDeal.remove();
        
        // Add n/a if no prices shown
        const priceContainers = row.querySelectorAll('.col-price, .mobile-price');
        priceContainers.forEach(container => {
          if( ! container.textContent.trim()) {
            const span = document.createElement('span');
            span.textContent = 'n/a';
            container.appendChild(span);
          }
        });
      }

      closeModal();
    }
    catch(err) {
      alert(err.message || 'Error resetting');
    }
  });

  // Comments modal logic
  
  const btnOpenComments = document.getElementById('btn-open-comments');
  const commentsOverlay = document.getElementById('comments-modal-overlay');
  const commentsTextarea = document.getElementById('comments-text');
  const commentsCancel = document.getElementById('comments-cancel');
  const commentsSave = document.getElementById('comments-save');

  function openCommentsModal(content) {
    commentsTextarea.value = content || '';
    commentsOverlay.style.display = 'flex';
    commentsTextarea.focus();
  }
  function closeCommentsModal() {
    commentsOverlay.style.display = 'none';
  }

  if( btnOpenComments ) {
    btnOpenComments.addEventListener('click', async function() {
      try {
        const res = await fetch('ajax.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ action: 'get_comments'})
        });
        const json = await res.json();
        if( json.status !== 'success' ) throw new Error(json.message || 'Load failed');
        const content = (json.data && typeof json.data.content === 'string') ? json.data.content : '';
        openCommentsModal(content);
      }
      catch(err) {
        alert(err.message || 'Error loading comments');
      }
    });
  }

  if( commentsCancel ) commentsCancel.addEventListener('click', closeCommentsModal);
  if( commentsOverlay ) commentsOverlay.addEventListener('click', (e) => { if( e.target === commentsOverlay ) closeCommentsModal(); });

  if( commentsSave ) {
    commentsSave.addEventListener('click', async function() {
      const content = commentsTextarea ? commentsTextarea.value : '';
      try {
        const res = await fetch('ajax.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ action: 'save_comments', content })
        });
        const json = await res.json();
        if( json.status !== 'success' ) throw new Error(json.message || 'Save failed');
        closeCommentsModal();
      }
      catch(err) {
        alert(err.message || 'Error saving comments');
      }
    });
  }

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
