<?php
require_once ('head.php');
require_once ('nav.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$cust_name = filter_input(INPUT_POST, 'portraitform_name');
$cust_email = filter_input(INPUT_POST, 'portraitform_email');
$cust_message = filter_input(INPUT_POST, 'portraitform_message');
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
          $from = "ichibancrushuncut@gmail.com"; //use mictho98@baron-zemo.dreamhost.com when this goes live
          $subject ="STUDIO ANNI: Portrait Request";
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
          for($x=0; $x<count($files); $x++){
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
              echo "<p>Mail could not be sent!</p>";
          }
        }
      }

function test(){
  print_r($_FILES);
}
?>

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
.dropzone{
  background-color: #EEE;
  border: #ccc 2px dashed;
  width: 300px;
  height: 250px;
  padding: 8px;
  font-size: 15px;
  line-height: 48px;
  text-align: center;
  color: #000;
}
.dropzone.dragover{
  border-color: #ABC;
  color: #000;
}
#fileElem{
  display: none;
}
div#imgpreview > img{
  width: 50px;
}
</style>

    <form id="portraitform" class="form-horizontal" name="portraitform" enctype="multipart/form-data" method="POST" action="portraitform.php">
    <div class="row row-centered">
    <div class="col-md-4 col-md-offset-4">
    <div class="page-header">
      <h1>Portrait Request Form</h1>
    </div>
    <div><p class="text-danger"><?php echo $fileexterror ?></p></div>
    <fieldset>
    <legend>Contact Details</legend>
    <div class="form-group">
        <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your Name</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
        <input type="text" name="portraitform_name" maxlength="80" placeholder="Your Name" id="portraitform_name" class="form-control" required>
      </div>
    </div>

    <div class="form-group">
        <label class="col-sm-4 col-md-4 col-xs-4 col-lg-4 control-label" for="textinput">Your E-Mail</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
          <input type="email" name="portraitform_email" maxlength="80" placeholder="Your E-Mail" class="form-control" required>
      </div>
    </div>
    </fieldset>

    <fieldset>
    <legend>About The Portrait's Subject(s)</legend>
    <div class="form-group">
      <label class="col-sm-6 control-label" for="textinput">Please briefly tell us what you're looking for with this portrait.</label>
      <div class="col-sm-6 col-md-6 col-xs-6 col-lg-6">
          <textarea name="portraitform_message" id="portraitform_message" rows="8" placeholder="Talk About The Person(s) For This Portrait!" class="form-control" required></textarea>
      </div>
    </div>

    <div class="form-group">
      <div id="dropzone" class="dropzone" ondragover="return false">
        <div id="drag_upload_image">
          <div id="imgpreview" class="imgpreview"></div>
          <p>Drop Image Files Here</p>
          <p>Or</p>
          <p><input type="file" id="fileElem" multiple accept="image/*" onchange="handleFile(this.files)"></p>
        </div>
      </div>
    </div>

    <script>
        let dropArea = document.getElementById('dropzone')

        // dropArea.addEventListener('dragenter', handlerFunction, false)
        // dropArea.addEventListener('dragleave', handlerFunction, false)
        // dropArea.addEventListener('dragover', handlerFunction, false)
        // dropArea.addEventListener('drop', handlerFunction, false)

        //prevent default drag behaviors
        ;['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
          dropArea.addEventListener(eventName, preventDefaults, false)
          document.body.addEventListener(eventName,preventDefaults, false)
        })

        function preventDefaults(e){
          e.preventDefault()
          e.stopPropagation()
        }

        //CSS Highlighting
        ;['dragenter', 'dragover'].forEach(eventName => {
          dropArea.addEventListener(eventName, highlight, false)
        })
        ;['dragleave', 'drop'].forEach(eventName => {
          dropArea.addEventListener(eventName, unhighlight, false)
        })

        function highlight(e){
          dropArea.classList.add('highlight')
        }

        function unhighlight(e){
          dropArea.classList.remove('highlight')
        }

        //handling dropped files
        dropArea.addEventListener('drop', handleDrop, false)

        function handleDrop(e){
          let dt = e.dataTransfer
          let files = dt.files
          handleFiles(files)
        }

        function handleFiles(files){
          files = [...files]
          files.forEach(uploadFile)
          files.forEach(previewFile)
        }

        function uploadFile(file){
          let url = '../studioanni/upload.php';
          let formData = new FormData();

          formData.append('file', file)

          fetch(url, {
            method: 'POST',
            body: formData
          })
          .then(() => console.log(file))
          .catch(() => console.log("Something Is Wrong Somewhere!"))
        }

        function previewFile(file){
          let reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onloadend = function() {
            let img = document.createElement('img');
            img.src = reader.result;
            document.getElementById('imgpreview').appendChild(img);
          }
        }

    </script>

    <label><input type="submit" class="purchaseButton" name="button" id="submit" value="Send Portrait Request!" /></label>
    </fieldset>
    </div></div>
</form>
<?php require_once ('footer.php');?>
