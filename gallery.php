<?php
//gallery index
require_once ('southwinds/phoenixeyes.php');
include 'view/headfoot.php';
$query = 'SELECT name, size, price, image, pieceID FROM works 
    WHERE special = 0 AND specialstatus IS NULL
    ORDER BY date DESC';
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
$thumbnum = count($result);
$i = 0;
$imgshown= 3;
     
?>
<h2>General Gallery</h2>
<p>This is the listing for all of the paintings that are available for purchase. Please note that at this time, <strong>we are only
   able to ship pieces within the United States.</strong> If you are outside of the United States and are interested
   in purchasing anything from this gallery, please <a href="contactus.php">Contact Us</a> and we may be able to arrange a purchase. Also, if you are local to the Atlanta area, feel free to
   <a href="contactus.php">Contact Us</a> to arrange to purchase and pick up the piece at the studio, if you prefer to save on shipping costs.</p>
        <div class="container gallery">
            <?php                
                for ($i; $i < $thumbnum; $i++) {
                    if ($i == 0) {
                        echo '<div class="row">';
                    }
                    if ($i % 3 == 0 && $i != 0) {
                        echo '</div>'
                        . '<div class="row">';
                    }
                    if ($i % 9 == 0 && $i != 0) {

                    }
                    echo '<div class="col-sm-4 col-md-4 col-xs-4 col-lg-4"><a href ="piece.php?id=' . $result[$i]['pieceID'] . '">' . '<img src="gallery/' . $result[$i]['image'] . '.jpg" class="img-thumbnail img-responsive" alt="Click To See More"></a>' .        
                    '<p><b>' . $result[$i]['name'] . '</b></br>                     
                        <b>Size/Medium: </b>' . $result[$i]['size'] . '</br>
                        <b>Price: $</b>' . $result[$i]['price'] . '</br></p>
                    </div>';

                    if ($i == $thumbnum) {
                        echo '</div>';
                    }
                }
                echo '<script> var gallerylength = $("div.row").length;
                $("div.gallery .row").slice(' . $imgshown . ', gallerylength).css({
                "visibility": "hidden", 
                "display": "none"
                });</script>';?>
        </div>
    </body>
</html>