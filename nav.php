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
        <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') {
            echo '<li class="active">'; //highlight "Home" when on index page
        } else {
            echo '<li>';
        }?>
        <a href="index.php">Home</a>
      </li>

      <?php if (basename($_SERVER['PHP_SELF']) == 'gallery.php' || basename($_SERVER['PHP_SELF']) == 'special.php') {
          echo '<li class="active dropdown">'; //highlight "Galleries" when on gallery.php or special.php
        }
      else {
          echo '<li class="dropdown">';
      }?>
      <a class="dropdown-toggle" href="#" data-toggle="dropdown">Galleries<span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a href="gallery.php">Main Gallery</a></li>
            <li><a href="special.php">Special Gallery</a></li>
        </ul>
      </li>

    <?php if (basename($_SERVER['PHP_SELF']) == 'petportrait.php' || basename($_SERVER['PHP_SELF']) == 'portraits.php') {
      echo '<li class="active dropdown">'; //highlight "Comissions" when on portraits.php or petportrait.php
    }
  else {
      echo '<li class="dropdown">';
  }?>
  <a class="dropdown-toggle" href="#" data-toggle="dropdown">Commissions<span class="caret"></span></a>
    <ul class="dropdown-menu">
        <li><a href="portraits.php">People Portraits</a></li>
        <li><a href="petportrait.php">Pet Portraits</a></li>
    </ul>
  </li>


<?php if (basename($_SERVER['PHP_SELF']) == 'contactus.php') {
      echo '<li class="active">';
  } //Highlight "Contact Us" on contactus.php
else {
    echo '<li>';
}?>
<a href="contactus.php">Contact Us</a>
</li>
</ul>
</div>
</div>
</div>
