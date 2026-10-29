/**
 * Checkout page (checkoutPage.php): Stripe's embedded card form.
 * Shown when "Card" is picked as the payment method; the form then has its own
 * pay button, so "Place Order" is hidden and the page form can't be submitted.
 * Server side: wearit_rest_checkout_session() in inc/checkout.php.
 */
(function () {
  const container = document.getElementById('checkout-form');
  const pageForm  = document.querySelector('.checkout-form');
  if (!container || !pageForm || !window.Stripe || !window.wearitStripe) return;

  // Not the coupon buttons: they sit in this form too but submit other forms via form="…"
  const placeOrder = pageForm.querySelector('button[type="submit"]:not([form])');
  let mounted = false;

  // Matches the shop (css/style.css): black fields, white text, lime accent, square corners.
  // The form is Stripe's iframe, so page CSS can't reach it — and it doesn't take `rules`,
  // only these variables. Colours must be hex: Stripe rejects var(--…) and rgba() here.
  const appearance = {
    theme: 'night',
    labels: 'above',               // like the delivery fields above it
    inputs: 'spaced',
    variables: {
      borderRadius: '0px',
      colorBackground: '#0d0d0d',  // --color-bg, same as .checkout-field__input
      colorText: '#ffffff',        // --color-text
      colorTextSecondary: '#a0a0a0', // --color-text-muted
      colorTextPlaceholder: '#888888', // --shop-muted
      colorPrimary: '#caff33',     // --color-accent: pay button, focus, selected
      colorPrimaryText: '#0d0d0d', // dark text on lime, like .cart-button--primary
      colorSuccess: '#caff33',
      colorDanger: '#ff6b6b',      // a red that stays readable on black
      // Barlow is the site font but only loads on the page, not inside Stripe's iframe,
      // so Stripe falls back to the site's own fallback stack (--font-primary).
      fontFamily: 'Barlow, "Helvetica Neue", Arial, sans-serif',
      fontSizeBase: '16px',
      spacingUnit: '4px',
    },
  };

  // Created once, the first time "Card" is picked
  async function mount() {
    mounted = true;
    const stripe = Stripe(wearitStripe.publishableKey);

    const clientSecret = fetch(wearitStripe.sessionUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-WP-Nonce': wearitStripe.nonce },
    })
      .then((response) => response.json())
      .then((json) => json.client_secret);
    const checkout = stripe.initCheckoutFormSdk({ clientSecret, appearance });

    const form = checkout.createForm({ layout: 'expanded' });
    form.mount('#checkout-form');

    const loadActionsResult = await checkout.loadActions();
    if (loadActionsResult.type === 'success') {
      form.on('confirm', async (event) => {
        try {
          await loadActionsResult.actions.confirm({ formConfirmEvent: event });
        } catch (error) {
          console.error('Payment confirmation error:', error);
        }
      });
    }
  }

  function isCard() {
    const picked = pageForm.querySelector('input[name="checkout_payment"]:checked');
    return picked && picked.value === 'card';
  }

  function update() {
    const card = isCard();
    container.hidden = !card;
    if (placeOrder) placeOrder.hidden = card;
    if (card && !mounted) mount();
  }

  pageForm.addEventListener('change', (e) => {
    if (e.target.name === 'checkout_payment') update();
  });

  // Enter in a field would otherwise post a card order that was never paid
  pageForm.addEventListener('submit', (e) => {
    if (isCard()) e.preventDefault();
  });

  update();
})();
