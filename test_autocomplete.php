<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Autocomplete</title>
    <link rel="stylesheet" href="./assests/css/autocomplete.css">
    <style>
        body {
            padding: 50px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .test-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }
        .search-form {
            margin: 20px 0;
        }
        .search-form input {
            width: 100%;
            padding: 12px 50px 12px 15px;
            border: 2px solid #ccc;
            border-radius: 25px;
            font-size: 16px;
        }
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #ff6b35;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
        }
        .test-results {
            margin-top: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Autocomplete Test Page</h1>
        
        <h3>1. Test API Directly:</h3>
        <button onclick="testAPI()">Test get_search_suggestions.php</button>
        <div id="api-result" class="test-results"></div>
        
        <h3>2. Test Search Input:</h3>
        <form action="search.php" method="GET" class="search-form">
            <input type="search" 
                   name="q" 
                   id="test-input"
                   placeholder="Type 'modern' or 'sofa'..." 
                   autocomplete="off">
            <button type="submit" class="search-btn">🔍</button>
        </form>
        
        <div id="js-result" class="test-results"></div>
        
        <h3>3. Database Check:</h3>
        <?php
        require_once 'config.php';
        
        echo "<p><strong>Database Connection:</strong> ";
        if ($conn) {
            echo "✓ Connected</p>";
            
            // Check products
            $query = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
            $result = mysqli_query($conn, $query);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "<p><strong>Active Products:</strong> " . $row['total'] . "</p>";
            }
            
            // Show sample products
            $sample = "SELECT name FROM products WHERE is_active = 1 LIMIT 5";
            $sampleResult = mysqli_query($conn, $sample);
            if ($sampleResult && mysqli_num_rows($sampleResult) > 0) {
                echo "<p><strong>Sample Products:</strong></p><ul>";
                while ($prod = mysqli_fetch_assoc($sampleResult)) {
                    echo "<li>" . htmlspecialchars($prod['name']) . "</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "✗ Not Connected</p>";
        }
        ?>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script src="./assests/js/autocomplete.js"></script>
    
    <script>
    function testAPI() {
        const resultDiv = document.getElementById('api-result');
        resultDiv.innerHTML = 'Loading...';
        
        fetch('get_search_suggestions.php?q=modern')
            .then(response => response.json())
            .then(data => {
                resultDiv.innerHTML = '<strong>API Response:</strong><br>' + 
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                resultDiv.innerHTML = '<strong style="color:red;">Error:</strong> ' + error;
            });
    }
    
    // Monitor input changes
    const input = document.getElementById('test-input');
    const jsResult = document.getElementById('js-result');
    
    input.addEventListener('input', function(e) {
        jsResult.innerHTML = 'Typed: "' + e.target.value + '"<br>Length: ' + e.target.value.length;
    });
    </script>
</body>
</html>
