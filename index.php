<?php
//main
require_once ('southwinds/phoenixeyes.php');
require_once ('head.php');
require_once ('nav.php');

//get recent slides
$query = "SELECT name, pieceID, size, slideimg FROM works
    WHERE (specialstatus IS NULL)
    AND (slideimg IS NOT NULL)
    ORDER BY date DESC
    LIMIT 7";
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
?>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

  <div class='container para-covered'>
    <p class='above-slide'>LATEST FROM ANNI</p>
  </div>
<div class="main-container">
<div class="para-covered">
  <div class="sizzle-reel para-covered" id='wrapper'>
    <?php foreach($result as $piece){
      $slide_url = "slides/" . $piece['slideimg'] . ".jpg";
      $page_url = "piece.php?id=" . $piece['pieceID'];
      echo '<div class="para-covered"><a href="' . $page_url . '" target="_blank"><img src="' . $slide_url . '" class="responsive-slide">'.
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
<div class='index-about'>
  <div class='about-gap2 no-para'></div>
  <div class='anni-photo'><img src='aboutanni.jpg' class='img-responsive' alt='Anni Thompson'></div>
  <div class='about-gap1 no-para'></div>
  <div class='about-anni'><h1>Meet Anni!</h1><p>Anni Thompson is a professional artist in the Atlanta area who specializes
    in original acrylic paintings and custom work. The themes of her work include animals, space, nature,
    fantasy, and everyday people. Anni has a meticulous eye for minor details, aiming for realism and the
    use of vibrant colors to capture not only the look of the subject, but also its essence and feel.
    While her influences include Maggie Stiefvater, her aunt (and teacher, sometimes) <a href="http://www.bepekafka.com/" target="_blank">Bepe Kafka</a>, as well as
    Vincent Van Gogh, Anni prefers to focus on refining her own proven style rather than fixating on the
    style of other artists.</p><p>Anni is not only an avid reader, but a self proclaimed nerd and animal lover. She lives with her
    husband and four cats, and spends a lot of time caught up in her imagination. She aims to spread joy
    through her work and hopes that any time you see her artwork, it makes you feel a little bit better.
    She would love it if you followed her on social media and shared her artwork with others who might enjoy it.</p>
    <p><a href="https://www.facebook.com/StudioAnniLLC" target="_blank" style="text-decoration: none;"><i class="fab fa-facebook fa-2x"></i><span style="font-family: 'Waiting for the Sunrise', sans-serif;font-size: 2em;"> StudioAnniLLC</span></a><br />
       <a href="https://www.instagram.com/studio_anni/" target="_blank" style="text-decoration: none;"><i class="fab fa-instagram fa-2x"></i><span style="font-family: 'Waiting for the Sunrise', sans-serif;font-size: 2em;"> studio_anni</span></a><br />
       <a href="https://twitter.com/StudioAnni" target="_blank" style="text-decoration: none;"><i class="fab fa-twitter fa-2x"></i><span style="font-family: 'Waiting for the Sunrise', sans-serif;font-size: 2em;"> StudioAnni</span></a></p>
  </div>
  <div class='about-gap3 no-para'></div>
</div>

<div class='index-tour'>
  <div id='tour-header'>
    <h1>A Tour Of The Studio...</h1>
  </div>
  <div id='tour-gallery' class="parallax-back">
    <div id='tour-gallery-text'>
      <h2><b>Main Gallery</b></h2>
      <p>The Main Gallery includes original works of art by Anni that are available for purchase at reasonable prices.
        We normally ship pieces via USPS, but if you're local to the Greater Atlanta Area, we can arrange for pick up.
        <b>Please note that we are unable to ship outside of the United States at this time!</b></p><br />
      <p style='text-align: center;'><a href='gallery.php' class="purchaseButton" role="button">Visit Main Gallery →</a></p>
    </div>
  </div>
  <div id='tour-portraits'>
    <h2><b>Custom Portraits</b></h2>
    <p>Commissions For Portraits Are <span style="color: #9bffb5;"><b>Currently Open!</b></span></p>
  </div>
  <div id='portraits-gap2' class='no-para'></div>
  <div id='tour-pet-portraits'>
    <div id='tour-portraits-text' class='tour-portraits-text-margins1'>
      <h3><b>Pet Portraits</b></h3>
      <p>Commission Anni to perfectly capture your furbaby (or fur children) on canvas!</p><br />
      <p style='text-align: center;'><a href='petportrait.php' class="purchaseButton" role="button">Learn More →</a></p>
    </div>
  </div>
  <div id='portraits-gap1' class='no-para'></div>
  <div id='tour-human-portraits' class='no-para'>
    <div id='tour-portraits-text' class='tour-portraits-text-margins2'>
      <h3><b>People Portraits</b></h3>
      <p>Want to see yourself or a loved one painted? Let Anni do it in immaculate detail!</p><br />
      <p style='text-align: center;'><a href='portraits.php' class="purchaseButton" role="button">Learn More →</a></p>
    </div>
  </div>
  <div id='portraits-gap3' class='no-para'></div>
  <div id='portraits-bottom' class='no-para'></div>
  <div id='tour-special' class='parallax-back'>
    <div id='tour-gallery-text'>
      <h2><b>Special Gallery</b></h2>
      <p>Here you can take a look at some of Anni's past commissions, sold/donated original pieces, and select works that are permanently kept in the studio.</p><br />
      <p style='text-align: center;'><a href='special.php' class="purchaseButton" role="button">Visit Special Gallery →</a></p>
    </div>
  </div>
  <div id='soc6-gap2' class='no-para'></div>
  <div id='soc6-logo' class='no-para'><img src='society6logo.png' alt='Studio Anni on Society6' class='img-responsive'></div>
  <div id='soc6-gap1' class='no-para'></div>
  <div id='soc6-text' class='no-para'><h2>Studio Anni on Society6!</h2><p>Can't afford an original piece right now? Or was your favorite painting already sold?
      Don't worry! Prints, posters and other high-quality merchandise of Anni's work, <em>including select pieces from the Special Gallery</em>,
      are available at <a href="https://society6.com/studioanni" target="_blank">Studio Anni's Society6 Store!</a></p></div>
  <div id='soc6-gap3' class='no-para'></div>
</div>
</div>
<?php require_once ('footer.php');?>
