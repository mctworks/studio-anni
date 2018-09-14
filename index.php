<?php
//main
require_once ('southwinds/phoenixeyes.php');
include ('view/headfoot.php');

//get recent slides
$query = "SELECT name, pieceID, size, slideimg FROM works 
    WHERE (specialstatus IS NULL)
    AND (slideimg IS NOT NULL)
    ORDER BY date DESC
    LIMIT 7";
$statement = $fy->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$statement->closeCursor();
?>

    <body>
        <div class='container'>
            <p class='above-slide'>LATEST FROM ANNI</p>
        </div>
        <div class="container" id='indexslide'>
            <div id="main_slide" class="carousel slide" data-ride='carousel'>
                <ol class="carousel-indicators">
                    <li data-target="#main_slide" data-slide-to="0" class="active"></li>
                    <li data-target="#main_slide" data-slide-to="1"></li>
                    <li data-target="#main_slide" data-slide-to="2"></li>
                    <li data-target="#main_slide" data-slide-to="3"></li>
                    <li data-target="#main_slide" data-slide-to="4"></li>
                    <li data-target="#main_slide" data-slide-to="5"></li>
                    <li data-target="#main_slide" data-slide-to="6"></li>
                    <li data-target="#main_slide" data-slide-to="7"></li>
                </ol>
                <!--slides-->
                <div class="carousel-inner" role="listbox" style="width:100%; height: 510px !important;">
                
                    <div class="item active">
                        <a href='petportrait.php'>
                        <img src='petportslide.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3>Custom Pet Portraits</h3>Let Anni immortalize your furbabies on canvas!
                        </div>   
                    </div>
                    
                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[0]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[0]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[0]['name'] . "</h3>" . $result[0]['size']; ?>
                        </div>
                </div>

                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[1]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[1]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[1]['name'] . "</h3>" . $result[1]['size']; ?>
                        </div>
                </div>

                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[2]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[2]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[2]['name'] . "</h3>" . $result[2]['size']; ?>
                        </div>
                </div>
                

                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[3]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[3]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[3]['name'] . "</h3>" . $result[3]['size']; ?>
                        </div>
                </div>
                                    
                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[4]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[4]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[4]['name'] . "</h3>" . $result[4]['size']; ?>
                        </div>
                </div>
                
                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[5]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[5]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[5]['name'] . "</h3>" . $result[5]['size']; ?>
                        </div>
                </div>
                    
                <div class="item">
                    <a href='<?php echo "piece.php?id=" . $result[6]["pieceID"]?>'>
                        <img src='slides/<?php echo $result[6]['slideimg']; ?>.jpg' class="center-block img-responsive"></a>
                        <div class="carousel-caption">
                            <h3><?php echo $result[6]['name'] . "</h3>" . $result[6]['size']; ?>
                        </div>
                </div>
                    
            </div>
        </div>
    </div>
    
    
                    <a class="left carousel-control" href="#main_slide" role="button" data-slide='prev'>
                        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>

                  <a class="right carousel-control" href="#main_slide" role="button" data-slide='next'>
                        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                  </a>

        
    </body>
</html>