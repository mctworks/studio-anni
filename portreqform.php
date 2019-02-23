<?php
require_once ('head.php');
require_once ('nav.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$cust_name = filter_input(INPUT_POST, 'petform_name');
$cust_email = filter_input(INPUT_POST, 'petform_email');
$cust_message = filter_input(INPUT_POST, 'petform_message');
$fileexterror = "";

    if(isset($_FILES) && (bool)$_FILES) {
        $allowedExtensions = array("gif","jpeg","jpg","png");

        $files = array();

        foreach($_FILES as $name=>$file) {
            if ($file['error']==0){
                $file_name = $file['name'];
                $temp_name = $file['tmp_name'];
                $file_type = $file['type'];
                $path_parts = pathinfo($file_name);
                $ext = $path_parts['extension'];
                if(!in_array($ext,$allowedExtensions)) {
                        $fileexterror = "ERROR: File <strong>$file_name</strong> has the extension .$ext which is not allowed. Please select an image file with the .jpg, .jpeg, .gif or .png extention.";
                } else {
                    $fileexterror = "";
                }
                array_push($files,$file);
            }
        }

        if ($fileexterror == ""){
            // email prep
            $to = "studioannillc@gmail.com";
            $from = "mictho98@baron-zemo.dreamhost.com";
            $subject ="STUDIO ANNI: Pet Portrait Request";
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

            // preparing attachments
            for($x=0;$x<count($files);$x++){
                    $file = fopen($files[$x]['tmp_name'],"rb");
                    $data = fread($file,filesize($files[$x]['tmp_name']));
                    fclose($file);
                    $data = chunk_split(base64_encode($data));
                    $name = $files[$x]['name'];
                    $message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$name\"\n" .
                    "Content-Disposition: attachment;\n" . " filename=\"$name\"\n" .
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
                    $message .= "--{$mime_boundary}\n";
            }
            // send email to Studio Anni

            $ok = mail($to, $subject, $message, $headers);
            if ($ok) {
                echo '<script type="text/javascript">
                window.location = "petformsent.php"
                </script>';
            } else {
                    echo "<p>mail could not be sent!</p>";
            }
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
    <form id="petform" class="form-horizontal" name="petform" enctype="multipart/form-data" method="POST" action="petform.php">
    <div class="row row-centered">
    <div class="col-md-4 col-md-offset-4">
    <div class="page-header">
      <h2 class="gdfg">Portrait Request Form</h2>
    </div>
    <div><p class="text-danger"><?php echo $fileexterror ?></p></div>
    <fieldset>
    <legend>Contact Details</legend>
    <div class="form-group">
        <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your Name</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type="text" name="petform_name" maxlength="80" placeholder="Your Name" id="petform_name" class="form-control" required>
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your E-Mail</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
          <input type="email" name="petform_email" maxlength="80" placeholder="Your E-Mail" class="form-control" required>
      </div>
    </div>
    </fieldset>

    <fieldset>
    <legend>About The Subject(s)</legend>
    <div class="form-group">
      <label class="col-sm-6 control-label" for="textinput">Please briefly tell us what you want Anni to paint, be it person(s) or pet(s)!</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
          <textarea name="petform_message" id="petform_message" rows="8" placeholder="Talk About Who You Want Painted!" class="form-control" required></textarea>
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-6 control-label" for="textinput">Source Image</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type='file' id="petpic1" name="petpic1" class="form-control" required>
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-6" for="textinput">Second Source Image (Optional)</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type='file' id="petpic2" name="petpic2" class="form-control">
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-6" for="textinput">Third Source Image (Optional)</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type='file' id="petpic3" name="petpic3" class="form-control">
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-6" for="textinput">Fourth Source Image (Optional)</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type='file' id="petpic4" name="petpic4" class="form-control">
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-6" for="textinput">Fifth Source Image (Optional)</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type='file' id="petpic5" name="petpic5" class="form-control">
      </div>
    </div>

    <label><input type="submit" class="purchaseButton" name="button" id="submit" value="Send Portrait Request!" /></label>
    </fieldset>
    </div></div>
</form>
<?php require_once ('footer.php');?>
