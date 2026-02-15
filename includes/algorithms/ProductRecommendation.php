<?php
/**
 * Product Recommendation Algorithm
 * 
 * Content-based + Collaborative filtering algorithm that recommends
 * products based on:
 * 1. Category similarity (same category gets higher score)
 * 2. Price proximity (similar price range scores higher)
 * 3. User behavior (items in cart/wishlist/past orders)
 * 4. Popularity (order frequency)
 * 
 * Scoring System:
 * - Same category:         40 points
 * - Price within 20%:      30 points
 * - Price within 50%:      15 points
 * - In other users' carts: 20 points (collaborative)
 * - Order popularity:      up to 25 points
 * - Recency bonus:         up to 10 points
 */

class ProductRecommendation {

    private $conn;
    private $currentProduct;
    private $userId;

    /**
     * Initialize recommendation engine
     *
     * @param mysqli $conn     Database connection
     * @param array  $product  The product currently being viewed
     * @param int    $userId   Logged-in user ID (0 if guest)
     */
    public function __construct($conn, $product, $userId = 0) {
        $this->conn = $conn;
        $this->currentProduct = $product;
        $this->userId = intval($userId);
    }

    /* ─────────── PUBLIC API ─────────── */

    /**
     * Get recommended products sorted by relevance score (descending).
     *
     * Algorithm Steps:
     * 1. Fetch all active products (exclude current)
     * 2. Score each product with multi-factor weighting
     * 3. Sort by score descending (Merge Sort for stability)
     * 4. Return top N results
     *
     * @param int $limit  Number of recommendations to return
     * @return array       Sorted product rows with attached '_score'
     */
    public function getRecommendations($limit = 4) {
        $candidates = $this->fetchCandidates();
        if (empty($candidates)) return [];

        // Gather collaborative data once (avoid N+1 queries)
        $popularIds   = $this->getPopularProductIds();
        $coCartIds    = $this->getCoBoughtIds();

        // Score every candidate
        $scored = [];
        foreach ($candidates as $p) {
            $score = $this->scoreProduct($p, $popularIds, $coCartIds);
            if ($score > 0) {
                $p['_score'] = $score;
                $scored[] = $p;
            }
        }

        // Stable sort by score descending using Merge Sort
        $scored = $this->mergeSort($scored);

        return array_slice($scored, 0, $limit);
    }

    /**
     * Get algorithm metadata (for documentation / test pages)
     */
    public static function getAlgorithmInfo() {
        return [
            'name'    => 'Product Recommendation Algorithm',
            'version' => '1.0',
            'type'    => 'Content-Based + Collaborative Filtering',
            'scoring' => [
                'same_category'       => '40 points',
                'price_within_20pct'  => '30 points',
                'price_within_50pct'  => '15 points',
                'co_cart_collaborative' => '20 points',
                'order_popularity'    => 'up to 25 points',
                'recency_bonus'       => 'up to 10 points',
            ],
            'features' => [
                'Content-based category matching',
                'Price proximity scoring',
                'Collaborative filtering via co-cart analysis',
                'Popularity weighting from order history',
                'Recency bias for newer products',
                'Merge Sort for stable ranking',
            ],
        ];
    }

    /* ─────────── SCORING ─────────── */

    /**
     * Calculate recommendation score for a single candidate product.
     *
     * @param array $candidate    Product row
     * @param array $popularIds   product_id => order_count
     * @param array $coCartIds    product IDs found in carts of users who also have current product
     * @return int  Score (0 = not recommended)
     */
    private function scoreProduct($candidate, $popularIds, $coCartIds) {
        $score = 0;
        $current = $this->currentProduct;

        // ── 1. Category Similarity (40 pts) ──
        if ($candidate['category_id'] == $current['category_id']) {
            $score += 40;
        }

        // ── 2. Price Proximity ──
        $curPrice = floatval($current['price']);
        $canPrice = floatval($candidate['price']);
        if ($curPrice > 0) {
            $priceDiff = abs($curPrice - $canPrice) / $curPrice;
            if ($priceDiff <= 0.20) {
                $score += 30;           // within 20 %
            } elseif ($priceDiff <= 0.50) {
                $score += 15;           // within 50 %
            }
        }

        // ── 3. Collaborative: co-cart signal (20 pts) ──
        if (in_array($candidate['id'], $coCartIds)) {
            $score += 20;
        }

        // ── 4. Popularity from orders (up to 25 pts) ──
        if (isset($popularIds[$candidate['id']])) {
            // Logarithmic scale so very popular items don't dominate
            $orderCount = $popularIds[$candidate['id']];
            $score += min(25, intval(log($orderCount + 1, 2) * 8));
        }

        // ── 5. Recency bonus (up to 10 pts) ──
        if (!empty($candidate['created_at'])) {
            $daysSinceCreated = (time() - strtotime($candidate['created_at'])) / 86400;
            if ($daysSinceCreated <= 7) {
                $score += 10;
            } elseif ($daysSinceCreated <= 30) {
                $score += 5;
            }
        }

        return $score;
    }

    /* ─────────── DATA HELPERS ─────────── */

    /**
     * Fetch all active products except the current one.
     */
    private function fetchCandidates() {
        $id = intval($this->currentProduct['id']);
        $query = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_active = 1 AND p.id != $id";
        $result = mysqli_query($this->conn, $query);
        $rows = [];
        if ($result) {
            while ($r = mysqli_fetch_assoc($result)) $rows[] = $r;
        }
        return $rows;
    }

    /**
     * Get product IDs ordered most often (popularity map).
     * Returns associative array: product_id => total_quantity_ordered
     */
    private function getPopularProductIds() {
        $map = [];
        $result = mysqli_query($this->conn,
            "SELECT product_id, SUM(quantity) AS total
             FROM order_items
             GROUP BY product_id");
        if ($result) {
            while ($r = mysqli_fetch_assoc($result)) {
                $map[intval($r['product_id'])] = intval($r['total']);
            }
        }
        return $map;
    }

    /**
     * Collaborative filter: find products that appear in carts of users
     * who also have the current product in their cart.
     * ("Users who added X also added Y")
     */
    private function getCoBoughtIds() {
        $ids = [];
        $pid = intval($this->currentProduct['id']);

        $result = mysqli_query($this->conn,
            "SELECT DISTINCT c2.product_id
             FROM cart c1
             JOIN cart c2 ON c1.user_id = c2.user_id
             WHERE c1.product_id = $pid
               AND c2.product_id != $pid");
        if ($result) {
            while ($r = mysqli_fetch_assoc($result)) {
                $ids[] = intval($r['product_id']);
            }
        }
        return $ids;
    }

    /* ─────────── MERGE SORT (stable, descending by _score) ─────────── */

    /**
     * Merge Sort implementation for stable ranking.
     * Time complexity: O(n log n) — preferred over usort for deterministic ordering.
     */
    private function mergeSort($arr) {
        $n = count($arr);
        if ($n <= 1) return $arr;

        $mid   = intval($n / 2);
        $left  = $this->mergeSort(array_slice($arr, 0, $mid));
        $right = $this->mergeSort(array_slice($arr, $mid));

        return $this->merge($left, $right);
    }

    private function merge($left, $right) {
        $result = [];
        $i = $j = 0;
        while ($i < count($left) && $j < count($right)) {
            // Descending order: higher score first
            if ($left[$i]['_score'] >= $right[$j]['_score']) {
                $result[] = $left[$i++];
            } else {
                $result[] = $right[$j++];
            }
        }
        while ($i < count($left))  $result[] = $left[$i++];
        while ($j < count($right)) $result[] = $right[$j++];
        return $result;
    }
}
