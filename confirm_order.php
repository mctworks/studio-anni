<?php
require_once 'vendor/autoload.php';
require_once 'pconfig.php';
require_once 'southwinds/phoenixeyes.php';
require_once 'southwinds/mailer.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$input         = json_decode(file_get_contents('php://input'), true);
$pi_id         = $input['payment_intent_id'] ?? null;
$pay_at_pickup = (bool)($input['pay_at_pickup'] ?? false);
$customer      = $input['customer'] ?? [];

// ── Stripe verification ────────────────────────────────────────────────────
if (!$pay_at_pickup) {
    if (!$pi_id) {
        echo json_encode(['status' => 'error', 'message' => 'Missing payment intent']);
        exit;
    }
    $stripe = new \Stripe\StripeClient(STRIPE_PRIVATE_KEY);
    try {
        $intent = $stripe->paymentIntents->retrieve($pi_id);
        if ($intent->status !== 'succeeded') {
            echo json_encode(['status' => 'error', 'message' => 'Payment not confirmed by Stripe']);
            exit;
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

$pieces_in_cart    = $_SESSION['cart'] ?? [];
$shipping_by_piece = $_SESSION['shipping'] ?? [];

if (empty($pieces_in_cart)) {
    echo json_encode(['status' => 'error', 'message' => 'Empty cart']);
    exit;
}

$suffix  = $pi_id ? substr($pi_id, -6) : strtoupper(substr(md5(uniqid()), 0, 6));
$purchID = ($pay_at_pickup ? 'P' : 'G') . date('ymj') . '-' . $suffix;

// ── Insert one row per piece with its own shipping address ─────────────────
$stmt = $fy->prepare(
    "INSERT INTO purchases
        (purchID, pieceID, cust_name, cust_street, cust_city, cust_state, cust_zip, cust_email,
         ship_name, ship_street, ship_city, ship_state, ship_zip, payment_intent)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

// ── Get piece names for the email ──────────────────────────────────────────
$piece_ids_list = implode(',', array_map('intval', array_keys($pieces_in_cart)));
$pieces_query   = $fy->prepare("SELECT pieceID, name FROM works WHERE pieceID IN ($piece_ids_list)");
$pieces_query->execute();
$piece_names    = array_column($pieces_query->fetchAll(PDO::FETCH_ASSOC), 'name', 'pieceID');

// ── Retrieve session data for fee breakdown ──────────────────────────────────
$shipping_costs = $_SESSION['shipping_costs']  ?? [];
$tax_by_group   = $_SESSION['tax_by_group']    ?? [];
$groups_detail  = $_SESSION['groups_detail']   ?? [];
$totals         = $_SESSION['totals']          ?? [];

$email_lines = [];

foreach ($pieces_in_cart as $piece_id => $qty) {
    $piece_name = $piece_names[$piece_id] ?? "Piece ID: $piece_id";
    $ship_info  = $shipping_by_piece[$piece_id] ?? ['method' => 'same_as_billing'];
    $method     = $ship_info['method'];

    if ($method === 'local_pickup') {
        $ship_address = 'LOCAL PICKUP - Lilburn, GA';
    } elseif ($method === 'different_address') {
        $ship_address = ($ship_info['name'] ?? '') . ', '
                      . ($ship_info['street'] ?? '') . ', '
                      . ($ship_info['city'] ?? '') . ', '
                      . ($ship_info['state'] ?? '') . ' '
                      . ($ship_info['zip'] ?? '');
    } else {
        $ship_address = ($customer['name'] ?? '') . ', '
                      . ($customer['street'] ?? '') . ', '
                      . ($customer['city'] ?? '') . ', '
                      . ($customer['state'] ?? '') . ' '
                      . ($customer['zip'] ?? '');
    }

    $pickup_note = '';
    if ($method === 'local_pickup' && !empty($ship_info['pickup_notes'])) {
        $pickup_note = ' [Notes: ' . $ship_info['pickup_notes'] . ']';
    }

    $email_lines[] = "  • " . htmlspecialchars($piece_name) . " → " . $ship_address . $pickup_note;

    if ($method === 'local_pickup') {
        $ship_name   = 'LOCAL PICKUP';
        $ship_street = '';
        $ship_city   = 'Lilburn';
        $ship_state  = 'GA';
        $ship_zip    = '30047';
    } elseif ($method === 'different_address') {
        $ship_name   = $ship_info['name']   ?? '';
        $ship_street = $ship_info['street'] ?? '';
        $ship_city   = $ship_info['city']   ?? '';
        $ship_state  = $ship_info['state']  ?? '';
        $ship_zip    = $ship_info['zip']    ?? '';
    } else {
        $ship_name   = $customer['name']   ?? '';
        $ship_street = $customer['street'] ?? '';
        $ship_city   = $customer['city']   ?? '';
        $ship_state  = $customer['state']  ?? '';
        $ship_zip    = $customer['zip']    ?? '';
    }

    try {
        $stmt->execute([
            $purchID,
            $piece_id,
            $customer['name']   ?? '',
            $customer['street'] ?? '',
            $customer['city']   ?? '',
            $customer['state']  ?? '',
            $customer['zip']    ?? '',
            $customer['email']  ?? '',
            $ship_name,
            $ship_street,
            $ship_city,
            $ship_state,
            $ship_zip,
            $pi_id,
        ]);
    } catch (PDOException $e) {
        error_log('confirm_order DB error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Mark piece as PROCESSING
    $upd = $fy->prepare("UPDATE works SET specialstatus = 'PROCESSING' WHERE pieceID = ?");
    $upd->execute([$piece_id]);
}

// ── Email Anni ─────────────────────────────────────────────────────────────
$payment_line = $pay_at_pickup ? "Payment: PAY AT PICKUP" : "Payment Intent: $pi_id";

$msg = ($pay_at_pickup ? "PICKUP RESERVATION" : "PURCHASE CONFIRMED via Stripe") . "\n"
     . "Purchase ID: $purchID\n"
     . "$payment_line\n\n"
     . "Customer: "  . ($customer['name']   ?? '') . "\n"
     . "Email: "     . ($customer['email']  ?? '') . "\n"
     . "Billing: "   . ($customer['street'] ?? '') . ", "
                     . ($customer['city']   ?? '') . ", "
                     . ($customer['state']  ?? '') . " "
                     . ($customer['zip']    ?? '') . "\n\n"
     . "PAINTINGS ORDERED:\n"
     . implode("\n", $email_lines) . "\n\n";

// Add fee breakdown by shipping group
if (!empty($groups_detail)) {
    $msg .= "FEE BREAKDOWN BY SHIPPING GROUP:\n";
    $msg .= "─────────────────────────────────────\n";
    
    foreach ($groups_detail as $group_key => $group) {
        $msg .= "\n" . $group['label'] . ":\n";
        
        // List pieces in this group
        foreach ($group['pieces'] as $piece) {
            $msg .= "  • " . htmlspecialchars($piece['name']) . " (" . htmlspecialchars($piece['canvas_size']) . "): $" . number_format((float)$piece['price'], 2) . "\n";
        }
        
        // Shipping cost for this group
        if ($group_key !== 'pickup' && isset($shipping_costs[$group_key])) {
            $ship_cost = $shipping_costs[$group_key];
            $msg .= "  Shipping (USPS Priority" . ($ship_cost['estimated'] ? " est." : "") . "): $" . number_format($ship_cost['cost'], 2) . "\n";
            if ($ship_cost['insurance'] > 0) {
                $msg .= "  Insurance: $" . number_format($ship_cost['insurance'], 2) . " (value: $" . number_format($ship_cost['item_value'], 2) . ")\n";
            }
        } elseif ($group_key === 'pickup') {
            $msg .= "  Shipping: FREE (local pickup)\n";
        }
        
        // Tax for this group
        if (isset($tax_by_group[$group_key])) {
            $tax_result = $tax_by_group[$group_key];
            $msg .= "  Tax: $" . number_format($tax_result['tax_amount'], 2) . "\n";
        }
    }
    
    // Total summary
    if (!empty($totals)) {
        $msg .= "\n─────────────────────────────────────\n";
        $msg .= "Subtotal: $" . number_format($totals['subtotal'], 2) . "\n";
        $msg .= "Shipping: $" . number_format($totals['shipping'], 2) . "\n";
        $msg .= "Tax: $" . number_format($totals['tax'], 2) . "\n";
        $msg .= "TOTAL: $" . number_format($totals['total'], 2) . "\n";
    }
}

$subject = $pay_at_pickup
    ? 'STUDIO ANNI PICKUP RESERVATION: ' . $purchID
    : 'STUDIO ANNI PURCHASE: ' . $purchID;

anni_mail(
    'studioannillc@gmail.com',
    $subject,
    wordwrap($msg, 70),
    $customer['email'] ?? ''
);

// ── Clear session ──────────────────────────────────────────────────────────
unset($_SESSION['cart'], $_SESSION['shipping'], $_SESSION['billing'], 
      $_SESSION['shipping_costs'], $_SESSION['tax_by_group'], $_SESSION['groups_detail'], $_SESSION['totals']);

echo json_encode(['status' => 'ok', 'purchID' => $purchID]);