<?php
//gallery index
require_once ('southwinds/phoenixeyes.php');
require_once ('head.php');
require_once ('nav.php');

$query = 'SELECT name, size, price, image, pieceID, society6 FROM works
    WHERE special = 0 AND specialstatus IS NULL
    ORDER BY date DESC';
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
$thumbnum = count($result);
$imgshown= 3;

?>
<div class='main-container'>
<h1>Main Gallery</h1>
        <div class="container gallery">
            <?php
                for ($i = 0; $i < $thumbnum; $i++) {
                    if ($i == 0) {
                        echo '<div class="row">';
                    }
                    if ($i % 3 == 0 && $i != 0) {
                        echo '</div>'
                        . '<div class="row">';
                    }
                    echo '<div class="col-sm-4 col-md-4 col-xs-4 col-lg-4"><a href ="piece.php?id=' . $result[$i]['pieceID'] . '">' . '<img src="gallery/' . $result[$i]['image'] . '.jpg" class="img-thumbnail img-responsive" alt="Click To See More"></a>' .
                    '<p><b>' . $result[$i]['name'] . '</b></br>
                        <b>Size/Medium: </b>' . $result[$i]['size'] . '</br>
                        <b>Price: $</b>' . $result[$i]['price'] . '</br>';
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
</div>
<?php require_once ('footer.php');?>
