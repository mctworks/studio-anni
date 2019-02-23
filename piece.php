<?php
//Art piece template
require_once ('southwinds/phoenixeyes.php');
include ('head.php');
include ('nav.php');

$qstring = filter_input(INPUT_GET, 'id');

$id_num = $qstring;
$query = 'SELECT name, size, price, shipping, image, type, side_a, side_b, special, specialstatus, society6 FROM works
    WHERE pieceID= :id_num';
$statement = $fy->prepare($query);
$statement ->bindValue(':id_num', $id_num);
$statement->execute();
$piece = $statement->fetch();
$statement->closeCursor();
$price = $piece['price'];
$shipping = $piece['shipping'];
?>
    <div class='container'>
        <div class='row'>
            <div class='col-sm-2'></div>
            <div class='col-sm-8 piece'>
                <?php
                if ($piece['image'] != NULL){
                    //Image record exists
                    echo '<p style="font-size:20px">
                    <div class="center">
                    <h2>' . $piece['name'] . '</h2>
                    <img src = gallery/' . $piece['image'] . '.jpg class="img-responsive"><p>';

                            if ($piece['side_a'] != NULL && $piece['side_b'] != NULL){
                                echo '<div class="row">';
                                echo '<div class="side">';
                                if ($piece['side_a'] != NULL){
                                    //Left angle image, if available
                                echo '<div class="col-sm-6">';
                                echo '<img src = gallery/' . $piece['side_a'] . '.jpg class="img-responsive"> ';
                                echo '</div>';
                                }
                                if ($piece['side_b'] != NULL){
                                    //Right angle image, if available
                                    echo '<div class="col-sm-6">';
                                    echo '<img src =gallery/' . $piece['side_b'] . '.jpg class="img-responsive"><br>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                        echo '<div class="row"><div class= "piecetext"><div class="col-sm-12" style="padding-left:5rem";><b>Size/Medium: </b>' . $piece['size'] . '<br />
                        <b>Category: </b>' . $piece['type'] . '<br />';
                            if ($piece['society6'] == 1){
                                echo '<em>Prints and merchandise of this piece are available now at <a href="https://society6.com/studioanni" target="_blank">Anni\'s Society6 Shop</a>!</em><br />';
                            }
                            if ($piece['special']== 0){
                                //Normal Gallery params
                                echo '<b>Price: </b>$' . $price . '<br>';
                                //Check to see if this piece can be shipped by standard means, and if so, show the shipping fee
                                if ($shipping == 24601.00){
                                    echo '<p style="text-align:center;"><strong><u>IMPORTANT</u></strong><br> Due to the size and/or value of this piece, we will need to make special shipping arrangements with you if you wish to purchase this piece. Please use the Contact Form (click the button below) if you wish to purchase this piece so that we can quote you on the shipping fees and make any appropriate arrangements.</p>'
                                    . '<p style="text-align:center;"> <a href="contactus.php" class="btn btn-info" role="button">Contact Us To Purchase</a></p>';
                                } else {
                                    echo '<b>Shipping Fees: </b>$' . $shipping . '<br>';
                                    echo '<form action=purchase.php?id=' . $id_num . ' method="POST">'
                                    . '<button class="purchaseButton" type="submit">Purchase ' . $piece['name'] . '</button></form>';
                                }
                            } else {
                                //Special Gallery params
                                '<b>Status: </b>' . $piece['specialstatus'] . '<br></p>';
                            }
                        echo '</div>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    //Image record doesn't exist
                    echo '<p><h2>ERROR: Unable To Find Gallery Record</h2><br>If you think you\'ve reached this page in error, please contact the web administrator. Otherwise, please return to the Gallery page or Special Gallery page and select a valid entry.<br>GET: ' . $qstring . '<br>IMAGE: ' . $piece['image'] . '<br>PRICE: ' . $price;
                }
                ?>
            </div>
        </div>
    </div>
<?php require_once ('footer.php');?>
