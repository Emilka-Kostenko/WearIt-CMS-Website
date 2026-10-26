/**
 * Mini-cart: open/close the header dropdown, "Add to Cart" on shop product
 * cards and front page drops, "Remove" inside the dropdown. Server side: inc/cart.php.
 */
(function () {
  const root   = document.getElementById('mini-cart');
  const toggle = document.getElementById('mini-cart-toggle');
  const panel  = document.getElementById('mini-cart-panel');
  const badge  = document.getElementById('mini-cart-badge');
  if (!root || !toggle || !panel || !window.wearitCart) return;

  function open() {
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
  }

  function close() {
    panel.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
  }

  toggle.addEventListener('click', () => (panel.hidden ? open() : close()));

  // Shop product cards use data-product-id, front page drops use data-cart-key
  const ADD_BUTTONS = '.product-card__add, .card_add_to_cart';

  // Close on click outside or Escape
  document.addEventListener('click', (e) => {
    if (!panel.hidden && !root.contains(e.target) && !e.target.closest(ADD_BUTTONS)) close();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !panel.hidden) {
      close();
      toggle.focus();
    }
  });

  function render(data) {
    panel.innerHTML = data.html;
    badge.textContent = data.count;
    badge.hidden = data.count === 0;
  }

  async function post(url, key) {
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key: String(key) }),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Cart request failed');
    return data;
  }

  // Add to Cart (shop product cards + front page drops)
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest(ADD_BUTTONS);
    const key = btn && (btn.dataset.cartKey || btn.dataset.productId);
    if (!key || btn.disabled) return;

    const label = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Adding…';
    try {
      render(await post(wearitCart.addUrl, key));
      btn.textContent = 'Added';
      open();
    } catch (err) {
      btn.textContent = 'Try again';
      console.error(err);
    }
    setTimeout(() => {
      btn.textContent = label;
      btn.disabled = false;
    }, 1200);
  });

  // Remove (inside the dropdown)
  panel.addEventListener('click', async (e) => {
    const btn = e.target.closest('.mini-cart__remove');
    if (!btn) return;

    btn.disabled = true;
    try {
      render(await post(wearitCart.removeUrl, btn.dataset.cartKey));
    } catch (err) {
      btn.disabled = false;
      console.error(err);
    }
  });
})();
