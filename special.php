<?php
//special gallery index
require_once ('southwinds/phoenixeyes.php');
require_once ('head.php');
require_once ('nav.php');
$query = 'SELECT name, size, specialstatus, image, pieceID, society6 FROM works
    WHERE special = 1 AND image IS NOT NULL
    ORDER BY date DESC';
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
?>

<div class='main-container'>
  <div class="gallery-header"><h1>Special Gallery</h1></div>
  <div class="gallery">
      <?php foreach($result as $piece){
          echo '<div class="gallery-selection">
                  <div class="piece-image">
                    <a href ="piece.php?id=' . $piece['pieceID'] . '">' . '<img src="gallery/' . $piece['image'] . '.jpg" class="img-responsive" alt="' . $piece['name'] . '""></a>
                  </div>' .
                  '<div class="piece-details">
                    <p><h4>' . $piece['name'] . '</h4></br>
                    <b>Size/Medium: </b>' . $piece['size'] . '</br>' .
                    '<b>Status: </b>' . $piece['specialstatus'] . '</br>';
                  if ($piece['society6'] == 1){
                    echo '<em>Prints of this available at <a href="https://society6.com/studioanni" target="_blank">Anni\'s Society6</a>!</em></p>';
                  } else { echo '</br></p>'; }
                  echo '</div></div>';
              }
      ?>
  </div>
</div>
<?php require_once ('footer.php'); ?>
