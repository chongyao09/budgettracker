<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check premium status
require_once 'db.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT is_premium FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (!(bool)$user['is_premium']) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Premium subscription required',
            'message' => 'You need a premium subscription to access AI features. Please upgrade to premium.'
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['error' => 'User not found']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Include configuration
require_once 'config.php';

// Google Gemini API configuration
$GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';

// Function to call Google Gemini API
function callGeminiAPI($prompt) {
    global $GEMINI_API_URL;
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 2048,
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Gemini API request failed with code: ' . $httpCode);
    }
    
    $result = json_decode($response, true);
    
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Invalid response from Gemini API');
    }
    
    $fullResponse = $result['candidates'][0]['content']['parts'][0]['text'];
    
    // Extract only content within <html></html> tags
    if (preg_match('/<html[^>]*>(.*?)<\/html>/is', $fullResponse, $matches)) {
        return '<html>' . $matches[1] . '</html>';
    }
    
    // If no HTML tags found, return the full response
    return $fullResponse;
}

// Function to generate budget plan prompt
function generateBudgetPlanPrompt($userData) {
    $totalIncome = $userData['total_income'];
    $totalExpenses = $userData['total_expenses'];
    $balance = $userData['balance'];
    $transactions = $userData['transactions'];
    
    // Analyze spending categories
    $categories = [];
    foreach ($transactions as $transaction) {
        if ($transaction['type'] === 'expense') {
            $category = $transaction['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
            $categories[$category] += floatval($transaction['amount']);
        }
    }
    
    $categoryAnalysis = '';
    foreach ($categories as $category => $amount) {
        $percentage = ($amount / $totalExpenses) * 100;
        $categoryAnalysis .= "- $category: $" . number_format($amount, 2) . " (" . number_format($percentage, 1) . "%)\n";
    }
    
    $prompt = "You are a financial advisor AI assistant. Based on the following user's financial data, create a comprehensive and personalized budget plan.\n\n";
    $prompt .= "USER FINANCIAL DATA:\n";
    $prompt .= "- Total Income: $" . number_format($totalIncome, 2) . "\n";
    $prompt .= "- Total Expenses: $" . number_format($totalExpenses, 2) . "\n";
    $prompt .= "- Current Balance: $" . number_format($balance, 2) . "\n\n";
    $prompt .= "SPENDING BREAKDOWN BY CATEGORY:\n" . $categoryAnalysis . "\n";
    
    $prompt .= "Please provide a summary budget plan that includes:\n";
    $prompt .= "1. A recommended 50/30/20 budget allocation (needs/wants/savings)\n";
    $prompt .= "2. Specific spending limits for each category based on their current spending\n";
    $prompt .= "3. Areas where they can reduce expenses\n";
    $prompt .= "4. Emergency fund recommendations\n";
    $prompt .= "5. Short-term and long-term financial goals\n";
    $prompt .= "6. Actionable steps to improve their financial situation\n\n";
    $prompt .= "7. Provide a short and concise summary of the budget plan\n\n";
    $prompt .= "8. return output only in html with nice css format. make sure it is user read friendly and easy to understand.Do not include any other text or comments.\n\n";
    $prompt .= "Make the advice practical, specific to their situation, and encouraging. Format the response in a clear, easy-to-read structure with headings and bullet points.";
    
    return $prompt;
}

// Function to generate extra ideas prompt
function generateExtraIdeasPrompt($userData) {
    $totalIncome = $userData['total_income'];
    $totalExpenses = $userData['total_expenses'];
    $balance = $userData['balance'];
    $transactions = $userData['transactions'];
    
    // Analyze spending patterns
    $categories = [];
    foreach ($transactions as $transaction) {
        if ($transaction['type'] === 'expense') {
            $category = $transaction['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
            $categories[$category] += floatval($transaction['amount']);
        }
    }
    
    $topCategories = array_slice($categories, 0, 5, true);
    $categoryList = implode(', ', array_keys($topCategories));
    
    $prompt = "You are a creative financial advisor AI assistant. Based on the following user's financial situation, provide innovative and practical ideas to save money and increase income.\n\n";
    $prompt .= "USER FINANCIAL SITUATION:\n";
    $prompt .= "- Monthly Income: $" . number_format($totalIncome, 2) . "\n";
    $prompt .= "- Monthly Expenses: $" . number_format($totalExpenses, 2) . "\n";
    $prompt .= "- Current Balance: $" . number_format($balance, 2) . "\n";
    $prompt .= "- Top Spending Categories: " . $categoryList . "\n\n";
    
    $prompt .= "Please provide creative and practical ideas in these categories:\n";
    $prompt .= "1. MONEY-SAVING HACKS: Specific, actionable ways to reduce expenses in their top spending categories\n";
    $prompt .= "2. INCOME BOOSTING IDEAS: Creative ways to increase their monthly income\n";
    $prompt .= "3. LIFESTYLE OPTIMIZATIONS: Smart changes to their daily habits that save money\n";
    $prompt .= "4. INVESTMENT OPPORTUNITIES: Low-risk ways to make their money work for them\n";
    $prompt .= "5. SIDE HUSTLE SUGGESTIONS: Realistic ways to earn extra income\n\n";
    $prompt .= "7. Provide a short and concise summary of the ideas\n\n";
    $prompt .= "8. return output only in html with nice css format. make sure it is user read friendly and easy to understand.Do not include any other text or comments.\n\n";
    $prompt .= "Make the suggestions specific to their spending patterns and income level. Include both quick wins and long-term strategies.\n\n";
    $prompt .= "Return output in HTML with nice CSS format. Make sure it is user-friendly and easy to understand with clear headings, bullet points, and visual formatting.";
    
    return $prompt;
}

// Main execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action not specified');
        }
        
        $action = $input['action'];
        $userData = $input['user_data'] ?? [];
        
        $prompt = '';
        
        switch ($action) {
            case 'generate_budget_plan':
                $prompt = generateBudgetPlanPrompt($userData);
                break;
                
            case 'generate_extra_ideas':
                $prompt = generateExtraIdeasPrompt($userData);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
        // Call Gemini API
        $response = callGeminiAPI($prompt);
        
        echo json_encode([
            'success' => true,
            'response' => $response
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } finally {
        // Close database connection
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
