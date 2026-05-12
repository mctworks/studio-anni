<?php
session_start();
require_once 'southwinds/phoenixeyes.php';
require_once 'southwinds/shipping_utils.php';

if (empty($_SESSION['cart'])) {
    header('Location: gallery.php');
    exit;
}

$pieces_in_cart  = $_SESSION['cart'];
$array_to_qmarks = implode(',', array_fill(0, count($pieces_in_cart), '?'));
$stmt            = $fy->prepare("SELECT * FROM works WHERE pieceID IN ($array_to_qmarks)");
$stmt->execute(array_keys($pieces_in_cart));
$cart_pieces     = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0.00;
foreach ($cart_pieces as $p) $subtotal += (float)$p['price'];

// ── Handle POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors            = [];
    $shipping_by_piece = [];
    $methods           = $_POST['shipping_method'] ?? [];

    // Determine if any item ships to billing — if so, billing address is required here
    $any_ships_to_billing = in_array('same_as_billing', array_values($methods));

    // Collect + validate billing info (always need name/email for card; address needed if ships_to_billing)
    $bill_name  = trim(filter_input(INPUT_POST, 'bill_name',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $bill_email = trim(filter_input(INPUT_POST, 'bill_email', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $bill_street = trim(filter_input(INPUT_POST, 'bill_street', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $bill_city   = trim(filter_input(INPUT_POST, 'bill_city',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $bill_state  = trim(filter_input(INPUT_POST, 'bill_state',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $bill_zip    = trim(filter_input(INPUT_POST, 'bill_zip',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

    if (!$bill_name)  $errors[] = 'Your name is required.';
    if (!$bill_email || !filter_var($bill_email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'A valid email address is required.';

    if ($any_ships_to_billing) {
        if (!$bill_street) $errors[] = 'Billing street address is required.';
        if (!$bill_city)   $errors[] = 'Billing city is required.';
        if (!$bill_state || $bill_state === '0')  $errors[] = 'Billing state is required.';
        if (!$bill_zip)    $errors[] = 'Billing ZIP is required.';
    }

    // Validate per-piece shipping
    foreach ($cart_pieces as $p) {
        $pid    = $p['pieceID'];
        $method = $methods[$pid] ?? 'same_as_billing';
        $entry  = ['method' => $method];

        if ($method === 'different_address') {
            $s = trim(filter_input(INPUT_POST, "ship_street_$pid", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $c = trim(filter_input(INPUT_POST, "ship_city_$pid",   FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $st= trim(filter_input(INPUT_POST, "ship_state_$pid",  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $z = trim(filter_input(INPUT_POST, "ship_zip_$pid",    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $n = trim(filter_input(INPUT_POST, "ship_name_$pid",   FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            if (!$s || !$c || !$st || $st === '0' || !$z)
                $errors[] = 'Complete shipping address required for "' . htmlspecialchars($p['name']) . '".';
            $entry += ['name' => $n, 'street' => $s, 'city' => $c, 'state' => $st, 'zip' => $z];
        } elseif ($method === 'local_pickup') {
            $entry['pickup_notes']  = trim(filter_input(INPUT_POST, "pickup_notes_$pid", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
            $entry['cost_override'] = 0.00;
        }

        $shipping_by_piece[$pid] = $entry;
    }

    if (empty($errors)) {
        $_SESSION['billing'] = [
            'name'   => $bill_name,
            'email'  => $bill_email,
            'street' => $bill_street,
            'city'   => $bill_city,
            'state'  => $bill_state,
            'zip'    => $bill_zip,
            'collected_here' => $any_ships_to_billing, // full address vs name/email only
        ];
        $_SESSION['shipping'] = $shipping_by_piece;
        header('Location: purchaseform.php');
        exit;
    }

    // Re-populate on error
    $prev_bill = compact('bill_name','bill_email','bill_street','bill_city','bill_state','bill_zip');
    $prev_ship = $shipping_by_piece;
} else {
    $errors    = [];
    $prev_bill = $_SESSION['billing'] ?? [];
    $prev_ship = $_SESSION['shipping'] ?? [];
}

include 'head.php';
include 'nav.php';

// State list helper
function state_options($selected = '') {
    $states = ['AL'=>'Alabama','AK'=>'Alaska','AZ'=>'Arizona','AR'=>'Arkansas',
               'CA'=>'California','CO'=>'Colorado','CT'=>'Connecticut','DE'=>'Delaware',
               'DC'=>'District of Columbia','FL'=>'Florida','GA'=>'Georgia','HI'=>'Hawaii',
               'ID'=>'Idaho','IL'=>'Illinois','IN'=>'Indiana','IA'=>'Iowa','KS'=>'Kansas',
               'KY'=>'Kentucky','LA'=>'Louisiana','ME'=>'Maine','MD'=>'Maryland',
               'MA'=>'Massachusetts','MI'=>'Michigan','MN'=>'Minnesota','MS'=>'Mississippi',
               'MO'=>'Missouri','MT'=>'Montana','NE'=>'Nebraska','NV'=>'Nevada',
               'NH'=>'New Hampshire','NJ'=>'New Jersey','NM'=>'New Mexico','NY'=>'New York',
               'NC'=>'North Carolina','ND'=>'North Dakota','OH'=>'Ohio','OK'=>'Oklahoma',
               'OR'=>'Oregon','PA'=>'Pennsylvania','RI'=>'Rhode Island','SC'=>'South Carolina',
               'SD'=>'South Dakota','TN'=>'Tennessee','TX'=>'Texas','UT'=>'Utah',
               'VT'=>'Vermont','VA'=>'Virginia','WA'=>'Washington','WV'=>'West Virginia',
               'WI'=>'Wisconsin','WY'=>'Wyoming'];
    $out = '<option value="0">— Select State —</option>';
    foreach ($states as $abbr => $name) {
        $sel  = ($selected === $abbr) ? ' selected' : '';
        $out .= "<option value=\"$abbr\"$sel>$name</option>";
    }
    return $out;
}
?>

<div class="main-container">
  <div class="col-sm-8 col-sm-offset-2">

    <h1 class="gallery-header">Shipping &amp; Contact</h1>

    <!-- Order summary strip -->
    <div style="background:#2a2630; border-radius:8px; padding:1em 1.5em; margin-bottom:2em;
                display:flex; justify-content:space-between; align-items:center;">
      <span><?= count($cart_pieces) ?> item<?= count($cart_pieces) > 1 ? 's' : '' ?> in cart</span>
      <strong>Subtotal: $<?= number_format($subtotal, 2) ?></strong>
      <a href="viewcart.php" style="font-size:0.85em;">Edit cart</a>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="checkout_shipping.php" id="shipping-form">

      <!-- ── Per-item shipping options ──────────────────────────────── -->
      <h4 style="margin-bottom:1em;">Shipping options</h4>

      <?php foreach ($cart_pieces as $p):
        $pid      = $p['pieceID'];
        $selected = $prev_ship[$pid]['method'] ?? 'same_as_billing';
      ?>
      <div style="background:#2a2630; border-radius:8px; padding:1.25em 1.5em; margin-bottom:1.25em;">

        <div style="display:flex; align-items:center; gap:1em; margin-bottom:1em;">
          <img src="gallery/<?= htmlspecialchars($p['image']) ?>.jpg"
               width="60" height="60" style="object-fit:cover; border-radius:4px; flex-shrink:0;"
               alt="<?= htmlspecialchars($p['name']) ?>">
          <div>
            <strong><?= htmlspecialchars($p['name']) ?></strong><br>
            <small style="color:#aaa;"><?= htmlspecialchars($p['canvas_size']) ?></small>
          </div>
        </div>

        <!-- Option 1 -->
        <label style="display:flex; align-items:flex-start; gap:.75em; cursor:pointer; margin-bottom:.6em;">
          <input type="radio" name="shipping_method[<?= $pid ?>]" value="same_as_billing"
                 class="ship-radio" data-pid="<?= $pid ?>"
                 <?= $selected === 'same_as_billing' ? 'checked' : '' ?>
                 style="margin-top:3px;">
          <div>
            <strong><img src="usps_logo.svg" width="20" alt="United States Postal Service"> Ship to billing address (via USPS)</strong>
            <p style="margin:.2em 0 0; color:#ccc; font-size:0.9em;">
              Uses the billing address entered below.
            </p>
          </div>
        </label>

        <!-- Option 2 -->
        <label style="display:flex; align-items:flex-start; gap:.75em; cursor:pointer; margin-bottom:.6em;">
          <input type="radio" name="shipping_method[<?= $pid ?>]" value="different_address"
                 class="ship-radio" data-pid="<?= $pid ?>"
                 <?= $selected === 'different_address' ? 'checked' : '' ?>
                 style="margin-top:3px;">
          <div style="width:100%;">
            <strong><img src="usps_logo.svg" width="20" alt="United States Postal Service"> Ship to a different address (via USPS)</strong>
            <div id="diff-<?= $pid ?>" style="margin-top:.75em;<?= $selected === 'different_address' ? '' : ' display:none;' ?>">
              <div class="form-group" style="margin-bottom:.5em;">
                <input type="text" name="ship_name_<?= $pid ?>" class="form-control"
                       placeholder="Recipient name (optional)"
                       value="<?= htmlspecialchars($prev_ship[$pid]['name'] ?? '') ?>">
              </div>
              <div class="form-group" style="margin-bottom:.5em;">
                <input type="text" name="ship_street_<?= $pid ?>" class="form-control"
                       placeholder="Street address"
                       value="<?= htmlspecialchars($prev_ship[$pid]['street'] ?? '') ?>">
              </div>
              <div class="form-group" style="margin-bottom:.5em;">
                <input type="text" name="ship_city_<?= $pid ?>" class="form-control"
                       placeholder="City"
                       value="<?= htmlspecialchars($prev_ship[$pid]['city'] ?? '') ?>">
              </div>
              <div style="display:flex; gap:1em;">
                <div class="form-group" style="flex:1; margin-bottom:.5em;">
                  <select name="ship_state_<?= $pid ?>" class="form-control">
                    <?= state_options($prev_ship[$pid]['state'] ?? '') ?>
                  </select>
                </div>
                <div class="form-group" style="flex:1; margin-bottom:.5em;">
                  <input type="text" name="ship_zip_<?= $pid ?>" class="form-control"
                         placeholder="ZIP" maxlength="10"
                         value="<?= htmlspecialchars($prev_ship[$pid]['zip'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>
        </label>

        <!-- Option 3 -->
        <label style="display:flex; align-items:flex-start; gap:.75em; cursor:pointer;">
          <input type="radio" name="shipping_method[<?= $pid ?>]" value="local_pickup"
                 class="ship-radio" data-pid="<?= $pid ?>"
                 <?= $selected === 'local_pickup' ? 'checked' : '' ?>
                 style="margin-top:3px;">
          <div style="width:100%;">
            <strong>Local pickup</strong>
            <p style="margin:.2em 0 0; color:#ccc; font-size:0.9em;">
              Lilburn, GA area. Shipping fee waived.
            </p>
            <div id="pickup-<?= $pid ?>" style="margin-top:.75em;<?= $selected === 'local_pickup' ? '' : ' display:none;' ?>">
              <div id="pickup-notice-<?= $pid ?>" style="display:none; background:#5c3a00;
                   border:1px solid #ffcc00; border-radius:6px; padding:.75em 1em;
                   margin-bottom:.75em; color:#ffcc00;">
                <strong>Heads up:</strong> Your ZIP appears to be more than 20 miles from
                our location in Lilburn, GA. You're still welcome to arrange pickup!
              </div>
              <div class="form-group" style="margin-bottom:.5em;">
                <input type="text" id="pickup-zip-<?= $pid ?>" class="form-control pickup-zip"
                       data-pid="<?= $pid ?>" maxlength="5"
                       placeholder="Your ZIP (optional — proximity check)"
                       style="max-width:160px;">
              </div>
              <textarea name="pickup_notes_<?= $pid ?>" class="form-control" rows="3"
                placeholder="Availability, preferred contact method, any pickup details..."
              ><?= htmlspecialchars($prev_ship[$pid]['pickup_notes'] ?? '') ?></textarea>
            </div>
          </div>
        </label>

      </div>
      <?php endforeach; ?>

      <!-- ── Billing / contact info ────────────────────────────────── -->
      <div id="billing-section" style="background:#2a2630; border-radius:8px;
           padding:1.25em 1.5em; margin-bottom:1.5em; margin-top:1.5em;">

        <h4 style="margin-top:0;" id="billing-section-title">Your Details</h4>

        <div class="form-group">
          <label class="control-label">Full Name <span style="color:#ff6b6b;">*</span></label>
          <input type="text" name="bill_name" class="form-control" required
                 placeholder="Your full name"
                 value="<?= htmlspecialchars($prev_bill['bill_name'] ?? $prev_bill['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="control-label">Email Address <span style="color:#ff6b6b;">*</span></label>
          <input type="email" name="bill_email" class="form-control" required
                 placeholder="Order confirmation sent here"
                 value="<?= htmlspecialchars($prev_bill['bill_email'] ?? $prev_bill['email'] ?? '') ?>">
        </div>

        <!-- Address fields — shown when any item ships to billing -->
        <div id="billing-address-fields">
          <div class="form-group" style="margin-top:.75em;">
            <label class="control-label">Street Address <span style="color:#ff6b6b;">*</span></label>
            <input type="text" name="bill_street" class="form-control"
                   placeholder="Street address"
                   value="<?= htmlspecialchars($prev_bill['bill_street'] ?? $prev_bill['street'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="control-label">City <span style="color:#ff6b6b;">*</span></label>
            <input type="text" name="bill_city" class="form-control"
                   placeholder="City"
                   value="<?= htmlspecialchars($prev_bill['bill_city'] ?? $prev_bill['city'] ?? '') ?>">
          </div>
          <div style="display:flex; gap:1em;">
            <div class="form-group" style="flex:1;">
              <label class="control-label">State <span style="color:#ff6b6b;">*</span></label>
              <select name="bill_state" class="form-control">
                <?= state_options($prev_bill['bill_state'] ?? $prev_bill['state'] ?? '') ?>
              </select>
            </div>
            <div class="form-group" style="flex:1;">
              <label class="control-label">ZIP <span style="color:#ff6b6b;">*</span></label>
              <input type="text" name="bill_zip" class="form-control"
                     placeholder="ZIP" maxlength="10"
                     value="<?= htmlspecialchars($prev_bill['bill_zip'] ?? $prev_bill['zip'] ?? '') ?>">
            </div>
          </div>
          <p style="font-size:0.85em; color:#aaa; margin-top:.5em;">
            * Required when shipping to billing address.
          </p>
        </div>

      </div>

      <div style="display:flex; justify-content:space-between; margin-top:1em; margin-bottom: 5em;">
        <a href="viewcart.php" class="purchaseButton">← Back to Cart</a>
        <button type="submit" class="purchaseButton">Continue to Payment →</button>
      </div>

    </form>
  </div>
</div>

<script>
// ── Show/hide address fields based on whether any item ships to billing ────
const billingFields = document.getElementById('billing-address-fields');
const billingTitle  = document.getElementById('billing-section-title');

function updateBillingVisibility() {
  const anyBilling = [...document.querySelectorAll('.ship-radio:checked')]
    .some(r => r.value === 'same_as_billing');
  billingFields.style.display = anyBilling ? '' : 'none';
  billingTitle.textContent    = anyBilling ? 'Billing & Contact Details' : 'Contact Details';
}

// ── Show/hide per-piece address sub-forms ─────────────────────────────────
document.querySelectorAll('.ship-radio').forEach(radio => {
  radio.addEventListener('change', () => {
    const pid  = radio.dataset.pid;
    const diff   = document.getElementById(`diff-${pid}`);
    const pickup = document.getElementById(`pickup-${pid}`);
    if (diff)   diff.style.display   = radio.value === 'different_address' ? '' : 'none';
    if (pickup) pickup.style.display = radio.value === 'local_pickup'      ? '' : 'none';
    if (radio.value === 'local_pickup') {
      const zipEl = document.getElementById(`pickup-zip-${pid}`);
      if (zipEl && zipEl.value.length === 5) checkDistance(pid, zipEl.value);
    }
    updateBillingVisibility();
  });
});

// Run on load
updateBillingVisibility();

// ── Distance check ─────────────────────────────────────────────────────────
const STUDIO_LAT = 33.8898, STUDIO_LNG = -84.1402, MILES_LIMIT = 20;

function haversine(la1, lo1, la2, lo2) {
  const R = 3958.8, dL = (la2-la1)*Math.PI/180, dO = (lo2-lo1)*Math.PI/180;
  const a = Math.sin(dL/2)**2 + Math.cos(la1*Math.PI/180)*Math.cos(la2*Math.PI/180)*Math.sin(dO/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function checkDistance(pid, zip) {
  const notice = document.getElementById(`pickup-notice-${pid}`);
  if (!notice || zip.length !== 5 || !/^\d{5}$/.test(zip)) {
    if (notice) notice.style.display = 'none';
    return;
  }
  fetch(`https://api.zippopotam.us/us/${zip}`)
    .then(r => r.ok ? r.json() : null)
    .then(d => {
      if (!d) return;
      const miles = haversine(parseFloat(d.places[0].latitude), parseFloat(d.places[0].longitude), STUDIO_LAT, STUDIO_LNG);
      notice.style.display = miles > MILES_LIMIT ? '' : 'none';
    })
    .catch(() => {});
}

document.querySelectorAll('.pickup-zip').forEach(el => {
  el.addEventListener('input', e => checkDistance(e.target.dataset.pid, e.target.value));
});
</script>

<?php require_once 'footer.php'; ?>