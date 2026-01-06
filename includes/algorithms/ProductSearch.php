<?php
/**
 * Product Search Algorithm
 * 
 * Advanced search algorithm with:
 * 1. Relevance scoring
 * 2. Fuzzy matching
 * 3. Multi-field search
 * 4. Search ranking
 * 5. Typo tolerance
 */

class ProductSearch {
    
    private $conn;
    private $searchQuery;
    private $searchTerms = [];
    
    /**
     * Initialize search engine
     * 
     * @param mysqli $connection Database connection
     * @param string $query Search query
     */
    public function __construct($conn, $query) {
        $this->conn = $conn;
        $this->searchQuery = trim($query);
        $this->searchTerms = $this->parseSearchQuery($query);
    }
    
    /**
     * Parse search query into individual terms
     * 
     * @param string $query Search query
     * @return array Search terms
     */
    private function parseSearchQuery($query) {
        // Remove special characters
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $query);
        
        // Split into words
        $words = preg_split('/\s+/', strtolower($cleaned));
        
        // Remove empty and short words (< 2 chars)
        return array_filter($words, function($word) {
            return strlen($word) >= 2;
        });
    }
    
    /**
     * Perform search with relevance scoring
     * 
     * Algorithm Steps:
     * 1. Search in product name (highest priority)
     * 2. Search in description (medium priority)
     * 3. Search in category name (medium priority)
     * 4. Calculate relevance score for each match
     * 5. Rank by score (descending)
     * 6. Return sorted results
     * 
     * @return array Search results with scores
     */
    public function search() {
        if (empty($this->searchQuery)) {
            return [];
        }
        
        $results = [];
        $scoredProducts = [];
        
        // Get all active products
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_active = 1";
        
        $result = mysqli_query($this->conn, $query);
        
        if (!$result) {
            // Database error - return empty array
            error_log("ProductSearch Error: " . mysqli_error($this->conn));
            return [];
        }
        
        // Score each product
        while ($product = mysqli_fetch_assoc($result)) {
            $score = $this->calculateRelevanceScore($product);
            
            if ($score > 0) {
                $scoredProducts[] = [
                    'product' => $product,
                    'score' => $score,
                    'matched_fields' => $this->getMatchedFields($product)
                ];
            }
        }
        
        // Sort by score (descending)
        usort($scoredProducts, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Extract products
        foreach ($scoredProducts as $item) {
            $results[] = $item['product'];
        }
        
        return $results;
    }
    
    /**
     * Calculate relevance score for a product
     * 
     * Scoring System:
     * - Exact match in name: 100 points
     * - Partial match in name: 50-80 points
     * - Match in description: 20-40 points
     * - Match in category: 30 points
     * - Multiple term matches: bonus points
     * 
     * @param array $product Product data
     * @return int Relevance score
     */
    private function calculateRelevanceScore($product) {
        $score = 0;
        $productName = strtolower($product['name']);
        $productDesc = strtolower($product['description']);
        $categoryName = strtolower($product['category_name']);
        $searchLower = strtolower($this->searchQuery);
        
        // EXACT MATCH in product name (100 points)
        if ($productName === $searchLower) {
            return 100;
        }
        
        // FULL PHRASE match in name (80 points)
        if (strpos($productName, $searchLower) !== false) {
            $score += 80;
        }
        
        // Individual TERM matches in name
        foreach ($this->searchTerms as $term) {
            if (strpos($productName, $term) !== false) {
                // Calculate position score (earlier = better)
                $position = strpos($productName, $term);
                $positionScore = max(50 - ($position * 2), 20);
                $score += $positionScore;
            }
        }
        
        // Matches in DESCRIPTION (20-40 points)
        foreach ($this->searchTerms as $term) {
            if (strpos($productDesc, $term) !== false) {
                $score += 25;
            }
        }
        
        // Matches in CATEGORY (30 points)
        foreach ($this->searchTerms as $term) {
            if (strpos($categoryName, $term) !== false) {
                $score += 30;
            }
        }
        
        // FUZZY MATCHING (typo tolerance)
        foreach ($this->searchTerms as $term) {
            if (strlen($term) >= 4) {
                // Check for typos using Levenshtein distance
                $words = explode(' ', $productName);
                foreach ($words as $word) {
                    if (strlen($word) >= 4) {
                        $distance = levenshtein($term, $word);
                        // Allow 1-2 character difference for typos
                        if ($distance <= 2 && $distance > 0) {
                            $score += (20 - ($distance * 5));
                        }
                    }
                }
            }
        }
        
        // BONUS for matching ALL search terms
        $matchedTerms = 0;
        $fullText = $productName . ' ' . $productDesc . ' ' . $categoryName;
        foreach ($this->searchTerms as $term) {
            if (strpos($fullText, $term) !== false) {
                $matchedTerms++;
            }
        }
        if ($matchedTerms === count($this->searchTerms) && count($this->searchTerms) > 1) {
            $score += 30; // Bonus for matching all terms
        }
        
        return $score;
    }
    
    /**
     * Get which fields matched for a product
     * 
     * @param array $product Product data
     * @return array Matched field names
     */
    private function getMatchedFields($product) {
        $matched = [];
        $productName = strtolower($product['name']);
        $productDesc = strtolower($product['description']);
        $categoryName = strtolower($product['category_name']);
        
        foreach ($this->searchTerms as $term) {
            if (strpos($productName, $term) !== false) {
                $matched[] = 'name';
            }
            if (strpos($productDesc, $term) !== false) {
                $matched[] = 'description';
            }
            if (strpos($categoryName, $term) !== false) {
                $matched[] = 'category';
            }
        }
        
        return array_unique($matched);
    }
    
    /**
     * Get search suggestions based on partial query
     * 
     * @param int $limit Number of suggestions
     * @return array Suggested search terms
     */
    public function getSuggestions($limit = 5) {
        if (strlen($this->searchQuery) < 2) {
            return [];
        }
        
        $suggestions = [];
        $searchTerm = mysqli_real_escape_string($this->conn, $this->searchQuery);
        
        // Get product names matching query
        $query = "SELECT DISTINCT name FROM products 
                  WHERE is_active = 1 
                  AND name LIKE '$searchTerm%' 
                  LIMIT $limit";
        
        $result = mysqli_query($this->conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $suggestions[] = $row['name'];
        }
        
        return $suggestions;
    }
    
    /**
     * Get popular search terms (based on product names)
     * 
     * @param int $limit Number of terms
     * @return array Popular search terms
     */
    public static function getPopularSearches($conn, $limit = 10) {
        $query = "SELECT name FROM products 
                  WHERE is_active = 1 
                  ORDER BY RAND() 
                  LIMIT $limit";
        
        $result = mysqli_query($conn, $query);
        $searches = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Extract first 2-3 words as search term
            $words = explode(' ', $row['name']);
            $term = implode(' ', array_slice($words, 0, min(3, count($words))));
            $searches[] = $term;
        }
        
        return array_unique($searches);
    }
    
    /**
     * Search with filters
     * 
     * @param array $filters Filters (category, price_min, price_max, etc.)
     * @return array Filtered search results
     */
    public function searchWithFilters($filters = []) {
        $results = $this->search();
        
        // Apply category filter
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $results = array_filter($results, function($product) use ($filters) {
                return $product['category_id'] == $filters['category_id'];
            });
        }
        
        // Apply price range filter
        if (isset($filters['price_min']) && !empty($filters['price_min'])) {
            $results = array_filter($results, function($product) use ($filters) {
                return $product['price'] >= $filters['price_min'];
            });
        }
        
        if (isset($filters['price_max']) && !empty($filters['price_max'])) {
            $results = array_filter($results, function($product) use ($filters) {
                return $product['price'] <= $filters['price_max'];
            });
        }
        
        // Apply stock filter
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $results = array_filter($results, function($product) {
                return $product['stock_quantity'] > 0;
            });
        }
        
        return array_values($results); // Re-index array
    }
    
    /**
     * Get search statistics
     * 
     * @return array Search statistics
     */
    public function getSearchStats() {
        return [
            'query' => $this->searchQuery,
            'terms' => $this->searchTerms,
            'term_count' => count($this->searchTerms),
            'query_length' => strlen($this->searchQuery)
        ];
    }
    
    /**
     * Highlight search terms in text
     * 
     * @param string $text Text to highlight
     * @return string Text with highlighted terms
     */
    public function highlightSearchTerms($text) {
        foreach ($this->searchTerms as $term) {
            $text = preg_replace(
                '/(' . preg_quote($term, '/') . ')/i',
                '<mark class="search-highlight">$1</mark>',
                $text
            );
        }
        return $text;
    }
    
    /**
     * Get algorithm information
     * 
     * @return array Algorithm details
     */
    public static function getAlgorithmInfo() {
        return [
            'name' => 'Product Search Algorithm',
            'version' => '1.0',
            'type' => 'Text Search with Relevance Scoring',
            'scoring' => [
                'exact_match_name' => '100 points',
                'phrase_match_name' => '80 points',
                'term_match_name' => '20-50 points (position-based)',
                'term_match_description' => '25 points',
                'term_match_category' => '30 points',
                'fuzzy_match' => '10-15 points (typo tolerance)',
                'all_terms_bonus' => '30 points'
            ],
            'features' => [
                'Multi-field search (name, description, category)',
                'Relevance scoring and ranking',
                'Fuzzy matching (typo tolerance)',
                'Position-based scoring',
                'Search suggestions',
                'Filter support',
                'Search term highlighting'
            ]
        ];
    }
}
