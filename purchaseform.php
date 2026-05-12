<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'pconfig.php';
require_once 'southwinds/phoenixeyes.php';
require_once 'southwinds/shipping_utils.php';
require_once 'southwinds/tax_utils.php';

if (empty($_SESSION['cart'])) {
    header('Location: gallery.php');
    exit;
}
if (empty($_SESSION['shipping'])) {
    header('Location: checkout_shipping.php');
    exit;
}

$billing           = $_SESSION['billing']  ?? [];
$shipping_by_piece = $_SESSION['shipping'] ?? [];
$billing_collected = !empty($billing['collected_here']);

// Pull cart items
$pieces_in_cart  = $_SESSION['cart'];
$array_to_qmarks = implode(',', array_fill(0, count($pieces_in_cart), '?'));
$stmt            = $fy->prepare("SELECT * FROM works WHERE pieceID IN ($array_to_qmarks)");
$stmt->execute(array_keys($pieces_in_cart));
$cart_pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Build destination groups ───────────────────────────────────────────────
$groups     = [];
$subtotal   = 0.00;
$any_pickup = false;
$all_pickup = true;

foreach ($cart_pieces as $p) {
    $pid    = $p['pieceID'];
    $info   = $shipping_by_piece[$pid] ?? ['method' => 'same_as_billing'];
    $method = $info['method'];
    $subtotal += (float)$p['price'];

    if ($method === 'local_pickup') {
        $any_pickup = true;
        $key        = 'pickup';
        $label      = '📦 Local pickup (Gwinnett County, GA/Metro Atlanta area)';
    } elseif ($method === 'same_as_billing') {
        $all_pickup = false;
        $key        = 'billing';
        $label      = '<img src="usps_logo.svg" width="20" alt="United States Postal Service"> Ship to billing address';
        if ($billing_collected) {
            $label .= ' (' . htmlspecialchars($billing['street'] ?? '') . ', '
                           . htmlspecialchars($billing['city']   ?? '') . ', '
                           . htmlspecialchars($billing['state']  ?? '') . ')';
        }
    } else {
        $all_pickup = false;
        $dest_slug  = strtolower(($info['zip'] ?? '') . ($info['street'] ?? ''));
        $key        = 'alt_' . md5($dest_slug);
        $first_name = explode(' ', $info['name'] ?? '')[0];
        $label      = '<img src="usps_logo.svg" width="20" alt="United States Postal Service"> Ship to ' .htmlspecialchars($first_name) . ' at ' . htmlspecialchars($info['street'] ?? '') . ', '
                            . htmlspecialchars($info['city']   ?? '') . ', '
                            . htmlspecialchars($info['state']  ?? '') . ' '
                            . htmlspecialchars($info['zip']    ?? '');
    }

    if (!isset($groups[$key])) {
        $groups[$key] = ['label' => $label, 'key' => $key, 'pieces' => []];
    }
    $groups[$key]['pieces'][] = $p;
}

// ── Calculate shipping per destination group ───────────────────────────────
$shipping_costs = [];
$ship_total     = 0.00;
$insurance_total = 0.00;

foreach ($groups as $key => $group) {
    if ($key === 'pickup') continue;

    // Destination address for this group
    if ($key === 'billing') {
        $dest_zip  = $billing['zip']    ?? '';
        $dest_addr = [
            'street' => $billing['street'] ?? '',
            'city'   => $billing['city']   ?? '',
            'state'  => $billing['state']  ?? '',
        ];
    } else {
        $first_pid = $group['pieces'][0]['pieceID'];
        $dest_zip  = $shipping_by_piece[$first_pid]['zip']    ?? '';
        $dest_addr = [
            'street' => $shipping_by_piece[$first_pid]['street'] ?? '',
            'city'   => $shipping_by_piece[$first_pid]['city']   ?? '',
            'state'  => $shipping_by_piece[$first_pid]['state']  ?? '',
        ];
    }

    // Declared value = sum of piece prices in this group
    $group_value = array_sum(array_column($group['pieces'], 'price'));

    $result               = get_shipping_cost($group['pieces'], $dest_zip, (float)$group_value, $dest_addr);
    $shipping_costs[$key] = $result;
    $ship_total          += $result['cost'];
    $insurance_total     += $result['insurance'];
}



// ── Sales tax ──────────────────────────────────────────────────────────────
// Tax is calculated per destination group on (items + shipping) for that group.
// For pickup groups: no shipping cost, but items are still taxable if GA delivery.
$tax_by_group = [];
$tax_total    = 0.00;

foreach ($groups as $key => $group) {
    if ($key === 'pickup') {
        // Pickup is at Lilburn, GA — taxable at Gwinnett County rate
        $group_subtotal  = array_sum(array_column($group['pieces'], 'price'));
        $tax             = get_sales_tax('', 'Lilburn', 'GA', '30047', $group_subtotal);
    } else {
        $ship_cost       = $shipping_costs[$key]['cost'] ?? 0.0;
        // Tax on items + shipping if shipping is taxable at destination
        $shipping_taxable = $shipping_costs[$key]['estimated']
            ? true  // assume taxable when estimated (safe default)
            : ($shipping_costs[$key]['packages'][0]['rate']['shipping_taxable'] ?? true);

        $group_subtotal  = array_sum(array_column($group['pieces'], 'price'));
        $taxable_amount  = $group_subtotal + ($shipping_taxable ? $ship_cost : 0.0);

        if ($key === 'billing') {
            $tax = get_sales_tax(
                $billing['street'] ?? '',
                $billing['city']   ?? '',
                $billing['state']  ?? '',
                $billing['zip']    ?? '',
                $taxable_amount
            );
        } else {
            $first_pid = $group['pieces'][0]['pieceID'];
            $tax = get_sales_tax(
                $shipping_by_piece[$first_pid]['street'] ?? '',
                $shipping_by_piece[$first_pid]['city']   ?? '',
                $shipping_by_piece[$first_pid]['state']  ?? '',
                $shipping_by_piece[$first_pid]['zip']    ?? '',
                $taxable_amount
            );
        }
    }

    $tax_by_group[$key] = $tax;
    $tax_total         += $tax['tax_amount'];
}

$total       = $subtotal + $ship_total + $tax_total;
$total_cents = (int)round($total * 100);

// ── Store shipping costs and group details in session for confirm_order.php ──
$_SESSION['shipping_costs'] = $shipping_costs;
$_SESSION['tax_by_group']   = $tax_by_group;
$_SESSION['groups_detail']  = $groups;
$_SESSION['totals']         = [
    'subtotal'  => $subtotal,
    'shipping'  => $ship_total,
    'tax'       => $tax_total,
    'total'     => $total,
];

// ── Create PaymentIntent ───────────────────────────────────────────────────
$stripe        = new \Stripe\StripeClient(STRIPE_PRIVATE_KEY);
$client_secret = '';
$pi_error      = '';

try {
    $intent = $stripe->paymentIntents->create([
        'amount'               => $total_cents,
        'currency'             => 'usd',
        'payment_method_types' => ['card'],
        'metadata'             => [
            'piece_ids' => implode(',', array_keys($pieces_in_cart)),
        ],
    ]);
    $client_secret = $intent->client_secret;
} catch (\Stripe\Exception\ApiErrorException $e) {
    $pi_error = $e->getMessage();
}

include 'head.php';
include 'nav.php';
?>

<div class="main-container">
  <div class="col-md-6 col-md-offset-3">
    <h1 class="gallery-header">Payment</h1>

    <?php if ($pi_error): ?>
      <div class="alert alert-danger"><strong>Error:</strong> <?= htmlspecialchars($pi_error) ?></div>
    <?php endif; ?>

    <!-- ── Order summary grouped by destination ───────────────────── -->
    <div style="background:#2a2630; border-radius:8px; padding:1em 1.5em; margin-bottom:1.5em;">
      <h4 style="margin-top:0; display:flex; justify-content:space-between;">
        Order Summary
        <a href="checkout_shipping.php" style="font-size:0.75em; font-weight:normal;">← Change shipping</a>
      </h4>

      <?php foreach ($groups as $group_key => $group):
        $ship_result = $shipping_costs[$group_key] ?? null;
        $tax_result  = $tax_by_group[$group_key]   ?? null;
      ?>
        <div style="margin-bottom:1.25em;">

          <div style="font-size:0.85em; color:#aaa; margin-bottom:.4em;
                      border-top:1px solid #444; padding-top:.6em;">
            <?= $group['label'] ?>
          </div>

          <?php foreach ($group['pieces'] as $p): ?>
            <div style="display:flex; justify-content:space-between; margin-bottom:.3em; padding-left:.75em;">
              <span>
                <?= htmlspecialchars($p['name']) ?>
                <small style="color:#aaa;">(<?= htmlspecialchars($p['canvas_size']) ?>)</small>
              </span>
              <span>$<?= number_format((float)$p['price'], 2) ?></span>
            </div>
          <?php endforeach; ?>

          <?php if ($ship_result): ?>
            <!-- Shipping line -->
            <div style="display:flex; justify-content:space-between; padding-left:.75em;
                        font-size:0.9em; color:#aaa; margin-top:.4em;">
              <span>Shipping<?= $ship_result['estimated'] ? ' <em>(est.)</em>' : '' ?></span>
              <span>$<?= number_format($ship_result['cost'], 2) ?></span>
            </div>

                    <!-- Insurance line — always shown, live amount or explanation -->
        <?php if ($ship_result): ?>
          <div style="display:flex; justify-content:space-between; padding-left:.75em;
                      font-size:0.9em; color:#aaa; margin-top:.2em;">
            <span>Insurance</span>
            <?php if ($ship_result['estimated']): ?>
              <span style="font-style:italic; font-size:0.9em;">
                <?= htmlspecialchars($ship_result['insurance_note'] ?? 'confirmed at shipment') ?>
              </span>
            <?php elseif ($ship_result['insurance'] > 0): ?>
              <span>$<?= number_format($ship_result['insurance'], 2) ?></span>
            <?php else: ?>
              <span style="font-style:italic;">Included in Priority Mail base rate</span>
            <?php endif; ?>
          </div>
        <?php elseif ($group_key === 'pickup'): ?>
          <div style="font-size:0.9em; color:#aaa; padding-left:.75em; margin-top:.2em;">
            Insurance: <em>N/A (Local Pickup)</em>
          </div>
        <?php endif; ?>

            <!-- Per-package breakdown -->
            <?php foreach ($ship_result['packages'] as $i => $pkg): ?>
              <div style="font-size:0.75em; color:#666; padding-left:.75em; margin-top:.2em;">
                <?php if ($pkg['oversized']): ?>
                  ⚠ Package <?= $i + 1 ?>: oversized — Anni will arrange shipping
                <?php else: ?>
                  <?php if (count($ship_result['packages']) > 1): ?>Package <?= $i + 1 ?>: <?php endif; ?>
                  <?= $pkg['box']['label'] ?> box &mdash;
                  <?= count($pkg['pieces']) ?> piece<?= count($pkg['pieces']) > 1 ? 's' : '' ?>,
                  <?= round($pkg['total_weight'], 1) ?> oz
                <?php endif; ?>
              </div>
            <?php endforeach; ?>

            <?php if ($ship_result['estimated']): ?>
              <div style="font-size:0.75em; color:#555; padding-left:.75em; margin-top:.2em;">
                Shipping estimate — final rate confirmed before shipment.
              </div>
            <?php endif; ?>

          <?php elseif ($group_key === 'pickup'): ?>
            <div style="font-size:0.9em; color:#aaa; padding-left:.75em; margin-top:.4em;">
              Shipping: <em>waived</em>
            </div>
          <?php endif; ?>

          <!-- Tax line for this group -->
          <?php if ($tax_result && $tax_result['success'] && $tax_result['tax_amount'] > 0): ?>
            <div style="display:flex; justify-content:space-between; padding-left:.75em;
                        font-size:0.9em; color:#aaa; margin-top:.2em;">
              <span>Sales tax (<?= $tax_result['rate_pct'] ?>)</span>
              <span>$<?= number_format($tax_result['tax_amount'], 2) ?></span>
            </div>
          <?php elseif ($tax_result && !$tax_result['success']): ?>
            <div style="font-size:0.75em; color:#ffcc00; padding-left:.75em; margin-top:.2em;">
              ⚠ Tax lookup unavailable — will be confirmed before charge
            </div>
          <?php elseif ($tax_result && isset($tax_result['note'])): ?>
            <div style="font-size:0.75em; color:#aaa; padding-left:.75em; margin-top:.2em;">
              <?= htmlspecialchars($tax_result['note']) ?>
            </div>
          <?php endif; ?>

        </div>
      <?php endforeach; ?>

      <hr style="border-color:#555; margin:.5em 0;">
      <div style="display:flex; justify-content:space-between; color:#aaa;">
        <span>Subtotal</span>
        <span>$<?= number_format($subtotal, 2) ?></span>
      </div>
      <?php if ($ship_total > 0): ?>
      <div style="display:flex; justify-content:space-between; color:#aaa; margin-top:.3em;">
        <span>Shipping<?= array_reduce($shipping_costs, fn($c, $r) => $c || $r['estimated'], false) ? ' (est.)' : '' ?></span>
        <span>$<?= number_format($ship_total, 2) ?></span>
      </div>
      <?php if (!empty($shipping_costs)): ?>
   <!-- API Debug section hidden -->
<?php endif; ?>
      <?php endif; ?>
      <?php if ($insurance_total > 0): ?>
      <div style="display:flex; justify-content:space-between; color:#aaa; margin-top:.3em;">
        <span>Insurance (incl. in shipping)</span>
        <span>$<?= number_format($insurance_total, 2) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($tax_total > 0): ?>
      <div style="display:flex; justify-content:space-between; color:#aaa; margin-top:.3em;">
        <span>Sales tax</span>
        <span>$<?= number_format($tax_total, 2) ?></span>
      </div>
      <?php endif; ?>
      <div style="display:flex; justify-content:space-between; font-size:1.2em; margin-top:.5em;">
        <strong>Total</strong>
        <strong>$<?= number_format($total, 2) ?></strong>
      </div>
    </div>

    <!-- ── Pickup payment toggle ──────────────────────────────────── -->
    <?php if ($any_pickup): ?>
    <div style="background:#2a2630; border-radius:8px; padding:1em 1.5em; margin-bottom:1.5em;">
      <h4 style="margin-top:0;">Pickup Payment</h4>
      <label style="display:flex; align-items:flex-start; gap:.75em; cursor:pointer; margin-bottom:.6em;">
        <input type="radio" name="pickup_payment" id="pay-at-pickup" value="at_pickup"
               style="margin-top:3px;" <?= $all_pickup ? 'checked' : '' ?>>
        <div>
          <strong>Pay at pickup</strong>
          <p style="margin:.2em 0 0; color:#ccc; font-size:0.9em;">
            Cash, card, or Venmo in person. We'll confirm details by email.
          </p>
        </div>
      </label>
      <label style="display:flex; align-items:flex-start; gap:.75em; cursor:pointer;">
        <input type="radio" name="pickup_payment" id="pay-now-pickup" value="pay_now"
               style="margin-top:3px;" <?= !$all_pickup ? 'checked' : '' ?>>
        <div>
          <strong>Pay now by card</strong>
          <p style="margin:.2em 0 0; color:#ccc; font-size:0.9em;">
            Charge everything today for $<?= number_format($total, 2) ?>.
          </p>
        </div>
      </label>
    </div>
    <?php endif; ?>

    <!-- ── Contact / billing info ─────────────────────────────────── -->
    <form id="payment-form">

      <?php if ($billing_collected): ?>
        <div style="background:#2a2630; border-radius:8px; padding:1em 1.5em; margin-bottom:1.5em;">
          <h4 style="margin-top:0; display:flex; justify-content:space-between;">
            Contact &amp; Billing
            <a href="checkout_shipping.php" style="font-size:0.75em; font-weight:normal;">Change</a>
          </h4>
          <p style="margin:0;">
            <?= htmlspecialchars($billing['name']   ?? '') ?><br>
            <?= htmlspecialchars($billing['email']  ?? '') ?><br>
            <?= htmlspecialchars($billing['street'] ?? '') ?>,
            <?= htmlspecialchars($billing['city']   ?? '') ?>,
            <?= htmlspecialchars($billing['state']  ?? '') ?>
            <?= htmlspecialchars($billing['zip']    ?? '') ?>
          </p>
        </div>
      <?php else: ?>
        <fieldset style="margin-bottom:1.5em;">
          <legend>Contact &amp; Billing Details</legend>
          <div class="form-group">
            <label class="control-label">Full Name</label>
            <input type="text" id="billing-name" class="form-control" required
                   placeholder="Name as it appears on your card"
                   value="<?= htmlspecialchars($billing['name'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="control-label">Email Address</label>
            <input type="email" id="billing-email" class="form-control" required
                   placeholder="Order confirmation sent here"
                   value="<?= htmlspecialchars($billing['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="control-label">Billing Street Address</label>
            <input type="text" id="billing-street" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="control-label">City</label>
            <input type="text" id="billing-city" class="form-control" required>
          </div>
          <div style="display:flex; gap:1em;">
            <div class="form-group" style="flex:1;">
              <label class="control-label">State</label>
              <input type="text" id="billing-state" class="form-control" maxlength="2" placeholder="GA" required>
            </div>
            <div class="form-group" style="flex:1;">
              <label class="control-label">ZIP</label>
              <input type="text" id="billing-zip" class="form-control" maxlength="10" required>
            </div>
          </div>
        </fieldset>
      <?php endif; ?>

      <fieldset id="card-fieldset" style="<?= $all_pickup ? 'display:none;' : '' ?>">
        <legend>Card Details</legend>
        <div id="card-element" style="background:#fff; padding:12px; border-radius:4px; margin-bottom:.75em;"></div>
        <div id="card-errors" role="alert" style="color:#ff6b6b; min-height:1.25em; margin-bottom:.5em;"></div>
      </fieldset>

      <div class="panel panel-default" style="margin-top:1.5em;">
        <div class="panel-heading"><h4 class="panel-title">Order Conditions</h4></div>
        <div class="panel-body">
          <div id="charge-summary"<?= $all_pickup ? ' style="display:none;"' : '' ?>>
            <p>Your card will be charged <strong>$<?= number_format($total, 2) ?></strong>.
               Appears on your statement as <strong>STUDIO ANNI</strong>.</p>
          </div>
          <p>We'll email you within 48 hours to confirm order details.</p>
          <p><strong class="text-warning">By submitting you agree:</strong><br>
            <strong>A.</strong> No refunds on shipped works unless damaged or lost in transit.<br>
            <strong>B.</strong> Studio Anni may use images of purchased pieces for marketing.<br>
            <strong>C.</strong> No commercial reproduction without consent.
            <em>(Displaying in your home or store is fine — no prints without Anni's permission!)</em>
          </p>
        </div>
      </div>

      <div style="text-align:center; margin-top:1.25em;">
        <button id="submit-btn" class="purchaseButton" type="submit">
          <?= $all_pickup ? 'Reserve &amp; Arrange Pickup' : 'Complete Purchase' ?>
        </button>
        <div id="pay-spinner" style="display:none; margin-top:.75em; color:#ccc;">Processing...</div>
      </div>
      <div id="card-errors-general" role="alert"
           style="color:#ff6b6b; margin-top:.75em; text-align:center;"></div>

    </form>

    <div id="payment-success" style="display:none; text-align:center; padding:2em 0;">
      <h2>Thank You!</h2>
      <p class="lead" id="success-msg"></p>
      <p>We'll be in touch within 48 hours. — Anni &amp; Mike</p>
      <a href="gallery.php" class="purchaseButton" style="margin-top:1em;">Back to Gallery</a>
    </div>

  </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
const ANY_PICKUP        = <?= $any_pickup ? 'true' : 'false' ?>;
const ALL_PICKUP        = <?= $all_pickup ? 'true' : 'false' ?>;
const BILLING_COLLECTED = <?= $billing_collected ? 'true' : 'false' ?>;
const CLIENT_SECRET     = <?= json_encode($client_secret) ?>;

const stripe   = Stripe(<?= json_encode(STRIPE_PUBLIC_KEY) ?>);
const elements = stripe.elements();
const card     = elements.create('card', {
  style: {
    base: {
      color: '#ffffff', fontFamily: "'Raleway', sans-serif", fontSize: '16px',
      '::placeholder': { color: '#aab7c4' }
    },
    invalid: { color: '#ff6b6b', iconColor: '#ff6b6b' }
  }
});
card.mount('#card-element');
card.on('change', e => {
  document.getElementById('card-errors').textContent = e.error ? e.error.message : '';
});

if (ANY_PICKUP) {
  const cardFS     = document.getElementById('card-fieldset');
  const chargeSumm = document.getElementById('charge-summary');
  const btn        = document.getElementById('submit-btn');
  document.querySelectorAll('[name="pickup_payment"]').forEach(r => {
    r.addEventListener('change', () => {
      const hide = document.getElementById('pay-at-pickup').checked && ALL_PICKUP;
      if (cardFS)     cardFS.style.display     = hide ? 'none' : '';
      if (chargeSumm) chargeSumm.style.display = hide ? 'none' : '';
      btn.textContent = hide ? 'Reserve & Arrange Pickup' : 'Complete Purchase';
    });
  });
}

function getBillingDetails() {
  if (BILLING_COLLECTED) {
    return {
      name:  <?= json_encode($billing['name']   ?? '') ?>,
      email: <?= json_encode($billing['email']  ?? '') ?>,
      address: {
        line1:       <?= json_encode($billing['street'] ?? '') ?>,
        city:        <?= json_encode($billing['city']   ?? '') ?>,
        state:       <?= json_encode($billing['state']  ?? '') ?>,
        postal_code: <?= json_encode($billing['zip']    ?? '') ?>,
        country: 'US'
      }
    };
  }
  return {
    name:  document.getElementById('billing-name')?.value  ?? '',
    email: document.getElementById('billing-email')?.value ?? '',
    address: {
      line1:       document.getElementById('billing-street')?.value ?? '',
      city:        document.getElementById('billing-city')?.value   ?? '',
      state:       document.getElementById('billing-state')?.value  ?? '',
      postal_code: document.getElementById('billing-zip')?.value    ?? '',
      country: 'US'
    }
  };
}

function getCustomerData() {
  const b = getBillingDetails();
  return { name: b.name, email: b.email, street: b.address.line1,
           city: b.address.city, state: b.address.state, zip: b.address.postal_code };
}

document.getElementById('payment-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn        = document.getElementById('submit-btn');
  const spinner    = document.getElementById('pay-spinner');
  const errGeneral = document.getElementById('card-errors-general');
  btn.disabled = true; spinner.style.display = 'block'; errGeneral.textContent = '';

  const payAtPickup = ALL_PICKUP && document.getElementById('pay-at-pickup')?.checked;
  const customer    = getCustomerData();

  if (payAtPickup) {
    const res  = await fetch('confirm_order.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ payment_intent_id: null, pay_at_pickup: true, customer })
    });
    const data = await res.json();
    if (data.status !== 'ok') {
      errGeneral.textContent = 'Something went wrong. Please contact us directly.';
      btn.disabled = false; spinner.style.display = 'none'; return;
    }
    document.getElementById('success-msg').textContent =
      "Your pickup reservation is confirmed! We'll be in touch to arrange the details.";
    document.getElementById('payment-form').style.display    = 'none';
    document.getElementById('payment-success').style.display = 'block';
    window.scrollTo(0, 0); return;
  }

  const { error, paymentIntent } = await stripe.confirmCardPayment(CLIENT_SECRET, {
    payment_method: { card, billing_details: getBillingDetails() }
  });

  if (error) {
    errGeneral.textContent = error.message;
    btn.disabled = false; spinner.style.display = 'none'; return;
  }

  if (paymentIntent.status === 'succeeded') {
    const res  = await fetch('confirm_order.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ payment_intent_id: paymentIntent.id, pay_at_pickup: false, customer })
    });
    const data = await res.json();
    if (data.status !== 'ok') console.error(data.message);
    document.getElementById('success-msg').textContent = 'Your payment was successful!';
    document.getElementById('payment-form').style.display    = 'none';
    document.getElementById('payment-success').style.display = 'block';
    window.scrollTo(0, 0);
  }
});
</script>

<?php require_once 'footer.php'; ?>