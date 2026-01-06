<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Autocomplete Demo - Furnessence</title>
    
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="./assests/css/style.css?v=14.0">
    <link rel="stylesheet" href="./assests/css/autocomplete.css">
    
    <style>
        .demo-container {
            max-width: 800px;
            margin: 120px auto 60px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .demo-title {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
        }
        
        .demo-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .demo-info h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .demo-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .demo-info li {
            margin: 8px 0;
            color: #555;
        }
        
        .search-demo {
            margin: 40px 0;
        }
        
        .search-form {
            position: relative;
            margin: 0 auto;
            max-width: 600px;
        }
        
        .search-form input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 50px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .search-form input:focus {
            border-color: #ff6b35;
        }
        
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #ff6b35;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .example-searches {
            text-align: center;
            margin-top: 20px;
        }
        
        .example-searches span {
            display: inline-block;
            margin: 5px;
            padding: 8px 15px;
            background: #e9ecef;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .example-searches span:hover {
            background: #dee2e6;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .feature-card {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            text-align: center;
        }
        
        .feature-card ion-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .feature-card h4 {
            margin: 10px 0 5px;
            font-size: 18px;
        }
        
        .feature-card p {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    
<div class="demo-container">
    <h1 class="demo-title">🔍 Search Autocomplete Demo</h1>
    
    <div class="demo-info">
        <h3>✨ Features:</h3>
        <ul>
            <li><strong>Real-time Suggestions</strong> - See product suggestions as you type (minimum 2 characters)</li>
            <li><strong>Keyboard Navigation</strong> - Use ↑ ↓ arrow keys to navigate, Enter to select, Esc to close</li>
            <li><strong>Smart Matching</strong> - Searches in product names, descriptions, and categories</li>
            <li><strong>Highlighted Terms</strong> - Matched words are highlighted in results</li>
            <li><strong>Category Tags</strong> - See which category each product belongs to</li>
            <li><strong>Fast & Responsive</strong> - Results appear instantly with 300ms debounce</li>
        </ul>
    </div>
    
    <div class="search-demo">
        <h3 style="text-align: center; margin-bottom: 20px;">Try it now:</h3>
        <form action="search.php" method="GET" class="search-form">
            <input type="search" 
                   name="q" 
                   placeholder="Type to search... (e.g., sofa, table, chair)" 
                   autocomplete="off">
            <button type="submit" class="search-btn">
                <ion-icon name="search-outline"></ion-icon>
            </button>
        </form>
        
        <div class="example-searches">
            <p style="color: #666; margin-bottom: 10px;">Try these searches:</p>
            <span onclick="searchFor('sofa')">🛋️ Sofa</span>
            <span onclick="searchFor('table')">🪑 Table</span>
            <span onclick="searchFor('chair')">💺 Chair</span>
            <span onclick="searchFor('bed')">🛏️ Bed</span>
            <span onclick="searchFor('lamp')">💡 Lamp</span>
        </div>
    </div>
    
    <div class="features">
        <div class="feature-card">
            <ion-icon name="flash-outline"></ion-icon>
            <h4>Instant Results</h4>
            <p>See suggestions immediately as you type</p>
        </div>
        
        <div class="feature-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <ion-icon name="search-outline"></ion-icon>
            <h4>Smart Search</h4>
            <p>Multi-field search with relevance scoring</p>
        </div>
        
        <div class="feature-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <ion-icon name="ribbon-outline"></ion-icon>
            <h4>Highlighted</h4>
            <p>Matched terms highlighted in results</p>
        </div>
        
        <div class="feature-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <ion-icon name="options-outline"></ion-icon>
            <h4>Keyboard Nav</h4>
            <p>Navigate with arrow keys</p>
        </div>
    </div>
    
    <div style="margin-top: 40px; text-align: center;">
        <a href="index.php" style="display: inline-block; padding: 12px 30px; background: #ff6b35; color: white; text-decoration: none; border-radius: 50px; font-weight: 500;">
            <ion-icon name="home-outline" style="vertical-align: middle;"></ion-icon>
            Back to Home
        </a>
    </div>
</div>

<script src="./assests/js/autocomplete.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<script>
function searchFor(term) {
    const input = document.querySelector('.search-form input[name="q"]');
    input.value = term;
    input.focus();
    
    // Trigger input event to show autocomplete
    const event = new Event('input', { bubbles: true });
    input.dispatchEvent(event);
}
</script>

</body>
</html>
