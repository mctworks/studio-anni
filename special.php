<?php
//special gallery index
require_once ('southwinds/phoenixeyes.php');
include 'view/headfoot.php';
$query = 'SELECT name, size, specialstatus, image, pieceID, society6 FROM works
    WHERE special = 1 AND image IS NOT NULL
    ORDER BY date DESC';
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
$thumbnum = count($result);
$i = 0;
$imgshown= 3;

?>
<h2>Special Gallery</h2>
<p>This gallery is a collection of pieces by Anni that have either been sold, commissioned, donated, or
    are in the studio but are not being considered for a sales listing. If you are looking for pieces that are
    being sold, you will need to look in the <a href="gallery.php">General Gallery</a>, or request for a
    commission for a Pet Portrait.</p>
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
                    echo '<div class="col-sm-4 col-md-4 col-xs-4 col-lg-4"><a href ="piece.php?id=' . $result[$i]['pieceID'] . '">' . '<img src="gallery/' . $result[$i]['image'] . '.jpg" class="img-thumbnail" alt="#"></a>' .
                    '<p><b>' . $result[$i]['name'] . '</b></br>
                        <b>Size/Medium: </b>' . $result[$i]['size'] . '</br>
                        <b>Status: </b>' . $result[$i]['specialstatus'] . '</br>';
                        if ($result[$i]['society6'] == 1){
                          echo '<em>Prints of this available at <a href="https://society6.com/studioanni" target="_blank">Anni\'s Society6</a>!</em></p>';
                        } else { echo '</p>'; }
                    echo '</div>';

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
