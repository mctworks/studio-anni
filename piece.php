<?php
//Art piece template
require_once ('southwinds/phoenixeyes.php');
include ('head.php');
include ('nav.php');

$qstring = filter_input(INPUT_GET, 'id');

$id_num = $qstring;
$query = 'SELECT * FROM works WHERE pieceID= :id_num';
$statement = $fy->prepare($query);
$statement->bindValue(':id_num', $id_num);
$statement->execute();
$piece = $statement->fetch();
$statement->closeCursor();
$price = $piece['price'];
$shipping = $piece['shipping'];
?>
    <div class='main-container'>
        <div class='row'>
            <div class='col-sm-2'></div>
            <div class='col-sm-8 piece'>
                <div class="center"><h1><?php echo $piece['name']; ?></h1></div>
                <?php
                    if (isset($_SESSION['cart']) && array_key_exists($id_num, $_SESSION['cart'])) {
                        echo '<p>Looks like <b>' . $piece['name'] . '</b> is already in your cart!</p>';
                        $array_to_question_marks = implode(',', array_fill(0, count($pieces_in_cart), '?'));
                        $statement = $fy->prepare('SELECT * FROM works WHERE pieceID IN (' . $array_to_question_marks . ')');
                        $statement->execute(array_keys($pieces_in_cart));
                        $cart_items = $statement->fetchAll(PDO::FETCH_ASSOC);

                        echo '<h2>Your Cart:</h2>';
                        foreach ($cart_items as $cart_item) {
                            $in_cart_class = ($cart_item['pieceID'] == $id_num) ? 'piece-cart-hl' : 'piece-cart';
                            echo '<tr><td class="img">
                                <div class="' . $in_cart_class . '">
                                <img src="gallery/' . strval($cart_item['image']) . '.jpg" class="img-responsive" width="50" height="50" alt="' . htmlspecialchars($cart_item['name']) . '">
                                </div>
                            </td>
                            <td>
                                ' . htmlspecialchars($cart_item['name']) . '
                                <br>
                                <a href="viewcart.php?remove=' . strval($cart_item['pieceID']) . '" class="remove">Remove</a>
                            </td>
                            <td class="price">$' . $cart_item['price'] . '</td>
                            </tr>';
                        }
                    } else {
                        // Not in cart, show piece details
                        if ($piece['image'] != NULL) {
                            echo '<div class="center">
                            <img src="gallery/' . $piece['image'] . '.jpg" class="img-responsive"><p>';

                            if ($piece['side_a'] != NULL && $piece['side_b'] != NULL) {
                                echo '<div class="row"><div class="side">';
                                if ($piece['side_a'] != NULL) {
                                    echo '<div class="col-sm-6"><img src="gallery/' . $piece['side_a'] . '.jpg" class="img-responsive"></div>';
                                }
                                if ($piece['side_b'] != NULL) {
                                    echo '<div class="col-sm-6"><img src="gallery/' . $piece['side_b'] . '.jpg" class="img-responsive"></div>';
                                }
                                echo '</div></div>';
                            }

                            echo '<div class="row"><div class="piecetext"><div class="col-sm-12" style="padding-left:5rem;padding-bottom:5vh;">
                                <b>Size/Medium: </b>' . $piece['canvas_size'] . ' ' . $piece['medium'] . '<br />
                                <b>Category: </b>' . $piece['type'] . '<br />';

                            if ($piece['special'] == 0) {
                                echo '<b>Price: </b>$' . $price . '<br>';
                                if ($shipping == 24601.00) {
                                    echo '<p style="text-align:center;"><strong><u>IMPORTANT</u></strong><br>
                                        Due to the size and/or value of this piece, we will need to make special shipping arrangements with you.
                                        Please use the Contact Form below if you wish to purchase this piece so that we can quote you on shipping fees.</p>
                                        <p style="text-align:center;"><a href="contactus.php" class="btn btn-info" role="button">Contact Us To Purchase</a></p>';
                                } else {
                                    echo '<form action="viewcart.php?id=' . $id_num . '" method="POST">'
                                        . '<input type="hidden" name="id_num" value="' . $id_num . '">'
                                        . '<button class="purchaseButton" type="submit">Add "' . htmlspecialchars($piece['name']) . '" to Cart</button></form>';
                                }
                            } else {
                                echo '<b>Status: </b>' . $piece['specialstatus'] . '<br>';
                            }

                            echo '</div></div></div></div>';
                        } else {
                            echo '<h2>ERROR: Unable To Find Gallery Record</h2>
                                <p>If you think you\'ve reached this page in error, please contact the web administrator.
                                Otherwise, please return to the Gallery or Special Gallery and select a valid entry.</p>';
                        }
                    }
                ?>
                <a href="gallery.php" class="purchaseButton" role="button">RETURN TO GALLERY</a>
                <a href="discount.php" class="purchaseButton" role="button">VIEW DISCOUNTED PIECES</a>
            </div>
        </div>
    </div>
<?php require_once ('footer.php');?>