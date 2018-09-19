<?php
//main
require_once ('southwinds/phoenixeyes.php');
include ('view/headfoot.php');

//get recent slides
$query = "SELECT name, pieceID, size, slideimg FROM works
    WHERE (specialstatus IS NULL)
    AND (slideimg IS NOT NULL)
    ORDER BY date DESC
    LIMIT 7"; //change this limit number to change the number of slides
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
?>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<body>
  <div class='container'>
    <p class='above-slide'>LATEST FROM ANNI</p>
  </div>

  <div class="container">
  <div class="sizzle-reel" id='wrapper'>
    <?php foreach($result as $piece){
      $slide_url = "slides/" . $piece['slideimg'] . ".jpg";
      $page_url = "piece.php?id=" . $piece['pieceID'];
      echo '<div><a href="' . $page_url . '" target="_blank"><img src="' . $slide_url . '" class="responsive-slide">'.
      '<span class="slide-text"><h3>' . $piece['name'] . '</h3>' . $piece['size'] . '</span></div>';
    }?>
  </div>
</div>
  <script type="text/javascript">
      $(document).ready(function(){
        $('.sizzle-reel').slick({
          dots: false,
          infinite: true,
          speed: 250,
          fade: true,
          cssEase: 'linear',
          autoplay: true,
          autoplaySpeed: 3000,
          swipe: true,
          pauseOnHover: false
        });
      });
    </script>
</body>
</html>
