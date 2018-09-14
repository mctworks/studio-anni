<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
        <title>Studio Anni - The Art of Anni Thompson</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Art of Atlanta-based artist Anni Thompson, who specializes in realistic acrylic paintings of pet portraits, space, nature and fantasy themes."/>
		<meta name="keywords" content="art, pet portraits, acrylic paintings, atlanta artists, photorealistic art, pet paintings, animal paintings, space paintings, dog portraits, cat portraits, anni thompson, studio anni"/>
		<meta name="p:domain_verify" content="d58df890b99f9ba7e1f98ee4cc2fcef0"/>
        <link href='https://fonts.googleapis.com/css?family=Della+Respira' rel='stylesheet' type='text/css'>
        <link href='https://fonts.googleapis.com/css?family=Oswald:300' rel='stylesheet' type='text/css'>
        <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet"> 
        <link href="view/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="all"> 
        <link href="view/customsa.css" rel="stylesheet" media="all">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <script src="view/bootstrap/js/bootstrap.min.js"></script>
        <script src="https://use.fontawesome.com/4001f06e11.js"></script>
        <link rel="stylesheet" href="view/bootstrap/css/bootstrap-formhelpers-min.css" media="all">
        <link rel="stylesheet" href="view/bootstrap/css/bootstrapValidator-min.css"/>
        <link rel="stylesheet" href="view/bootstrap/css/bootstrap-side-notes.css" />
        <link rel="stylesheet" href="stripe.css" />
        <script src="view/script.js"></script>
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
	</head>
	<!-- navigation menu -->
	<div class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle Navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">
					<img src="view/salogomobile.png" alt="Studio Anni" class="visible-sm">
					<img src="view/salogomobile.png" alt="Studio Anni" class="visible-md">
					<img src="view/salogo2.png" alt="Studio Anni" class="visible-lg">
					<img src='view/salogomobile.png' alt='Studio Anni' class='visible-xs'>
				</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-right">
					<?php if (basename($_SERVER['PHP_SELF']) == 'index.php'){
					echo '<li class="active">';} //highlight "Home" when on index page
					else {echo '<li>';}?>
					<a href="index.php">Home</a>
				</li>                        
				<?php if (basename($_SERVER['PHP_SELF']) == 'gallery.php'){
				echo '<li class="active">';} //highlight "General Gallery" when on gallery.php
				else {echo '<li>';}?>
				<a href="gallery.php">General Gallery</a>
			</li>
			<?php if (basename($_SERVER['PHP_SELF']) == 'petportrait.php'){
			echo '<li class="active">';} //highlight "Pet Portraits" when on index page
			else {echo '<li>';}?>
			<a href="petportrait.php">Pet Portraits</a>
		</li>
		<?php if (basename($_SERVER['PHP_SELF']) == 'special.php'){
		echo '<li class="active">';} //Highlight "Special Gallery" on special.php 
		else {echo '<li>';}?>
		<a href="special.php">Special Gallery</a>
	</li>
</li>
<?php if (basename($_SERVER['PHP_SELF']) == 'contactus.php'){
echo '<li class="active">';} //Highlight "Contact Us" on contactus.php 
else {echo '<li>';}?>
<a href="contactus.php">Contact Us</a>
</li>
</ul>
</div>
</div>
</div>
<!-- footer/social media links -->
<div class="navbar navbar-inverse navbar-fixed-bottom" role="navigation" id="footer">
	<div class="container">
		<div class="navbar-text pull-left">
			<p>&copy;2018 Studio Anni LLC.</p>
		</div> 
		<div class="navbar-text pull-right">
			<a href="https://www.facebook.com/StudioAnniLLC"><img src="fbicon.gif"></a>
			<a href="https://twitter.com/StudioAnni"><img src="twicon.gif"></a>
			<a href="https://www.instagram.com/studio_anni/"><img src="igicon.gif"></a>
		</div>
	</div>
	</div>		