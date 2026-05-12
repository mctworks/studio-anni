<?php
//main
require_once ('southwinds/phoenixeyes.php');
require_once ('head.php');
require_once ('nav.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

//get recent slides
$query = "SELECT name, pieceID, canvas_size, slideimg, medium FROM works
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

<div class='index-welcome'><h1>Welcome to Studio Anni!</h1></div>
<div class='index-about'>
  <div class='about-gap2 no-para'></div>
  <div class='anni-photo'><img src='aboutanni.jpg' class='img-responsive' alt='Anni Thompson'></div>
  <div class='about-gap1 no-para'></div>
  <div class='about-anni'><h2>Meet Anni!</h2><p>Anni Thompson is a professional artist in the Atlanta area who specializes
    in original acrylic paintings and custom work. The themes of her work include animals, space, nature,
    fantasy, and everyday people. Anni has a meticulous eye for minor details, aiming for realism and the
    use of vibrant colors to capture not only the look of the subject, but also its essence and feel.
    While her influences include Maggie Stiefvater, her aunt (and teacher, sometimes) <a href="http://www.bepekafka.com/" target="_blank">Bepe Kafka</a>, as well as
    Vincent Van Gogh, Anni prefers to focus on refining her own proven style rather than fixating on the
    style of other artists.</p><p>Anni is not only an avid reader, but a self proclaimed nerd and animal lover. She lives with her
    husband and her animals (three cats and a small chihuahua), and spends a lot of time caught up in her imagination. She aims to spread joy
    through her work and hopes that any time you see her artwork, it makes you feel a little bit better.</p>
  </div>
  <div class='about-gap3 no-para'></div>
</div>

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
      '<span class="slide-text"><h3>' . $piece['name'] . '</h3>' . $piece['canvas_size'] . ' ' . $piece['medium'] . '</span></a></div>';
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

<div class='index-updates container no-para'>
  <div class='index-welcome'>
    <h2>May 2026 Updates</h2>
  </div>
  <div class='update-item'>
    <h3>Anni's work at Holston Mountain Artisans, Abingdon, VA</h3>
    <div style='text-align: -webkit-center;'><img src='holston_artisans1.jpg' class='img-responsive' alt='Anni's Wood Nymph series at Holston Mountain Artisans in Abingdon, VA'></div>
    <p>Select pieces by Anni, including her Wood Nymph series, are now on display and available for purchase at the gallery at <a href="https://www.holstonmtnarts.org/" target="_blank">Holston Mountain Artisans</a> in Abingdon, VA! If you're in the area, be sure to stop by and check out her work in person!</p>
    <p>Holston Mountain Artisans is located at 280 West Main Street, Abingdon, VA 24210. They are open Monday through Saturday from 10am to 5pm.</p>
    <div style='text-align: -webkit-center;'><img src='holston_artisans2.jpg' class='img-responsive' alt='Some of Anni's paintings of large cats on display at Holston Mountain Artisans in Abingdon, VA'></div>
  </div>
  <div class='update-item'>
    <h3>StudioAnni.com Ver 2.0 in development! Under old (but improved) management!</h3>
    <div style='text-align: -webkit-center;'><img src='mct630_logo.svg' class='img-responsive' style='padding: 10px 25%;' alt='MCT630 logo'></div>
    <p>We have some exciting news to share. Michael Thompson, the original developer of this site and Anni's husband, is coming back to refresh his old college side-project. He built and formerly launched this domain nearly 10 years ago, and now he's putting all the experience he's gained since launching the site back in 2017 into the early stages of building a whole new website for Studio Anni. Our goal is to make the user experience better and stay more connected with Anni's audience without relying so much on invasive social media platforms.</p>
    <p>Development is already rolling, and we'll share more details later. But right now, Michael really wants to hear from former customers and fans of Anni's work. Hit us up through our <a href="contact.php" target="_blank">contact page</a> for some special opportunities involving the new site.</p>
    <p>In the meantime, now that Anni is back from hiatus, Michael has given this site a big refresh. Everything works again, including purchasing and contacting for commissions. We've also updated the checkout process. You can still buy Anni's work online, but if you're local to the Atlanta area, we've made it super easy to arrange local pickup without having to use the contact form.</p>
    <p>You can find Michael's up to date contact info on his new professional website at <a href="https://www.mct630.com" target="_blank">MCT630.com</a> under the 'About' section. He invites all of Anni's fans to reach out. He'd love to hear from you.</p> 
  </div>
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
    <p>Commissions For Portraits Are <span style="color: #e800ff;"><b>OPEN!</b></span></p>
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
  <!-- <div id='soc6-gap2' class='no-para'></div>
  Old print promo section
</div>-->
</div> 
<?php require_once ('footer.php');?>
