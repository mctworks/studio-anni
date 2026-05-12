<?php
require_once ('head.php');
require_once ('nav.php');
require_once ('southwinds/mailer.php');

$honeypot = filter_input(INPUT_POST, 'honeypot');
$jsCheck  = filter_input(INPUT_POST, 'js_check');

$form_error   = '';
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($honeypot) || $jsCheck !== 'js_enabled') {
        // Bot — silently do nothing
    } else {
        $cust_name    = filter_input(INPUT_POST, 'contact_name',    FILTER_SANITIZE_SPECIAL_CHARS);
        $cust_email   = filter_input(INPUT_POST, 'contact_email',   FILTER_VALIDATE_EMAIL);
        $cust_message = filter_input(INPUT_POST, 'contact_message', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$cust_email) {
            $form_error = 'Please enter a valid email address.';
        } else {
            $spam_keywords = [
                'seo', 'backlink', 'ranking', 'make money', 'work from home',
                'earn $', 'free trial', 'credit card', 'bitcoin', 'chatGPT', 'AI', 'blockchain',
                'pharmacy', 'replica', 'weight loss', 'loan', 'crypto', 'gen AI', 'LLM', 'deepfake', 'synthetic media'
            ];
            foreach ($spam_keywords as $kw) {
                if (stripos($cust_message, $kw) !== false) {
                    $form_error = "Your message contains invalid content. Whatever it is, we're not buying!";
                    break;
                }
            }
        }

        if (!$form_error) {
            // Rate limit: 60s per IP
            $ip       = $_SERVER['REMOTE_ADDR'];
            $last     = $_SESSION['last_contact_time'][$ip] ?? 0;
            $now      = time();
            if ($now - $last < 60) {
                $form_error = 'Please wait a moment before submitting again.';
            } else {
                $_SESSION['last_contact_time'][$ip] = $now;
            }
        }

        if (!$form_error && isset($_POST['submit'])) {
            $body = "Name: $cust_name\nE-Mail: $cust_email\nMessage:\n$cust_message";
            $ok   = anni_mail(
                'studioannillc@gmail.com',
                'Studio Anni Contact Inquiry',
                $body,
                $cust_email  // reply-to set to customer so Anni can reply directly
            );
            if ($ok) {
                echo '<script>window.location = "contactsent.php";</script>';
                exit;
            } else {
                $form_error = 'Sorry, your message could not be sent. Please try again or email us directly at our gmail account, which begins with "studioannillc".';
            }
        }
    }
}
?>
<div class="main-container">
  <div class="row">
    <div class="col-md-4 col-md-offset-4">
      <div class="page-header">
        <h2 class="gdfg">Contact Us</h2>
      </div>
      <p>We will happily answer any questions you may have.</p>

      <?php if ($form_error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($form_error) ?></div>
      <?php endif; ?>

      <form id="contactform" class="form-horizontal" method="POST" action="contactus.php">
        <fieldset>
          <legend>Contact Details</legend>

          <div class="form-group">
            <label class="col-sm-4 control-label">Your Name</label>
            <div class="col-sm-6">
              <input type="text" name="contact_name" maxlength="80"
                     placeholder="Your Name" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-4 control-label">Your E-Mail</label>
            <div class="col-sm-6">
              <input type="email" name="contact_email" maxlength="80"
                     placeholder="Your E-Mail" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-4 control-label">Your Message</label>
            <div class="col-sm-6">
              <textarea name="contact_message" rows="8"
                        placeholder="Talk To Us! Ask Us Anything!"
                        class="form-control" required></textarea>
            </div>
          </div>

          <!-- Honeypot -->
          <input type="text" name="honeypot" style="display:none" tabindex="-1" autocomplete="off">
          <input type="hidden" name="js_check" value="">
          <script>
            document.addEventListener('DOMContentLoaded', function() {
              document.getElementsByName('js_check')[0].value = 'js_enabled';
            });
          </script>

          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-6">
              <input type="submit" class="purchaseButton" name="submit" value="Submit">
            </div>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
<?php require_once ('footer.php'); ?>