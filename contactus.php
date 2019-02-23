<?php
require_once ('head.php');
require_once ('nav.php');

    $cust_name = filter_input(INPUT_POST, 'contact_name');
    $cust_email = filter_input(INPUT_POST, 'contact_email');
    $cust_message = filter_input(INPUT_POST, 'contact_message');
if(isset($_POST['submit'])){

    // email prep
    $to = "studioannillc@gmail.com";
    $from = "mictho98@baron-zemo.dreamhost.com";
    $subject ="Studio Anni Contact Inquiry";
    $message = "Name: " . $cust_name . "\nE-Mail: " . $cust_email . "\nMessage From Customer: " . $cust_message;
    $headers = "From: $from";

    // boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    // headers for attachment
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

    // multipart boundary
    $message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
    $message .= "--{$mime_boundary}\n";

    // send email to Studio Anni

    $ok = mail($to, $subject, $message, $headers);
    if ($ok) {
        echo '<script type="text/javascript">
           window.location = "contactsent.php"
        </script>';
    } else {
            echo "<p>mail could not be sent!</p>";
    }
}
?>
<link rel="stylesheet" href="view/bootstrap/bootstrap-formhelpers-min.css" media="screen">
<link rel="stylesheet" href="view/bootstrap/bootstrapValidator-min.css"/>
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" />
<link rel="stylesheet" href="view/bootstrap-side-notes.css" />
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
</head>
<body>
    <form id="petform" class="form-horizontal" name="contactus" enctype='multipart/form-data' method="POST" action="contactus.php">
    <div class="row row-centered">
    <div class="col-md-4 col-md-offset-4">
    <div class="page-header">
      <h2 class="gdfg">Contact Us</h2>
    </div>
    <div><p>We will happily answer any questions you may have.</p></div>
    <fieldset>
    <legend>Contact Details</legend>
    <div class="form-group">
        <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your Name</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type="text" name="contact_name" maxlength="80" placeholder="Your Name" id="contact_name" class="form-control" required>
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your E-Mail</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
          <input type="email" name="contact_email" maxlength="80" placeholder="Your E-Mail" id='contact_email' class="form-control" required>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your Message</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
          <textarea name="contact_message" id="contact_message" rows="8" placeholder="Talk To Us! Ask Us Anything!" class="form-control" required></textarea>
      </div>
    </div>

    <label><input type="submit" class="purchaseButton" name="submit" id="submit" value="Submit Contact Form" /></label>
    </fieldset>
    </div></div>
</form>
<?php require_once ('footer.php');?>
