<?php
include ('head.php');
include ('nav.php');
include ('footer.php');
require('southwinds/phoenixeyes.php');
$qstring = filter_input(INPUT_GET, 'id');

$id_num = $qstring;
$query = 'SELECT name, size, price, image, specialstatus FROM works
    WHERE pieceID= :id_num';
$statement = $fy->prepare($query);
$statement ->bindValue(':id_num', $id_num);
$statement->execute();
$piece = $statement->fetch();
$piecename = $piece['name'];
$processing = $piece['specialstatus'];
$statement->closeCursor();

//If processing, display message. If not, continue
if ($processing == 'PROCESSING'){
    echo "Item is processing.";
} else {
    //Customer name and billing address
    $bill_name = filter_input(INPUT_POST, 'cardholdername');
    $bill_street = filter_input(INPUT_POST, 'street');
    $bill_city = filter_input(INPUT_POST, 'city');
    $bill_state = filter_input(INPUT_POST, 'state');
    $bill_zip = filter_input(INPUT_POST, 'zip');
    $cust_email = filter_input(INPUT_POST, 'email');
    //Alternate shipping info, if provided
    $ship_name = filter_input(INPUT_POST, 'shippingname');
    $ship_street = filter_input(INPUT_POST, 'shippingstreet');
    $ship_city = filter_input(INPUT_POST, 'shippingcity');
    $ship_state = filter_input(INPUT_POST, 'shippingstate');
    $ship_zip = filter_input(INPUT_POST, 'shippingzip');
    $purchID = "G" . date('ymj') . $id_num;
    $pieceID = $id_num;

    //Record data to 'purchases' table
    $bill_name2 = mysql_escape_string($bill_name);
    $bill_street2 = mysql_escape_string($bill_street);
    $bill_city2 = mysql_escape_string($bill_city);
    $bill_state2 = mysql_escape_string($bill_state);
    $bill_zip2 = mysql_escape_string($bill_zip);
    $cust_email2 = mysql_escape_string($cust_email);
    $ship_name2 = mysql_escape_string($ship_name);
    $ship_street2 = mysql_escape_string($ship_street);
    $ship_city2 = mysql_escape_string($ship_city);
    $ship_state2 = mysql_escape_string($ship_state);
    $ship_zip2 = mysql_escape_string($ship_zip);
    $purchID2 = mysql_escape_string($purchID);
    $pieceID2 = mysql_escape_string($pieceID);
    $query2 = "INSERT INTO purchases
        (purchID, pieceID, cust_name, cust_street, cust_city, cust_state, cust_zip, cust_email, ship_name, ship_city, ship_street, ship_state, ship_zip)
        VALUES
        ('$purchID2', '$pieceID2', '$bill_name2', '$bill_street2', '$bill_city2', '$bill_state2', '$bill_zip2', '$cust_email2', '$ship_name2', '$ship_city2', '$ship_street2', '$ship_state2', '$ship_zip2')";
    $statement2 = $fy->prepare($query2);
    $statement2->execute();
    $statement2->closeCursor();

    //Change "specialstatus" in "works" table to 'PROCESSING' status
    $query3 = "UPDATE works SET specialstatus = 'PROCESSING' WHERE pieceID = $id_num";
    $statement3 = $fy->prepare($query3);
    $statement3->execute();
    $statement3->closeCursor();

    echo 'Check specialstatus...</br>';
    //send email to Studio Anni
        if ($ship_name == NULL){
            $ship_name = "Same as billing";
        }
        if ($ship_street == NULL){
        $ship_street = "Same as billing";
        }
    $msgSA = "AUTOMATED MESSAGE: Check Stripe to confirm the purchase. If the payment has been successful, touch base with the customer before shipping." . "\rCustomer Information...\rPiece ID: "
            . $id_num . "\nBilling Name: " . $bill_name
            . "\nBilling Address: " . $bill_street . "\n" . $bill_city . ", " . $bill_state . " " . $bill_zip
            . "\nCustomer E-Mail: " . $cust_email . "\n"
            . "\nShipping Name: " . $ship_name
            . "\nShipping Address: " . $ship_street . "\n" . $ship_city . " " . $ship_state . " " . $ship_zip;
    $msgSA = wordwrap($msgSA,70);
    mail('studioannillc@gmail.com', 'STUDIO ANNI PURCHASE ALERT: ' . $piecename, $msgSA);
}

echo 'This is still in test mode. You\'re unable to make a purchase just yet. Stay tuned to our social media for opening.';
?>
