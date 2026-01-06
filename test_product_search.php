<?php
/**
 * Product Search Algorithm - Demo & Test Page
 * 
 * Demonstrates the product search algorithm with relevance scoring
 */

require_once 'config.php';
require_once 'includes/algorithms/ProductSearch.php';

// Test search queries
$test_queries = [
    'sofa',
    'wooden',
    'chair',
    'table dining',
    'bed modern'
];

// Get algorithm info
$algo_info = ProductSearch::getAlgorithmInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search Algorithm Demo - Furnessence</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            color: #2c3e50;
            font-size: 36px;
            margin-bottom: 10px;
            border-bottom: 4px solid #3498db;
            padding-bottom: 15px;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 18px;
            margin-bottom: 30px;
        }
        .algo-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .algo-info h2 {
            margin-bottom: 20px;
            font-size: 28px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-card {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .info-card h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }
        .search-demo {
            background: white;
            padding: 40px;
            border-radius: 15px;
            margin: 30px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .search-demo h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 26px;
        }
        .search-input-wrapper {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .search-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #3498db;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        .search-input:focus {
            outline: none;
            border-color: #2980b9;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .search-btn {
            padding: 15px 40px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .search-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        .test-queries {
            margin-bottom: 20px;
        }
        .test-query-btn {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            background: #ecf0f1;
            border: 2px solid #bdc3c7;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .test-query-btn:hover {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        .results-section {
            margin-top: 30px;
        }
        .result-count {
            color: #27ae60;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding: 15px;
            background: #d5f4e6;
            border-left: 5px solid #27ae60;
            border-radius: 5px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            border: 2px solid #ecf0f1;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #3498db;
        }
        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }
        .product-info {
            padding: 20px;
        }
        .product-name {
            color: #2c3e50;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            min-height: 50px;
        }
        .product-price {
            color: #27ae60;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .product-category {
            display: inline-block;
            padding: 5px 15px;
            background: #3498db;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .relevance-score {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f39c12;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        .scoring-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .scoring-table th {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .scoring-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        .scoring-table tr:last-child td {
            border-bottom: none;
        }
        .scoring-table tr:hover {
            background: #f8f9fa;
        }
        .feature-badge {
            display: inline-block;
            padding: 8px 15px;
            background: #27ae60;
            color: white;
            border-radius: 5px;
            margin: 5px;
            font-size: 13px;
        }
        .formula-box {
            background: #2c3e50;
            color: #2ecc71;
            padding: 25px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 20px 0;
            overflow-x: auto;
            line-height: 1.8;
        }
        .highlight {
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
        }
        mark.search-highlight {
            background: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        .no-results i {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container">
    <h1>🔍 Product Search Algorithm</h1>
    <p class="subtitle">Advanced search with relevance scoring, fuzzy matching, and intelligent ranking</p>

    <!-- Algorithm Information -->
    <div class="algo-info">
        <h2>📊 Algorithm Specifications</h2>
        <div class="info-grid">
            <div class="info-card">
                <h3>Algorithm Name</h3>
                <p><?php echo $algo_info['name']; ?></p>
            </div>
            <div class="info-card">
                <h3>Version</h3>
                <p><?php echo $algo_info['version']; ?></p>
            </div>
            <div class="info-card">
                <h3>Type</h3>
                <p><?php echo $algo_info['type']; ?></p>
            </div>
        </div>

        <h3 style="margin-top: 30px; margin-bottom: 15px; font-size: 22px;">Scoring System:</h3>
        <table class="scoring-table">
            <thead>
                <tr>
                    <th>Match Type</th>
                    <th>Score</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($algo_info['scoring'] as $type => $score): ?>
                <tr>
                    <td><strong><?php echo ucwords(str_replace('_', ' ', $type)); ?></strong></td>
                    <td><span class="highlight"><?php echo $score; ?></span></td>
                    <td>
                        <?php
                        $descriptions = [
                            'exact_match_name' => 'Product name exactly matches search query',
                            'phrase_match_name' => 'Search phrase found in product name',
                            'term_match_name' => 'Individual search terms found in name (earlier position = higher score)',
                            'term_match_description' => 'Search terms found in product description',
                            'term_match_category' => 'Search terms found in category name',
                            'fuzzy_match' => 'Similar words (1-2 character difference for typos)',
                            'all_terms_bonus' => 'Bonus when all search terms are matched'
                        ];
                        echo $descriptions[$type] ?? '';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 style="margin-top: 30px; margin-bottom: 15px; font-size: 22px;">Features:</h3>
        <?php foreach ($algo_info['features'] as $feature): ?>
            <span class="feature-badge">✓ <?php echo $feature; ?></span>
        <?php endforeach; ?>
    </div>

    <!-- How It Works -->
    <div class="search-demo">
        <h2>🔧 How The Algorithm Works</h2>
        
        <div class="formula-box">
// Step 1: Parse search query into individual terms<br>
$terms = parseSearchQuery($query);<br>
// Example: "wooden table" → ["wooden", "table"]<br>
<br>
// Step 2: For each product, calculate relevance score<br>
foreach ($products as $product) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;$score = 0;<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Exact match in name (100 points)<br>
&nbsp;&nbsp;&nbsp;&nbsp;if ($product['name'] === $searchQuery) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$score = 100;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Phrase match in name (80 points)<br>
&nbsp;&nbsp;&nbsp;&nbsp;if (contains($product['name'], $searchQuery)) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$score += 80;<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Term matches in name (20-50 points each)<br>
&nbsp;&nbsp;&nbsp;&nbsp;foreach ($terms as $term) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$position = indexOf($product['name'], $term);<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$score += max(50 - ($position * 2), 20);<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Term matches in description (25 points each)<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Term matches in category (30 points each)<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Fuzzy matching for typos (10-15 points)<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Bonus for matching all terms (+30 points)<br>
}<br>
<br>
// Step 3: Sort by score (descending)<br>
sortByScore($products);<br>
<br>
// Step 4: Return ranked results<br>
return $products;
        </div>
    </div>

    <!-- Live Search Demo -->
    <div class="search-demo">
        <h2>🎯 Live Search Demo</h2>
        <p style="margin-bottom: 20px; color: #7f8c8d;">Try searching for products below. Results are ranked by relevance score.</p>
        
        <form method="GET" action="">
            <div class="search-input-wrapper">
                <input type="text" 
                       name="demo_query" 
                       class="search-input" 
                       placeholder="Search for products (e.g., sofa, wooden chair, dining table)..."
                       value="<?php echo isset($_GET['demo_query']) ? htmlspecialchars($_GET['demo_query']) : ''; ?>"
                       autofocus>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>

        <div class="test-queries">
            <strong>Quick Test Queries:</strong>
            <?php foreach ($test_queries as $test_query): ?>
                <a href="?demo_query=<?php echo urlencode($test_query); ?>" class="test-query-btn">
                    <?php echo htmlspecialchars($test_query); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_GET['demo_query']) && !empty($_GET['demo_query'])): ?>
            <?php
            $demo_query = $_GET['demo_query'];
            $searchEngine = new ProductSearch($conn, $demo_query);
            $results = $searchEngine->search();
            $stats = $searchEngine->getSearchStats();
            ?>
            
            <div class="results-section">
                <div class="result-count">
                    <i class="fas fa-check-circle"></i>
                    Found <strong><?php echo count($results); ?></strong> result(s) for 
                    "<strong><?php echo htmlspecialchars($demo_query); ?></strong>"
                    <br>
                    <small style="font-size: 14px; font-weight: normal; opacity: 0.8;">
                        Search terms: <?php echo implode(', ', $stats['terms']); ?>
                    </small>
                </div>

                <?php if (empty($results)): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>No products found</h3>
                        <p>Try different keywords or check the spelling</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php 
                        $rank = 1;
                        foreach ($results as $product): 
                            // Calculate score for display
                            $tempSearch = new ProductSearch($conn, $demo_query);
                            $reflection = new ReflectionClass($tempSearch);
                            $method = $reflection->getMethod('calculateRelevanceScore');
                            $method->setAccessible(true);
                            $score = $method->invoke($tempSearch, $product);
                        ?>
                        <div class="product-card">
                            <div class="relevance-score">
                                #<?php echo $rank++; ?> - Score: <?php echo $score; ?>
                            </div>
                            <img src="<?php echo htmlspecialchars($product['image'] ?: 'assests/images/products/product-1.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-info">
                                <div class="product-name">
                                    <?php echo $searchEngine->highlightSearchTerms(htmlspecialchars($product['name'])); ?>
                                </div>
                                <div class="product-price">
                                    Rs <?php echo number_format($product['price'], 2); ?>
                                </div>
                                <?php if (!empty($product['category_name'])): ?>
                                    <span class="product-category">
                                        <?php echo $searchEngine->highlightSearchTerms(htmlspecialchars($product['category_name'])); ?>
                                    </span>
                                <?php endif; ?>
                                <p style="margin-top: 10px; font-size: 13px; color: #7f8c8d; line-height: 1.6;">
                                    <?php 
                                    $desc = substr($product['description'], 0, 100);
                                    echo $searchEngine->highlightSearchTerms(htmlspecialchars($desc)) . '...'; 
                                    ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Usage Example -->
    <div class="search-demo">
        <h2>💻 Usage in Your Code</h2>
        
        <h3 style="margin: 20px 0 10px 0;">Basic Search:</h3>
        <div class="formula-box">
require_once 'includes/algorithms/ProductSearch.php';<br>
<br>
// Create search engine instance<br>
$searchEngine = new ProductSearch($conn, $searchQuery);<br>
<br>
// Perform search and get ranked results<br>
$results = $searchEngine->search();<br>
<br>
// Display results<br>
foreach ($results as $product) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;echo $product['name'];<br>
&nbsp;&nbsp;&nbsp;&nbsp;echo $product['price'];<br>
}
        </div>

        <h3 style="margin: 20px 0 10px 0;">Search with Filters:</h3>
        <div class="formula-box">
$filters = [<br>
&nbsp;&nbsp;&nbsp;&nbsp;'category_id' => 5,<br>
&nbsp;&nbsp;&nbsp;&nbsp;'price_min' => 5000,<br>
&nbsp;&nbsp;&nbsp;&nbsp;'price_max' => 20000,<br>
&nbsp;&nbsp;&nbsp;&nbsp;'in_stock' => true<br>
];<br>
<br>
$results = $searchEngine->searchWithFilters($filters);
        </div>

        <h3 style="margin: 20px 0 10px 0;">Get Search Suggestions:</h3>
        <div class="formula-box">
$suggestions = $searchEngine->getSuggestions(5);
        </div>
    </div>

    <!-- File Location -->
    <div class="search-demo" style="background: #d5f4e6; border-left: 5px solid #27ae60;">
        <h2 style="color: #27ae60;">📁 File Locations</h2>
        <p><strong>Algorithm File:</strong> <code>includes/algorithms/ProductSearch.php</code></p>
        <p><strong>Demo File:</strong> <code>test_product_search.php</code> (this page)</p>
        <p><strong>Used In:</strong> <code>search.php</code> (search results page)</p>
    </div>

    <div style="text-align: center; margin: 50px 0; padding: 30px; background: white; border-radius: 15px;">
        <h2 style="color: #27ae60; margin-bottom: 15px;">✅ Product Search Algorithm is Ready!</h2>
        <p style="color: #7f8c8d; margin-bottom: 25px;">Test it on the search page or use the demo above</p>
        <a href="search.php" style="display: inline-block; padding: 15px 40px; background: #3498db; color: white; text-decoration: none; border-radius: 10px; font-weight: bold; transition: all 0.3s;">
            Go to Search Page
        </a>
    </div>
</div>

</body>
</html>
