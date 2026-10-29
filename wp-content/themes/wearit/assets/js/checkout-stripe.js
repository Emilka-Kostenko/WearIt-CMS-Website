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

  const placeOrder = pageForm.querySelector('button[type="submit"]');
  let mounted = false;

  const appearance = {
    theme: 'night',
    labels: 'auto',
    inputs: 'spaced',
    variables: {
      borderRadius: '4px',
      colorBackground: '#ffffff',
      colorDanger: '#df1b41',
      colorPrimary: '#0570de',
      colorSuccess: '#00c853',
      colorText: '#30313d',
      fontFamily: 'Montserrat',
      fontSizeBase: '16px',
      spacingUnit: '4px',
    },
  };

  // Created once, the first time "Card" is picked
  async function mount() {
    mounted = true;
    const stripe = Stripe(wearitStripe.publishableKey);

    const clientSecret = fetch(wearitStripe.sessionUrl, { method: 'POST' })
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
