<?php
require_once ('head.php');
require_once ('nav.php');
require_once ('southwinds/phoenixeyes.php');

$query = 'SELECT name, size, image, pieceID FROM works
         WHERE type = "Portrait"
         ORDER BY date DESC';
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
?>
  <div class="gallery-header"><h1>Portraits</h1></div>
  <div class="main-container">
    <div class='col-sm-12 col-md-12 col-xs-12 col-lg-12'>
      <blockquote><p>I started painting portraits of people I know
                  personally in spring of 2018.  Soon more people were asking
                  about portraits and I started getting more commissions.
                  When I paint a portrait of someone, I want to show not just
                  what they look like, but who they are. I  work to show the
                  subtleties of expression while also matching the skin tone and
                  texture of the reference. I relish the new challenge and the
                  opportunity to expand my skills. I love to paint all kinds of
                  people, old and young, male and female, light and dark, and
                  everything in between.</p>
                <footer>Anni H. Thompson</footer></blockquote>
    </div>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
      <div class='main-container'>
        <p class='above-slide'>PAST PORTRAITS BY ANNI</p>
      </div>
      <div class="main-container">
      <div class="portrait-focused" id='wrapper'>
        <?php foreach($result as $piece){
          $slide_url = "gallery/" . $piece['image'] . ".jpg";
          echo '<div><img src="' . $slide_url . '" class="portrait-slide">'.
          '<span class="slide-text"><h3>' . $piece['name'] . '</h3>' . $piece['size'] . '</span></div>';
        }?>
      </div>
      <div class="row"><br /></div>
      <div class="portrait-nav" id='wrapper'>
        <?php foreach($result as $piece){
          $slide_url2 = "gallery/" . $piece['image'] . ".jpg";
          echo '<img src="' . $slide_url2 . '" class="portrait-nav-slide">';
        }?>
      </div>
      <script type="text/javascript">
        $('.portrait-focused').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.portrait-nav'
        });
        $('.portrait-nav').slick({
        slidesToShow: 7,
        slidesToScroll: 1,
        asNavFor: '.portrait-focused',
        dots: false,
        centerMode: true,
        focusOnSelect: true,
        variableWidth: true,
        autoplay: true,
        autoplaySpeed: 3000
        });
      </script>
      <div class="portrait-prices">
          <h2>Portrait Commission Pricing:</h2>
          <p>
          <table>
    			<thead>
    			<tr>
    			<th>Size (One Person)  </th>
          <th>Price*</th>
    			</tr>
    			</thead>
    			<tbody>
          <tr>
      		<td>8 x 8</td>
      		<td>$120.00 + Shipping</td>
      		</tr>
    			<tr>
    			<td>8 x 10</td>
    			<td>$150.00 + Shipping</td>
    			</tr>
          <tr>
    			<td>9 x 12</td>
    			<td>$160.00 + Shipping</td>
    			</tr>
    			<tr>
    			<td>12 x 12</td>
    			<td>$175.00 + Shipping</td>
    			</tr>
          <tr>
    			<td>11 x 14</td>
    			<td>$180.00 + Shipping</td>
    			</tr>
    			<tr>
    			<td>12 x 16</td>
    			<td>$200.00 + Shipping</td>
    			</tr>
          <tr>
    			<td>16 x 16</td>
    			<td>$250.00 + Shipping</td>
    			</tr>
          <tr>
    			<td>16 x 20</td>
    			<td>$300.00 + Shipping</td>
    			</tr>
    			</tbody>
    			</table>
    			*Add $50.00 for each additional person.
    			</p>
      </div>
      <div class="col-sm-12 col-md-12 col-xs-12 col-lg-12">
      <h2>Portrait Commission Steps</h2>
      <p style='font-size:125%;'>Step 1: Complete the information on our <a href='portreqform.php'>Portrait Request Form.</a></p>
      <p><strong>There is no charge to request a commission.</strong> Anni will need
      to know the details of what you're looking for in your portrait. The form will ask for
      your name, contact e-mail, details of what exactly you want for your portrait, and at least
      one picture of of the person(s) you wish for Anni to paint. We will need to know the size of the canvas for your portrait. If you wish for more than one person in the same portrait, please mention this
      as it can alter the cost of the painting depending on the complexity. If there is a deadline for the portrait, Anni will need that information before she can
      approve your request and begin work on your piece. She will need at least one high-quality image of the person(s) for reference.
      You can attach up to five images, though we strongly recommend at least three good photos of your pet.</p>
      <p style='font-size:125%;'>Step 2: Provide Anni With Additional Details</p>
      <p>Once we have your <a href='portreqform.php'>Portrait Request Form</a>, Anni will
      contact you by e-mail as soon as possible. While we normally respond within a few hours of receiving your form,
      we ask that you wait at least 48 hours for a response. Anni will e-mail you, and may ask for more information
      about person(s) that will help her assess the details and focus areas into your portrait. Often, she will ask for information about the
      person(s) you wish for her to paint. This kind of information will ensure that your portrait accurately captures everything about the subject she will be working with.</p>
      <p style='font-size:125%;'>Step 3: Commission Approval And Payment</p>
      <p>Once Anni has enough information to begin your portrait, she will first quote you a price, plus shipping costs, for your portrait. We have a secure form reserved
      for portrait customers if you wish to pay by credit or debit card, though if you prefer an alternative payment or shipping method, we will do our best to accommodate you. <strong>Please remember that the price
      that Anni quotes you must be paid in full before she can begin work on your portrait.</strong> Once the quoted amount for the commission has been covered, Anni can begin work on your portrait. During this
      time, you will receive progress notifications via e-mail from Anni until your portrait is finished.</p>
      <p style='font-size:125%;'>Step 4: Commission Completion And Shipment</p>
      <p>Anni will contact you once your portrait has been shipped. Normally we ship via USPS Priority Mail
      and will provide you with the tracking number. If you need your portrait shipped by a different courier, please be sure to mention this either on the
      <a href='portreqform.php'>Portrait Request Form</a> or before paying for your painting.</p>
      </div>
    <div>
      <p style='text-align: center;'><a href='portreqform.php' class="purchaseButton" role="button">Portrait Request Form</a></p>
    </div>
<?php require_once ('footer.php');?>
