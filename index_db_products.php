<?php
// Add this right after the product query section (around line 51-60)
// Replace the hardcoded <ul class="grid-list product-list" data-filter="all"> section with:
?>

          <ul class="grid-list product-list" data-filter="all">

            <?php 
            if ($products_result && mysqli_num_rows($products_result) > 0) {
                while ($product = mysqli_fetch_assoc($products_result)) {
                    $is_out_of_stock = $product['stock_quantity'] == 0;
                    $is_low_stock = $product['stock_quantity'] > 0 && $product['stock_quantity'] <= $product['low_stock_threshold'];
                    $category_class = strtolower($product['category_slug'] ?? 'accessory');
                    $product_image = !empty($product['image']) ? './assests/images/products/' . $product['image'] : './assests/images/product-1.jpg';
            ?>
            <li class="<?php echo $category_class; ?>">
              <div class="product-card">

                <a href="#" class="card-banner img-holder has-before" style="--width: 300; --height: 300;">
                  <img src="<?php echo htmlspecialchars($product_image); ?>" width="300" height="300" loading="lazy"
                    alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-cover">

                  <ul class="card-action-list">

                    <li>
                      <button class="card-action-btn add-to-cart-btn" 
                              data-product-id="<?php echo $product['id']; ?>"
                              data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                              data-product-price="<?php echo $product['price']; ?>"
                              data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                              aria-label="add to cart" title="Add to cart"
                              <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                        <ion-icon name="bag-handle-outline" aria-hidden="true"></ion-icon>
                      </button>
                    </li>

                    <li>
                      <button class="card-action-btn add-to-wishlist-btn" 
                              data-product-id="<?php echo $product['id']; ?>"
                              data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                              data-product-price="<?php echo $product['price']; ?>"
                              data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                              aria-label="add to wishlist" title="Add to wishlist">
                        <ion-icon name="heart-outline" aria-hidden="true"></ion-icon>
                      </button>
                    </li>

                  </ul>

                  <?php if ($is_out_of_stock): ?>
                    <div class="card-badge">Out of Stock</div>
                  <?php elseif ($is_low_stock): ?>
                    <div class="card-badge orange">Low Stock</div>
                  <?php endif; ?>
                </a>

                <div class="card-content">
                  <h3 class="h3">
                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="card-title"><?php echo htmlspecialchars($product['name']); ?></a>
                  </h3>

                  <div class="card-price">
                    <data class="price" value="<?php echo $product['price']; ?>">Rs <?php echo number_format($product['price'], 2); ?></data>
                  </div>
                  
                  <?php if ($is_low_stock && !$is_out_of_stock): ?>
                    <div class="stock-info" style="color: #ff6b35; font-size: 12px; margin-top: 5px;">
                      Only <?php echo $product['stock_quantity']; ?> left in stock
                    </div>
                  <?php endif; ?>
                </div>

              </div>
            </li>

            <?php 
                }
            } else {
                echo '<li class="no-products" style="padding: 40px; text-align: center; width: 100%;"><p>No products available at the moment. Admin can add products from the admin panel.</p></li>';
            }
            ?>

          </ul>
