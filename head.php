<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'vendor/autoload.php';
require_once 'pconfig.php';
require_once 'southwinds/phoenixeyes.php';
require_once 'cart.php';
?>
<!DOCTYPE html>
    <head>
      <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
      <title>Studio Anni - The Art of Anni Thompson</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
		  <meta name="description" content="Art of Atlanta-based artist Anni Thompson, who specializes in realistic acrylic paintings of pet portraits, space, nature and fantasy themes."/>
		  <meta name="keywords" content="art, pet portraits, acrylic paintings, atlanta artists, photorealistic art, pet paintings, animal paintings, space paintings, dog portraits, cat portraits, anni thompson, studio anni"/>
		  <meta name="p:domain_verify" content="d58df890b99f9ba7e1f98ee4cc2fcef0"/>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
      <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
      <link href='https://fonts.googleapis.com/css?family=Della+Respira' rel='stylesheet' type='text/css'>
      <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css?family=Waiting+for+the+Sunrise" rel="stylesheet">
      <link href="view/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet" media="all">
      <link href="view/customsa.css" type="text/css" rel="stylesheet" media="all">
      <script src="view/bootstrap/js/bootstrap.min.js"></script>
      <link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
      <link rel="stylesheet" type="text/css" href="view/bootstrap/css/bootstrap-formhelpers-min.css" media="all">
      <link rel="stylesheet" type="text/css" href="view/bootstrap/css/bootstrapValidator-min.css"/>
      <link rel="stylesheet" type="text/css" href="view/bootstrap/css/bootstrap-side-notes.css" />
      <link rel="stylesheet" type="text/css" href="stripe.css" />
      <script src="view/script.js"></script>
      <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>	
      <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
      <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
      <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
		  <link rel="stylesheet" type="text/css" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
      <link rel="stylesheet" type="text/css" type="text/css" href="https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick-theme.css"/>
      <!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//studioanni.com/matomo/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
	</head>
<body>

