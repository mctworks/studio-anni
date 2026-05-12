<?php
include 'head.php';
include 'nav.php';
?>

<div class="main-container" style="position:relative; z-index:10; transform:translateZ(0);">
  <div class="col-sm-10 col-sm-offset-1">
    <h1 class="gallery-header">Your Cart</h1>
    <?php if (empty($pieces)): ?>

      <div class="text-center" style="padding: 4em 0;">
        <p class="lead">Your cart is empty.</p>
        <a href="gallery.php" class="purchaseButton">Browse The Gallery</a>
      </div>

    <?php else: ?>
      
      <p style="color: #fff; margin-bottom: 1em;"><strong>Cart Items (<?php echo count($pieces); ?>):</strong></p>

      <table class="table" style="color:#fff; margin-top:1.5em;">
        <thead>
          <tr>
            <th style="color:#fff;"></th>
            <th style="color:#fff;">Piece</th>
            <th style="color:#fff;">Price</th>
            <th style="color:#fff;"></th>
          </tr>
        </thead>
        <tbody style="display: table-row-group; visibility: visible;">
          <?php foreach ($pieces as $piece): ?>
          <tr style="display: table-row; visibility: visible;">
            <td style="display: table-cell; visibility: visible;">
              <a href="piece.php?id=<?= $piece['pieceID'] ?>">
                <img src="gallery/<?= htmlspecialchars($piece['image']) ?>.jpg"
                     width="70" height="70"
                     style="object-fit:cover; border-radius:4px;"
                     alt="<?= htmlspecialchars($piece['name']) ?>">
              </a>
            </td>
            <td style="display: table-cell; visibility: visible;">
              <a href="piece.php?id=<?= $piece['pieceID'] ?>" style="color:#fff;">
                <?= htmlspecialchars($piece['name']) ?>
              </a>
            </td>
            <td style="display: table-cell; visibility: visible;">$<?= number_format((float)$piece['price'], 2) ?></td>
            
            <td style="display: table-cell; visibility: visible;">
              <a href="viewcart.php?remove=<?= $piece['pieceID'] ?>"
                 style="color:#ff6b6b; font-size:0.85em;">Remove</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php
      $has_special_shipping = false;
      foreach ($pieces as $p) {
          if ($p['shipping'] == 24601.00) {
              $has_special_shipping = true;
              break;
          }
      }
      ?>

      <div class="subtotal" style="text-align:right; font-size:1.3em; margin:1em 0;">
        <strong>Subtotal: $<?= number_format($subtotal, 2) ?></strong>
      </div>

      <?php if ($has_special_shipping): ?>
        <div class="alert alert-warning" style="color:#000; margin-bottom:1em;">
          <strong>Heads up:</strong> One or more items need special shipping arrangements.
          Please <a href="contactus.php">contact us</a> to sort out shipping before checking out.
        </div>
      <?php endif; ?>

      <div class="buttons" style="display:flex; gap:1em; justify-content:flex-end; margin-top:1em;">
        <a href="gallery.php" class="purchaseButton">← Keep Shopping</a>
        <form action="checkout_shipping.php" method="GET">
          <button type="submit"
                  class="purchaseButton"
                  <?= $has_special_shipping ? 'disabled title="Resolve special shipping first"' : '' ?>>
            Checkout →
          </button>
        </form>
      </div>

    <?php endif; ?>
  </div>
</div>

<?php require_once ('footer.php'); ?>