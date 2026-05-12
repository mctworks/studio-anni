<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once ('southwinds/phoenixeyes.php');


if (isset($_POST['id_num'])) {
    $qstring = filter_input(INPUT_GET, 'id');
    $purch_item_id = $qstring;
    $quantity = 1; //Change this accordingly if we ever sell anything with a quantity greater than 1
    //Checking if the piece exists in the database
    $query = 'SELECT * FROM works WHERE pieceID = :id_num';
    $statement = $fy->prepare($query);
    $statement ->bindValue(':id_num', $purch_item_id);
    $statement->execute();
    $piece = $statement->fetch();
    $statement->closeCursor();
    // Check if the product exists (array is not empty)
    //DEBUG
    //$path = realpath(session_save_path());
    //$files = array_diff(scandir($path), array('.', '..'));
    if ($piece && $quantity > 0) {
        // Product exists in database, now we can create/update the session variable for the cart
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            if (array_key_exists($purch_item_id, $_SESSION['cart'])) {
                // Disabled code below, since all pieces are one of a kind. Renable if we ever have the need for quantity (e.g. prints)
                // $_SESSION['cart'][$purch_item_id] += $quantity;
                // NOTE: REPLACE WITH PROPER ERROR HANDLER
            } else {
                // PIECE NOT IN CART

                $_SESSION['cart'][$purch_item_id] = $quantity;
            }
        } else {
            // There are no pieces in cart, this will add the first piece to cart
            $_SESSION['cart'] = array($purch_item_id => $quantity);

        }
        
    }
    
    // Prevent form resubmission...
   //header('location: viewcart.php?' . $purch_item_id);
    //exit;
}

// Remove product from cart, check for the URL param "remove", this is the product id, make sure it's a number and check if it's in the cart
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    // Remove the product from the shopping cart
    unset($_SESSION['cart'][$_GET['remove']]);
}

// Update item ("piece") quantities in cart. Implement "Update" button on the shopping cart page if we ever use this.
if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    // Loop through the post data so we can update the quantities for every product in cart
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'quantity') !== false && is_numeric($v)) {
            $id = str_replace('quantity-', '', $k);
            $quantity = (int)$v;
            // Always do checks and validation
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $quantity > 0) {
                // Update new quantity
                $_SESSION['cart'][$id] = $quantity;
            }
        }
    }
    // Prevent form resubmission...
    header('location: viewcart.php?' . $purch_item_id);
    exit;
}

// Send the user to the place order page if they click the Place Order button, also the cart should not be empty
if (isset($_POST['placeorder']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
   header('Location: placeorder.php');
   exit;
}

// Check the session variable for items in cart
$pieces_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$pieces = array();
$subtotal = 0.00;
// If there are items in cart
if ($pieces_in_cart) {
    // Products in cart array to question mark string array, we need the SQL statement to include IN (?,?,?,...etc)
    $array_to_question_marks = implode(',', array_fill(0, count($pieces_in_cart), '?'));
    $statement = $fy->prepare('SELECT * FROM works WHERE pieceID IN (' . $array_to_question_marks . ')');
    // We only need the array keys, not the values, the keys are the id's of the pieces
    $statement->execute(array_keys($pieces_in_cart));
    // Fetch the products from the database and return the result as an Array
    $pieces = $statement->fetchAll(PDO::FETCH_ASSOC);
    // Calculate the subtotal
    foreach ($pieces as $piece) 
    {   //$stock = (int)$pieces_in_cart[$piece['QTY_TBD'];
        $stock = '1';

        $subtotal += (float)$piece['price'] *  $stock;

    }
}


?>
