<?php
require_once ('southwinds/phoenixeyes.php');
include 'view/headfoot.php';
include 'pconfig.php';

$qstring = filter_input(INPUT_GET, 'id');

$id_num = $qstring;
$query = 'SELECT price, shipping, item1, item2, item3, item4, shipping, ship_method, cust_name FROM specialorders 
    WHERE sporderID= :id_num';
$statement = $fy->prepare($query);
$statement ->bindValue(':id_num', $id_num);
$statement->execute();
$specialorder = $statement->fetch();
$statement->closeCursor();
$price = $specialorder['price'];
$ship_cost = $specialorder['shipping'];
$total_cost = bcadd($price, $ship_cost, 2);
$piecename1 = $specialorder['item1'];
$piecename2 = $specialorder['item2'];
$piecename3 = $specialorder['item3'];
$piecename4 = $specialorder['item4'];
$shipmeth = $specialorder['ship_method'];
$customer = $specialorder['cust_name'];
$checkoutprice = str_replace('.', '', $total_cost);

$purchid_type = "S";
$purchID = $purchid_type . date('ymj') . $id_num;

//Customer name and billing address
$bill_name = filter_input(INPUT_POST, 'cardholdername');
$bill_street = filter_input(INPUT_POST, 'street');
$bill_city = filter_input(INPUT_POST, 'city');
$bill_state = filter_input(INPUT_POST, 'state');
$bill_zip = filter_input(INPUT_POST, 'zip');
$bill_country = filter_input(INPUT_POST, 'country');
$cust_email = filter_input(INPUT_POST, 'email');
//Alternate shipping info, if provided
$ship_name = filter_input(INPUT_POST, 'shippingname');
$ship_street = filter_input(INPUT_POST, 'shippingstreet');
$ship_city = filter_input(INPUT_POST, 'shippingcity');
$ship_state = filter_input(INPUT_POST, 'shippingstate');
$ship_zip = filter_input(INPUT_POST, 'shippingzip');
$ship_country = filter_input(INPUT_POST, 'shippingcountry');

//Prepped for SQL
$bill_name2 = mysql_escape_string($bill_name);
$bill_street2 = mysql_escape_string($bill_street);
$bill_city2 = mysql_escape_string($bill_city);
$bill_state2 = mysql_escape_string($bill_state);
$bill_zip2 = mysql_escape_string($bill_zip);
$bill_country2 = mysql_escape_string($bill_country);
$cust_email2 = mysql_escape_string($cust_email);
$ship_name2 = mysql_escape_string($ship_name);
$ship_street2 = mysql_escape_string($ship_street);
$ship_city2 = mysql_escape_string($ship_city);
$ship_state2 = mysql_escape_string($ship_state);
$ship_zip2 = mysql_escape_string($ship_zip);
$purchID2 = mysql_escape_string($purchID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Studio Anni: Purchase Form</title>
<link rel="stylesheet" href="view/bootstrap/bootstrap-formhelpers-min.css" media="screen">
<link rel="stylesheet" href="view/bootstrap/bootstrapValidator-min.css"/>
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" />
<link rel="stylesheet" href="view/bootstrap-side-notes.css" />
<script src="view/bootstrap/js/bootstrap-formhelpers-min.js"></script>
<script src="view/bootstrap/js/bootstrapValidator-min.js"></script>
<script src="view/bootstrap/js/bootstrap-side-notes.js"></script>
<style type="text/css">
.col-centered {
    display:inline-block;
    float:none;
    text-align:left;
    margin-right:-4px;
}
.row-centered {
	margin-left: 9px;
	margin-right: 9px;
}
</style>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="view/bootstrap/js/bootstrap-min.js"></script>
<script src="view/bootstrap/js/bootstrap-formhelpers-min.js"></script>
<script type="text/javascript" src="view/bootstrap/js/bootstrapValidator-min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#payment-form').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
		submitHandler: function(validator, form, submitButton) {
                    // createToken returns immediately - the supplied callback submits the form if there are no errors
                    Stripe.card.createToken({
                        number: $('.card-number').val(),
                        cvc: $('.card-cvc').val(),
                        exp_month: $('.card-expiry-month').val(),
                        exp_year: $('.card-expiry-year').val(),
			name: $('.card-holder-name').val(),
			address_line1: $('.address').val(),
			address_city: $('.city').val(),
			address_zip: $('.zip').val(),
			address_state: $('.state').val(),
			address_country: $('.country').val()
                        
                    }, stripeResponseHandler);
                    return false; // submit from callback
        },
        fields: {
            street: {
                validators: {
                    notEmpty: {
                        message: 'The street is required and cannot be empty'
                    },
					stringLength: {
                        min: 6,
                        max: 96,
                        message: 'The street must be more than 6 and less than 96 characters long'
                    }
                }
            },
            city: {
                validators: {
                    notEmpty: {
                        message: 'The city is required and cannot be empty'
                    }
                }
            },
			zip: {
                validators: {
                    notEmpty: {
                        message: 'The postal code is required and cannot be empty'
                    },
					stringLength: {
                        min: 4,
                        max: 9,
                        message: 'The postal code must be more than 4 and less than 9 characters long'
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'The email address is required and can\'t be empty'
                    },
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    },
					stringLength: {
                        min: 6,
                        max: 65,
                        message: 'The email must be more than 6 and less than 65 characters long'
                    }
                }
            },
			cardholdername: {
                validators: {
                    notEmpty: {
                        message: 'The card holder name is required and can\'t be empty'
                    },
					stringLength: {
                        min: 6,
                        max: 70,
                        message: 'The card holder name must be more than 6 and less than 70 characters long'
                    }
                }
            },
			cardnumber: {
		selector: '#cardnumber',
                validators: {
                    notEmpty: {
                        message: 'The credit card number is required and can\'t be empty'
                    },
					creditCard: {
						message: 'The credit card number is invalid'
					},
                }
            },
			expMonth: {
                selector: '[data-stripe="exp-month"]',
                validators: {
                    notEmpty: {
                        message: 'The expiration month is required'
                    },
                    digits: {
                        message: 'The expiration month can contain digits only'
                    },
                    callback: {
                        message: 'Expired',
                        callback: function(value, validator) {
                            value = parseInt(value, 10);
                            var year         = validator.getFieldElements('expYear').val(),
                                currentMonth = new Date().getMonth() + 1,
                                currentYear  = new Date().getFullYear();
                            if (value < 0 || value > 12) {
                                return false;
                            }
                            if (year == '') {
                                return true;
                            }
                            year = parseInt(year, 10);
                            if (year > currentYear || (year == currentYear && value > currentMonth)) {
                                validator.updateStatus('expYear', 'VALID');
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            },
            expYear: {
                selector: '[data-stripe="exp-year"]',
                validators: {
                    notEmpty: {
                        message: 'The expiration year is required'
                    },
                    digits: {
                        message: 'The expiration year can contain digits only'
                    },
                    callback: {
                        message: 'Expired',
                        callback: function(value, validator) {
                            value = parseInt(value, 10);
                            var month        = validator.getFieldElements('expMonth').val(),
                                currentMonth = new Date().getMonth() + 1,
                                currentYear  = new Date().getFullYear();
                            if (value < currentYear || value > currentYear + 100) {
                                return false;
                            }
                            if (month == '') {
                                return false;
                            }
                            month = parseInt(month, 10);
                            if (value > currentYear || (value == currentYear && month > currentMonth)) {
                                validator.updateStatus('expMonth', 'VALID');
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            },
			cvv: {
		selector: '#cvv',
                validators: {
                    notEmpty: {
                        message: 'The cvv is required and can\'t be empty'
                    },
					cvv: {
                        message: 'The value is not a valid CVV',
                        creditCardField: 'cardnumber'
                    }
                }
            },
        }
    });
});
</script>
<script type="text/javascript">
            //identifies website in the createToken call below
            Stripe.setPublishableKey(<?php echo '"'. STRIPE_PUBLIC_KEY . '"'?>); //STRIPE KEY
 
            function stripeResponseHandler(status, response) {
                if (response.error) {
                    // re-enable the submit button
                    $('.submit-button').removeAttr("disabled");
					// show hidden div
					document.getElementById('a_x200').style.display = 'block';
                    // show errors on the form
                    $(".payment-errors").html(response.error.message);
                } else {
                    
                    var form$ = $("#payment-form");
                    // token contains id, last4, and card type
                    var token = response['id'];
                    // insert the token into the form so it gets submitted to the server
                    form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
                    // and submit
                    form$.get(0).submit();
                    
                }
            }
</script>
</head>
<body>

  <form method="POST" id="payment-form" class="form-horizontal">
  <div class="row row-centered">
  <div class="col-md-4 col-md-offset-4">
  <div class="page-header">
      <h2 class="gdfg">International Order Form: <strong>Order for <?php echo $customer;?></strong></h2>
      <div class="row row-centered">
          <div class="col-sm-4 col-md-4 col-xs-4 col-lg-4"><b>Pieces:</b><br>
          <?php echo $piecename1 . '</br>'; 
          if ($piecename2 != 'NULL'){
              echo $piecename2 . '</br>';
          }
          if ($piecename3 != 'NULL'){
              echo $piecename3 . '</br>';
          }
          if ($piecename4 != 'null'){
              echo $piecename4 . '</br>';
          }
          echo '<b>Subtotal:</b> $' . $price . '</br>';
          echo '<b>Shipping:</b></br>' . '$' . $ship_cost . ' (via ' . $shipmeth . ')' ?> </div>
        <div class="col-sm-4 col-md-4 col-xs-4 col-lg-4"><p class="outside-form"><strong>Total: $<?php echo $total_cost; ?></strong></p></div>       
      </div>
  </div>
  <noscript>
  <div class="bs-callout bs-callout-danger">
    <h4>JavaScript is not enabled!</h4>
    <p>This payment form requires your browser to have JavaScript enabled. Please activate JavaScript and reload this page. Check <a href="http://enable-javascript.com" target="_blank">enable-javascript.com</a> for more informations.</p>
  </div>
  </noscript>
  <?php
require 'view/lib/Stripe.php';

$error = '';
$success = '';
	  
if ($_POST) {
  Stripe::setApiKey(STRIPE_PRIVATE_KEY); //STRIPE KEY

  try {
	if (empty($_POST['street']) || empty($_POST['city']) || empty($_POST['zip']))
      throw new Exception("Fill out all required fields.");
    if (!isset($_POST['stripeToken']))
      throw new Exception("Invalid Purchase Action");
    Stripe_Charge::create(array("amount" => $checkoutprice,
                                "currency" => "USD",
                                "card" => $_POST['stripeToken'],
				"description" => $_POST['email']));
    $success = '<div class="panel-body">
                <h2>Thank You For Your Purchase!</h2> <p class= "outside-form">Your payment was successful. Next, we will personally e-mail you to confirm your order details no later than 48 hours (we aim to respond within the first few hours of purchase.)</br>                
<p><a href="gallery.php" class="btn btn-info" role="button">Revisit General Gallery</a>&nbsp;<a href="index.php" class="btn btn-info" role="button">Back To Home Page</a></p></p> 
				</div>';
    //send non-payment order info to database
    $query2 = "INSERT INTO purchases
        (purchID, pieceID, cust_name, cust_street, cust_city, cust_state, cust_zip, cust_country, cust_email, ship_name, ship_city, ship_street, ship_state, ship_zip, ship_country)
        VALUES
        ('$purchID2', '0', '$bill_name2', '$bill_street2', '$bill_city2', '$bill_state2', '$bill_zip2', '$bill_country2', '$cust_email2', '$ship_name2', '$ship_city2', '$ship_street2', '$ship_state2', '$ship_zip2', '$ship_country2')";
    $statement2 = $fy->prepare($query2);
    $statement2->execute();
    $statement2->closeCursor();
    
    //send email to Studio Anni
        if ($ship_name == NULL){
            $ship_name = "Same as billing";
        }
        if ($ship_street == NULL){
        $ship_street = "Same as billing";
        }
    $msgSA = "AUTOMATED MESSAGE: Check Stripe to confirm the purchase. If the payment has been successful, touch base with the customer before shipping." . "\nCustomer Information...\nBilling Name: " . $bill_name 
            . "\nBilling Address: " . $bill_street . "\n" . $bill_city . ", " . $bill_state . " " . $bill_zip 
            . "\nCustomer E-Mail: " . $cust_email . "\n"
            . "\nShipping Name: " . $ship_name
            . "\nShipping Address: " . $ship_street . "\n" . $ship_city . " " . $ship_state . " " . $ship_zip;
    $msgSA = wordwrap($msgSA,70);
    mail('studioannillc@gmail.com', 'SPECIAL ORDER ALERT: ' . $customer, $msgSA);
  }
  catch (Exception $e) {
	$error = '<div class="alert alert-danger">
			  <strong>Error!</strong> '.$e->getMessage().'
			  </div>';
  }
}
?>
  <div class="alert alert-danger" id="a_x200" style="display: none;"> <strong>Error!</strong> <span class="payment-errors"></span> </div>
  <span class="payment-success">
  <?= $success ?>
  <?= $error ?>
  </span>
  <fieldset>
  
  <legend>Billing Details</legend>
  
  <!-- Street -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Street</label>
    <div class="col-sm-6">
      <input type="text" name="street" placeholder="Street" class="address form-control">
    </div>
  </div>
  
  <!-- City -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">City</label>
    <div class="col-sm-6">
      <input type="text" name="city" placeholder="City" class="city form-control">
    </div>
  </div>
  
  <!-- State -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">State/Province/Prefecture (If Applicable)</label>
    <div class="col-sm-6">
      <input type="text" name="state" maxlength="65" placeholder="State" class="state form-control">
    </div>
  </div>
  
  <!-- Zip Code -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Postal Code</label>
    <div class="col-sm-6">
      <input type="text" name="zip" maxlength="9" placeholder="Postal Code" class="zip form-control">
    </div>
  </div>
  
  <!-- Country -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Country</label>
    <div class="col-sm-6"> 
      <input type="text" name="country" placeholder="Country" class="country form-control">
    </div>
  </div>

  
  <!-- Email -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Email</label>
    <div class="col-sm-6">
      <input type="text" name="email" maxlength="65" placeholder="Email" class="email form-control">
    </div>
  </div>
  </fieldset>
  
  <fieldset>
    <legend>Card Details</legend>
    
    <!-- Card Holder Name -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Card Holder's Name</label>
      <div class="col-sm-6">
        <input type="text" name="cardholdername" maxlength="70" placeholder="Card Holder Name" class="card-holder-name form-control">
      </div>
    </div>
    
    <!-- Card Number -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Card Number</label>
      <div class="col-sm-6">
        <input type="text" id="cardnumber" maxlength="19" placeholder="Card Number" class="card-number form-control">
      </div>
    </div>
    
    <!-- Expiry-->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">Expiration Date</label>
      <div class="col-sm-8">
        <div class="form-inline">
          <select name="select2" data-stripe="exp-month" class="card-expiry-month stripe-sensitive required form-control">
            <option value="01" selected="selected">01</option>
            <option value="02">02</option>
            <option value="03">03</option>
            <option value="04">04</option>
            <option value="05">05</option>
            <option value="06">06</option>
            <option value="07">07</option>
            <option value="08">08</option>
            <option value="09">09</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
          </select>
          <span> / </span>
          <select name="select2" data-stripe="exp-year" class="card-expiry-year stripe-sensitive required form-control">
          </select>
          <script type="text/javascript">
            var select = $(".card-expiry-year"),
            year = new Date().getFullYear();
 
            for (var i = 0; i < 12; i++) {
                select.append($("<option value='"+(i + year)+"' "+(i === 0 ? "selected" : "")+">"+(i + year)+"</option>"))
            }
        </script> 
        </div>
      </div>
    </div>
    
    <!-- CVV -->
    <div class="form-group">
      <label class="col-sm-4 control-label" for="textinput">CVV/CVV2</label>
      <div class="col-sm-3">
        <input type="text" id="cvv" placeholder="CVV" maxlength="4" class="card-cvc form-control">
      </div>
    </div>
    
    <!-- Shipping -->
  <fieldset>
    <legend>Shipping Details*</legend>
    <p class="outside-form">*These only need to be filled out if the shipping information is different from the billing information.
        Otherwise, you can skip any or all of these fields.</p>
    <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput" >Recipient's Name</label>
    <div class="col-sm-6">
        <input type="text" maxlength="70" placeholder="Recipient's Name" name="shippingname" class='form-control' value='<?php $ship_name?>'>
        </div>
    </div>
    
    <!-- Street -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Street</label>
    <div class="col-sm-6">
      <input type="text" name="shippingstreet" placeholder="Shipping Street" class="form-control" value='<?php $ship_street?>'>
    </div>
  </div>
  
  <!-- City -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">City</label>
    <div class="col-sm-6">
      <input type="text" name="shippingcity" placeholder="Shipping City" class="form-control" value='<?php $ship_city?>'>
    </div>
  </div>
  
  <!-- State -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">State/Province/Prefecture</label>
    <div class="col-sm-6">
      <input type="text" name="shippingstate" maxlength="65" placeholder="Shipping State" class="form-control">
    </div>
  </div>
  
  <!-- Postal Code -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Postal Code</label>
    <div class="col-sm-6">
      <input type="text" name="shippingzip" maxlength="9" placeholder="Postal Code" class="form-control">
    </div>
  </div>
  
    <!-- Country -->
  <div class="form-group">
    <label class="col-sm-4 control-label" for="textinput">Country</label>
    <div class="col-sm-6"> 
      <input type="text" name="shippingcountry" placeholder="Shipping Country" class="form-control">
    </div>
  </div>
  
    <!-- Important notice -->
    <div class="form-group">
    <div class="panel panel-success">
      <div class="panel-heading">
        <h3 class="panel-title">Payment Summary and Conditions</h3>
      </div>
      <div class="panel-body">
           <!--MORE PAYMENT DETAILS NEEDED HERE-->
        <p>Your card will be charged <?php echo '<strong>$' . $total_cost . '</strong>';?> after clicking "Complete Purchase" below. The charge will appear on your statement as "STUDIO ANNI".</p>
        <p>We will send a notification by E-Mail to the address you provided within 48 hours (we usually respond in just a few hours) to verify your purchase.</p>
        <p><strong class= "text-warning">By clicking "Complete Purchase", you agree to the following:</strong><br>
            <strong>A.</strong> Studio Anni CANNOT refund commissions already paid for or works that have already been shipped, unless damaged or lost during shipment.<br>
            <strong>B.</strong> Studio Anni reserves the right to use images of the art piece you purchase for marketing purposes.<br>
            <strong>C.</strong> Customers do not reserve the right to use any works of art purchased by Studio Anni for business or financial purposes without consent, with the exception of standard display in commercial environments. <i>(e.g. We're happy if you want to display the piece in your store or cafe, just don't make prints of it without Anni's permission!)</i><br>
      </div>
    </div>
    
    <div class="control-group">
      <div class="controls">
        <center>
            <button class="purchaseButton" type="submit">Complete Purchase</button>
        </center>
      </div>
    </div>
  </fieldset>
</form>
</body>
</html>
