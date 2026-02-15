<?php
/**
 * Cart Price Calculator Algorithm
 * 
 * Multi-step pricing algorithm that computes the final cart total
 * using tiered discount logic, shipping rules, and tax calculation.
 *
 * Algorithm Steps:
 * 1. Calculate line-item subtotals  (price × quantity)
 * 2. Sum to get cart subtotal
 * 3. Apply tiered discount based on subtotal brackets
 * 4. Calculate shipping (free above threshold, weight-based otherwise)
 * 5. Calculate tax on discounted amount
 * 6. Compute grand total
 *
 * Discount Tiers:
 *   Rs  5,000+  →  5 %
 *   Rs 10,000+  → 10 %
 *   Rs 20,000+  → 15 %
 *
 * Shipping Rules:
 *   Free if subtotal ≥ Rs 3,000
 *   Otherwise Rs 150 flat
 *
 * Tax: 13 % VAT (Nepal standard)
 */

class CartPriceCalculator {

    /** Discount tiers: [minimum_amount => discount_percentage] (sorted desc) */
    private const DISCOUNT_TIERS = [
        20000 => 15,
        10000 => 10,
        5000  => 5,
    ];

    private const FREE_SHIPPING_THRESHOLD = 3000;
    private const FLAT_SHIPPING_FEE       = 150;
    private const TAX_RATE                = 0.13;   // 13 % VAT

    private $items = [];   // cart item rows

    /**
     * Initialize with cart items.
     *
     * @param array $items  Array of cart rows, each must have 'product_price' and 'quantity'
     */
    public function __construct(array $items) {
        $this->items = $items;
    }

    /* ═══════════════ PUBLIC API ═══════════════ */

    /**
     * Run the full pricing pipeline and return a breakdown array.
     *
     * @return array  [subtotal, discount_pct, discount_amount,
     *                 after_discount, shipping, tax, grand_total,
     *                 item_count, savings_message]
     */
    public function calculate() {
        // Step 1-2: line totals → subtotal
        $subtotal = $this->calculateSubtotal();

        // Step 3: tiered discount
        $discountPct    = $this->getDiscountPercentage($subtotal);
        $discountAmount = $this->computeDiscount($subtotal, $discountPct);
        $afterDiscount  = $subtotal - $discountAmount;

        // Step 4: shipping
        $shipping = $this->calculateShipping($subtotal);

        // Step 5: tax (on discounted price)
        $tax = $this->calculateTax($afterDiscount);

        // Step 6: grand total
        $grandTotal = round($afterDiscount + $shipping + $tax, 2);

        return [
            'subtotal'         => round($subtotal, 2),
            'discount_pct'     => $discountPct,
            'discount_amount'  => round($discountAmount, 2),
            'after_discount'   => round($afterDiscount, 2),
            'shipping'         => round($shipping, 2),
            'tax_rate'         => self::TAX_RATE * 100,
            'tax'              => round($tax, 2),
            'grand_total'      => $grandTotal,
            'item_count'       => $this->getTotalQuantity(),
            'savings_message'  => $this->getSavingsMessage($subtotal, $discountPct),
        ];
    }

    /**
     * Get algorithm metadata.
     */
    public static function getAlgorithmInfo() {
        return [
            'name'    => 'Cart Price Calculator Algorithm',
            'version' => '1.0',
            'type'    => 'Tiered Discount + Shipping + Tax Pipeline',
            'discount_tiers' => [
                'Rs 5,000+'  => '5 % off',
                'Rs 10,000+' => '10 % off',
                'Rs 20,000+' => '15 % off',
            ],
            'shipping' => [
                'free_above' => 'Rs 3,000',
                'flat_fee'   => 'Rs 150',
            ],
            'tax' => '13 % VAT',
            'features' => [
                'Line-item subtotal calculation',
                'Multi-tier automatic discounts',
                'Free shipping threshold',
                'VAT tax computation',
                'Savings message generation',
                'Next-tier upsell suggestion',
            ],
        ];
    }

    /* ═══════════════ STEP 1-2: SUBTOTAL ═══════════════ */

    /**
     * Sum of (price × quantity) for every item — Linear scan O(n).
     */
    private function calculateSubtotal() {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $price    = floatval($item['product_price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $subtotal += $price * $quantity;
        }
        return $subtotal;
    }

    private function getTotalQuantity() {
        $count = 0;
        foreach ($this->items as $item) {
            $count += intval($item['quantity'] ?? 1);
        }
        return $count;
    }

    /* ═══════════════ STEP 3: TIERED DISCOUNT ═══════════════ */

    /**
     * Determine discount percentage using Binary-Search-style bracket lookup.
     * Tiers are iterated from highest to lowest; first match wins.
     *
     * @param float $subtotal
     * @return int  Discount percentage (0 if none)
     */
    private function getDiscountPercentage($subtotal) {
        foreach (self::DISCOUNT_TIERS as $threshold => $pct) {
            if ($subtotal >= $threshold) {
                return $pct;
            }
        }
        return 0;
    }

    private function computeDiscount($subtotal, $pct) {
        return ($subtotal * $pct) / 100;
    }

    /* ═══════════════ STEP 4: SHIPPING ═══════════════ */

    /**
     * Shipping: free above threshold, flat fee otherwise.
     */
    private function calculateShipping($subtotal) {
        if ($subtotal >= self::FREE_SHIPPING_THRESHOLD) {
            return 0;
        }
        return self::FLAT_SHIPPING_FEE;
    }

    /* ═══════════════ STEP 5: TAX ═══════════════ */

    /**
     * Tax calculated on the after-discount amount.
     */
    private function calculateTax($amount) {
        return $amount * self::TAX_RATE;
    }

    /* ═══════════════ HELPERS ═══════════════ */

    /**
     * Generate a user-friendly savings / upsell message.
     */
    private function getSavingsMessage($subtotal, $currentPct) {
        if ($currentPct >= 15) {
            return "You're getting our best discount — 15 % off!";
        }

        // Find next tier the user hasn't reached yet
        $tiers = array_reverse(self::DISCOUNT_TIERS, true); // ascending
        foreach ($tiers as $threshold => $pct) {
            if ($subtotal < $threshold) {
                $remaining = ceil($threshold - $subtotal);
                return "Add Rs " . number_format($remaining) . " more to unlock {$pct}% off!";
            }
        }

        return '';
    }
}
