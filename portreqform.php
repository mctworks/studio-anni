<?php
require_once ('head.php');
require_once ('nav.php');
require_once ('southwinds/mailer.php');

$fileexterror = '';
$allowed_ext  = ['gif', 'jpeg', 'jpg', 'png'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('DEBUG: Form submitted. POST data received.');
    error_log('DEBUG: $_FILES: ' . json_encode($_FILES, JSON_PARTIAL_OUTPUT_ON_ERROR));
    
    $cust_name    = filter_input(INPUT_POST, 'petform_name',    FILTER_SANITIZE_SPECIAL_CHARS);
    $cust_email   = filter_input(INPUT_POST, 'petform_email',   FILTER_VALIDATE_EMAIL);
    $cust_message = filter_input(INPUT_POST, 'petform_message', FILTER_SANITIZE_SPECIAL_CHARS);

    $attachments = [];

    if (isset($_FILES) && (bool)$_FILES) {
        error_log('DEBUG: Processing files. Count: ' . count($_FILES));
        foreach ($_FILES as $fieldname => $file) {
            error_log('DEBUG: Processing field ' . $fieldname . '. Error code: ' . $file['error']);
            if ($file['error'] === 0) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                error_log('DEBUG: File extension: ' . $ext);
                if (!in_array($ext, $allowed_ext)) {
                    $fileexterror = 'ERROR: File <strong>' . htmlspecialchars($file['name']) . '</strong> '
                        . 'has extension .' . $ext . ' which is not allowed. '
                        . 'Please upload .jpg, .jpeg, .gif or .png files only.';
                    error_log('DEBUG: Invalid extension. Error set: ' . $fileexterror);
                    break;
                }
                $attachments[] = ['tmp_name' => $file['tmp_name'], 'name' => $file['name']];
                error_log('DEBUG: Attachment added - tmp_name: ' . $file['tmp_name'] . ', name: ' . $file['name']);
            } else {
                error_log('DEBUG: File upload error code: ' . $file['error']);
            }
        }
    }

    if (!$fileexterror && isset($_POST['button'])) {
        error_log('DEBUG: portreqform - Attempting to send email. Attachments count: ' . count($attachments));
        error_log('DEBUG: portreqform - Customer email: ' . $cust_email);
        
        $body = "Name: $cust_name\nE-Mail: $cust_email\nMessage From Customer:\n$cust_message";
        $ok   = anni_mail_with_attachments(
            'studioannillc@gmail.com',
            'STUDIO ANNI: Portrait Request',
            $body,
            $attachments,
            $cust_email
        );
        
        error_log('DEBUG: portreqform - Mail function returned: ' . ($ok ? 'TRUE' : 'FALSE'));
        
        if ($ok) {
            error_log('DEBUG: portreqform - Success! Redirecting to petformsent.php');
            echo '<script>window.location = "petformsent.php";</script>';
            exit;
        } else {
            error_log('DEBUG: portreqform - Email send failed!');
            $fileexterror = 'Your request could not be sent. Please try again or contact us directly.';
        }
    }
}
?>
<div class="main-container">
  <div class="row">
    <div class="col-md-4 col-md-offset-4">
      <div class="page-header">
        <h2 class="gdfg">Portrait Request Form</h2>
      </div>

      <!-- DEBUG OUTPUT -->
      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="alert alert-info">
          <strong>DEBUG INFO:</strong><br>
          Attachments count: <?= count($attachments) ?><br>
          File error: <?= htmlspecialchars($fileexterror ?: 'None') ?><br>
          Customer email: <?= htmlspecialchars($cust_email ?: 'Invalid') ?><br>
        </div>
      <?php endif; ?>
      <!-- END DEBUG OUTPUT -->
      
      <?php if ($fileexterror): ?>
        <p class="text-danger"><?= $fileexterror ?></p>
      <?php endif; ?>

      <form id="petform" class="form-horizontal" name="petform"
            enctype="multipart/form-data" method="POST" action="portreqform.php">
        <fieldset>
          <legend>Contact Details</legend>
          <div class="form-group">
            <label class="col-sm-4 control-label">Your Name</label>
            <div class="col-sm-6">
              <input type="text" name="petform_name" maxlength="80"
                     placeholder="Your Name" id="petform_name" class="form-control" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label">Your E-Mail</label>
            <div class="col-sm-6">
              <input type="email" name="petform_email" maxlength="80"
                     placeholder="Your E-Mail" class="form-control" required>
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>About The Subject(s)</legend>
          <div class="form-group">
            <label class="col-sm-6 control-label">
              Please briefly tell us what you want Anni to paint, be it person(s) or pet(s)!
            </label>
            <div class="col-sm-6">
              <textarea name="petform_message" id="petform_message" rows="8"
                        placeholder="Talk About Who You Want Painted!"
                        class="form-control" required></textarea>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-6 control-label">Source Image</label>
            <div class="col-sm-6">
              <input type="file" id="petpic1" name="petpic1" class="form-control" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-6">Second Source Image (Optional)</label>
            <div class="col-sm-6">
              <input type="file" id="petpic2" name="petpic2" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-6">Third Source Image (Optional)</label>
            <div class="col-sm-6">
              <input type="file" id="petpic3" name="petpic3" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-6">Fourth Source Image (Optional)</label>
            <div class="col-sm-6">
              <input type="file" id="petpic4" name="petpic4" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-6">Fifth Source Image (Optional)</label>
            <div class="col-sm-6">
              <input type="file" id="petpic5" name="petpic5" class="form-control">
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-6">
              <input type="submit" class="purchaseButton" name="button"
                     value="Send Portrait Request!">
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
<?php require_once ('footer.php'); ?>