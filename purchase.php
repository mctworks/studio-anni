<?php
require_once ('southwinds/phoenixeyes.php');
require_once ('head.php');
require_once ('nav.php');\

$qstring = filter_input(INPUT_GET, 'id');

$id_num = $qstring;
$query = 'SELECT name, size, price, image, special, specialstatus FROM works
    WHERE pieceID= :id_num';
$statement = $fy->prepare($query);
$statement ->bindValue(':id_num', $id_num);
$statement->execute();
$piece = $statement->fetch();
$statement->closeCursor();
$price = $piece['price'];
$piecename = $piece['name'];
$checkoutprice = str_replace('.', '', $price);
$specialcheck = $piece['special'];
$processing = $piece['specialstatus'];
?>
<body>
<?php if ($specialcheck == 1 || $processing == 'PROCESSING'){
   echo '<container><h2>SORRY!</h2><p>This item is either currently unavailable for purchase, or has already been sold.</p></container>';
} else {
    //Redirect to the purchase form
    echo '<script type="text/javascript">
           window.location = "purchaseform.php?id=' . $id_num .'"</script>';
} ?>
<?php require_once ('footer.php');?>
