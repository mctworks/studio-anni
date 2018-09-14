<?php
require_once 'view/headfoot.php';
require_once ('southwinds/phoenixeyes.php');
include 'view/headfoot.php';
$query = 'SELECT name, image, pieceID FROM works '
        . 'WHERE type = "Pet" '
        . 'AND pieceID <> 30 '
        . 'ORDER BY date ASC ';
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
$thumbnum = count($result) - 2;
$imgshown= 3;
$j = 2;
?>
    <body>
        <h2>Pet Portraits</h2>
    <div class= 'container'>
        <div class='row'>
            <div class='col-sm-9 col-md-9 col-xs-8 col-lg-9'>
                <blockquote><p>I have always loved animals. Even as a child, I
                    felt a strong bond with them and I feel a special connection
                    with every animal I meet. I think the beauty of animals should
                    be celebrated...</p>
                </blockquote>
            </div>
            <div class="col-sm-3 col-md-3 col-xs-4 col-lg-3">
                <?php echo "<img src='gallery/" . $result[0]['image'] . ".jpg' width=250px class='img-rounded img-responsive'>"; ?><br><i><?php echo $result[0]['name'];?></i>
            </div>
        </div>
        <div class='row'>
            <div class="col-sm-3 col-md-3 col-xs-4 col-lg-3">
                <?php echo "<img src='gallery/" . $result[1]['image'] . ".jpg' width=250px class='img-rounded img-responsive'>"; ?><br><i><?php echo $result[1]['name'];?></i>
            </div>
            <div class="col-sm-9 col-md-9 col-xs-8 col-lg-9">
                <blockquote class='blockquote-reverse'><p>...Every time I paint an animal, I aim to capture who the animal
                is, not just the details of fur and eyes.  My Pet Portraits are
                no different. I want to capture who your pet is on canvas, to the
                point that you can instantly recognize the attributes that
                spur the love you have for your pet.</p>
                <footer>Anni H. Thompson</footer></blockquote>
            </div>
        </div>
        <div class='row'>
            <div class="col-sm-12 col-md-12 col-xs-12 col-lg-12">
                <h1>Let Anni Paint Your Pet!</h1>
            </div>
        </div>
        <div class='row'>
            <div class="col-sm-12 col-md-12 col-xs-12 col-lg-12">

            </div>
        </div>
        <div class='row'>
            <div class='col-sm-4 col-md-4 col-xs-4 col-lg-4'>
                <img src='gallery/nerophoto.jpg' class='img-rounded img-responsive'>
            </div>
            <div class='col-sm-4 col-md-4 col-xs-4 col-lg-4'>
                <img src='gallery/507170.jpg' class='img-rounded img-responsive'>
            </div>
            <div class='col-sm-4 col-md-4 col-xs-4 col-lg-4'>
                <p style='padding: 6px 0 6px 0;'><i>Left: Photograph of Nero<br> Right: Pet Portrait of Nero by Anni</i></p>
            </div>
            <p style='font-size:125%;'>Anni is currently accepting commissions for her highly popular and
            painstakingly detailed pet portraits, at very reasonable rates. Pricing
            starts at $150.00 for one pet on a 8 x 10 inch
            canvas. Anni can customize each portrait to your liking, such as adding accessories
            to the subject, extra detailing of features that define your pet.

			<table>
			<thead>
			<tr>
			<th>Size (One Pet)  </th>
			<th>Price</th>
			</tr>
			</thead>
			<tbody>
			<tr>
			<td>8 x 10</td>
			<td>$150.00 + Shipping</td>
			</tr>
			<tr>
			<td>12 x 12</td>
			<td>$175.00 + Shipping</td>
			</tr>
			<tr>
			<td>12 x 16</td>
			<td>$200.00 + Shipping</td>
			</tr>
			</tbody>
			</table>
			Add $50.00 for each additional pet.
			</p>
        </div>
        <div class='row'>
            <div class="col-sm-12 col-md-12 col-xs-12 col-lg-12">
                <h3>Commissioning a Pet Portrait from Studio Anni</h3>
                <p style='font-size:125%;'>Step 1: Complete the information on our <a href='petform.php'>Pet Portrait Request Form.</a></p>
                <p style="font-family: 'Raleway', sans-serif;"><strong>There is no charge to request a commission.</strong> Anni will need
                to know the details of what you're looking for in a Pet Portrait. The form will ask for
                your name, contact e-mail, details of what exactly you want in your pet's portrait, and at least
                one picture of your pet. Anni will need to know the size of the canvas for your Pet Portrait. If there is more than one pet in the same portrait, please mention this
                as it can alter the cost of the painting depending on the complexity. If there is a deadline for the portrait, Anni will need that information before she can
                approve your request and begin work on your piece. Anni will need at least one high-quality image of your pet for reference.
                You can attach up to five images, though we strongly recommend at least three good photos of your pet.</p>
                <p style='font-size:125%;'>Step 2: Provide Anni With Additional Details</p>
                <p style="font-family: 'Raleway', sans-serif;">Once we have your <a href='petform.php'>Pet Portrait Request Form</a>, Anni will
                contact you by e-mail as soon as possible. While we normally respond within a few hours of receiving your form,
                we ask that you wait at least 48 hours for a response. Anni will e-mail you, and will usually ask for more information
                about your pet that will help her assess the details and focus areas into your Pet Portrait. Often, she will ask you questions
                about your pet's behavior. While this seems trivial, rest assured that this kind of information will ensure that your portrait accurately captures everything you love about your pet.</p>
                <p style='font-size:125%;'>Step 3: Commission Approval And Payment</p>
                <p style="font-family: 'Raleway', sans-serif;">Once Anni has enough information about your pet, she will be able to quote you a price, plus shipping costs, for your Pet Portrait. We have a secure form reserved
                for Pet Portrait customers if you wish to pay by credit or debit card, though if you prefer an alternative payment or shipping method, we will do our best to accommodate you. <strong>Please remember that the price
                that Anni quotes you must be paid in full before she can begin work on your Pet Portrait.</strong> Once the quoted amount for the commission has been covered, Anni can begin work on your Pet Portrait. During this
                time, you will receive progress notifications via e-mail from Anni until your Pet Portrait is finished.</p>
                <p style='font-size:125%;'>Step 4: Commission Completion And Shipment</p>
                <p style="font-family: 'Raleway', sans-serif;">Anni will contact you once your Pet Portrait has been shipped. Normally we ship via USPS Priority Mail
                and will provide you with the tracking number. If you need your Pet Portrait shipped by a different courier, please be sure to mention this either on the
                <a href='petform.php'>Pet Portrait Request Form</a> or before paying for your painting.</p>

                <h3>Past Pet Portraits by Anni</h3>
                <?php
                for ($i = 0; $i < $thumbnum; $i++) {
                    if ($i == 0) {
                        echo '<div class="row">';
                    }
                    if ($i % 3 == 0 && $i != 0) {
                        echo '</div>'
                        . '<div class="row">';
                    }
                    if ($i % 9 == 0 && $i != 0) {

                    }
                    echo '<div class="col-sm-4 col-md-4 col-xs-4 col-lg-4"><img src="gallery/' . $result[$j]['image'] . '.jpg" class="img-rounded img-responsive"></a>' .
                    '<p><i>' . $result[$j]['name'] . '</i></br>
                    </div>';
                    $j++;
                    if ($i == $thumbnum) {
                        echo '</div>';
                    }
                }
                echo '<script> var gallerylength = $("div.row").length;
                $("div.gallery .row").slice(' . $imgshown . ', gallerylength).css({
                "visibility": "hidden",
                "display": "none"
                });</script>';?>
                <div class="col-sm-12 col-md-12 col-xs-12 col-lg-12">
                <p style='font-size:125%;'>Want to see your pet amongst these fine animals?</p>
                <p style='text-align: center;'><a href='petform.php' class="purchaseButton" role="button">Fill Out The Pet Portrait Request Form</a></p>
                </div>
            </div>
        </div>
    </div>
    </body>
</html>
