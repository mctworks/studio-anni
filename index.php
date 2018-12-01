<?php
//main
require_once ('southwinds/phoenixeyes.php');
include ('head.php');
include ('nav.php');
include ('footer.php');

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
      '<span class="slide-text"><h3>' . $piece['name'] . '</h3>' . $piece['size'] . '</span></a></div>';
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
<h1>Meet Anni!</h1>
<div class='index-body'>
  <div class='anni-photo'><img src='aboutanni.jpg' class='img-responsive' alt='Anni Thompson'></div>
  <div class='about-anni'><p>Anni Thompson is a professional artist in the Atlanta area who specializes
    in original acrylic paintings and custom work. The themes of her work include animals, space, nature,
    fantasy, and everyday people. Anni has meticulous eye for minor details, aiming for realism and the
    use of vibrant colors to capture not only the look of the subject, but also its essence and feel.
    While her influences include Maggie Stiefvater, her aunt and sometimes-mentor Bepe Kafka, as well as
    Vincent Van Gogh, Anni prefers to focus on refining her own proven style rather than fixating on the
    style of other artists.</p><p>Anni is not only an avid reader, but a self proclaimed nerd and animal lover. She lives with her
    husband and four cats, and spends a lot of time caught up in her imagination. She aims to spread joy
    through her work and hopes that any time you see her artwork, it makes you feel a little bit better.
    She would love it if you followed her on social media and shared her artwork with others who might enjoy it.</p>
    <p><a href="https://www.facebook.com/StudioAnniLLC" target="_blank" style="text-decoration: none;"><i class="fab fa-facebook fa-2x"></i><span style="font-family: 'Waiting for the Sunrise', sans-serif;font-size: 2em;"> StudioAnniLLC</span></a><br />
       <a href="https://www.instagram.com/studio_anni/" target="_blank" style="text-decoration: none;"><i class="fab fa-instagram fa-2x"></i><span style="font-family: 'Waiting for the Sunrise', sans-serif;font-size: 2em;"> studio_anni</span></a><br />
       <a href="https://twitter.com/StudioAnni" target="_blank" style="text-decoration: none;"><i class="fab fa-twitter fa-2x"></i><span style="font-family: 'Waiting for the Sunrise', sans-serif;font-size: 2em;"> StudioAnni</span></a></p>
  </div>
</div>
</body>
</html>
