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
$GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent';

// Function to inject dark mode styles into AI response
function injectDarkModeStyles($html) {
    $darkModeStyles = '
<style>
/* Light Mode Styles for AI Response - Font Preservation */
.ai-response-content {
    font-family: "Montserrat", sans-serif !important;
}

.ai-response-content * {
    font-family: inherit !important;
}

/* Dark Mode Styles for AI Response - Font Preservation */
[data-theme="dark"] .ai-response-content {
    color: var(--text-primary) !important;
    background: transparent !important;
    font-family: "Montserrat", sans-serif !important;
}

[data-theme="dark"] .ai-response-content h1,
[data-theme="dark"] .ai-response-content h2,
[data-theme="dark"] .ai-response-content h3,
[data-theme="dark"] .ai-response-content h4,
[data-theme="dark"] .ai-response-content h5,
[data-theme="dark"] .ai-response-content h6 {
    color: var(--text-primary) !important;
    font-family: "Montserrat", sans-serif !important;
}

[data-theme="dark"] .ai-response-content h1 {
    color: #ffa600 !important;
    border-bottom-color: #ffa600 !important;
}

[data-theme="dark"] .ai-response-content h2 {
    color: #ffa600 !important;
}

[data-theme="dark"] .ai-response-content h3 {
    color: #4ade80 !important;
}

[data-theme="dark"] .ai-response-content h4 {
    color: #ffa600 !important;
}

[data-theme="dark"] .ai-response-content p {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .ai-response-content strong,
[data-theme="dark"] .ai-response-content b {
    color: #ffb733 !important;
    background: rgba(255, 166, 0, 0.2) !important;
    padding: 0.15rem 0.3rem !important;
    border-radius: 4px !important;
}

[data-theme="dark"] .ai-response-content em,
[data-theme="dark"] .ai-response-content i {
    color: var(--text-secondary) !important;
}

[data-theme="dark"] .ai-response-content ul,
[data-theme="dark"] .ai-response-content ol {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .ai-response-content li {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .ai-response-content .highlight-box {
    background: var(--bg-tertiary) !important;
    border-left-color: #ffa600 !important;
}

[data-theme="dark"] .ai-response-content .action-steps {
    background: rgba(46, 204, 113, 0.1) !important;
    border-color: #2ecc71 !important;
}

[data-theme="dark"] .ai-response-content .action-steps h4 {
    color: #4ade80 !important;
}

[data-theme="dark"] .ai-response-content .action-steps p,
[data-theme="dark"] .ai-response-content .action-steps li {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .ai-response-content table {
    background: var(--card-bg) !important;
    box-shadow: 0 2px 8px var(--shadow-color) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--text-primary) !important;
}

[data-theme="dark"] .ai-response-content th {
    background: linear-gradient(135deg, #ffa600, #ff8c00) !important;
    color: white !important;
    font-weight: 700 !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
}

[data-theme="dark"] .ai-response-content td {
    border-bottom-color: var(--border-color) !important;
    color: var(--text-primary) !important;
    background: var(--card-bg) !important;
}

[data-theme="dark"] .ai-response-content tr:hover {
    background: rgba(255, 166, 0, 0.1) !important;
}

[data-theme="dark"] .ai-response-content td strong {
    background: transparent !important;
    padding: 0 !important;
    color: #ffb733 !important;
}

[data-theme="dark"] .ai-response-content .success-box {
    background: rgba(46, 204, 113, 0.15) !important;
    border-color: #2ecc71 !important;
    color: #4ade80 !important;
}

[data-theme="dark"] .ai-response-content .warning-box {
    background: rgba(255, 166, 0, 0.15) !important;
    border-color: #ffa600 !important;
    color: #ffb733 !important;
}

[data-theme="dark"] .ai-response-content .info-box {
    background: rgba(13, 110, 253, 0.15) !important;
    border-color: #0d6efd !important;
    color: #6ea8fe !important;
}

[data-theme="dark"] .ai-response-content .tip {
    background: rgba(0, 123, 255, 0.1) !important;
    border-left-color: #007bff !important;
}

[data-theme="dark"] .ai-response-content .tip strong,
[data-theme="dark"] .ai-response-content .tip p {
    color: var(--text-primary) !important;
}

[data-theme="dark"] .ai-response-content .progress-bar {
    background: var(--bg-tertiary) !important;
}

[data-theme="dark"] .ai-response-content .progress-fill {
    background: linear-gradient(90deg, #28a745, #20c997) !important;
}

[data-theme="dark"] .ai-response-content .metric-card {
    background: var(--card-bg) !important;
    border-color: var(--border-color) !important;
    box-shadow: 0 2px 4px var(--shadow-color) !important;
}

[data-theme="dark"] .ai-response-content .metric-value {
    color: #ffa600 !important;
}

[data-theme="dark"] .ai-response-content .metric-label {
    color: var(--text-secondary) !important;
}

[data-theme="dark"] .ai-response-content .budget-summary {
    background: linear-gradient(135deg, #ffa600, #ffb733) !important;
    color: white !important;
}

[data-theme="dark"] .ai-response-content .budget-summary h3 {
    color: white !important;
}

[data-theme="dark"] .ai-response-content .budget-summary p {
    color: white !important;
}

[data-theme="dark"] .ai-response-content .budget-summary strong {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

/* Ensure all elements in AI response have transparent backgrounds in dark mode */
[data-theme="dark"] .ai-response-content *,
[data-theme="dark"] .ai-response-content html,
[data-theme="dark"] .ai-response-content body,
[data-theme="dark"] .ai-response-content div,
[data-theme="dark"] .ai-response-content p,
[data-theme="dark"] .ai-response-content span,
[data-theme="dark"] .ai-response-content table,
[data-theme="dark"] .ai-response-content td,
[data-theme="dark"] .ai-response-content th,
[data-theme="dark"] .ai-response-content tr {
    background: transparent !important;
    background-color: transparent !important;
}
</style>';

    // Wrap the HTML content with a div that has the dark mode class
    $wrappedHtml = '<div class="ai-response-content">' . $html . '</div>';
    
    // Add the dark mode styles before the content
    return $darkModeStyles . $wrappedHtml;
}

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
            'maxOutputTokens' => 4096,
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
        $html = '<html>' . $matches[1] . '</html>';
    } else {
        // If no HTML tags found, wrap the response in basic HTML
        $html = '<html><body>' . nl2br(htmlspecialchars($fullResponse)) . '</body></html>';
    }

    // Inject dark mode styles into the AI response
    return injectDarkModeStyles($html);
}

// Function to generate budget plan prompt
function generateBudgetPlanPrompt($userData, $preset = 'balanced') {
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

    $basePrompt = "You are a financial advisor AI assistant. Based on the following user's financial data, create a comprehensive and personalized budget plan.\n\n";
    $basePrompt .= "USER FINANCIAL DATA:\n";
    $basePrompt .= "- Total Income: $" . number_format($totalIncome, 2) . "\n";
    $basePrompt .= "- Total Expenses: $" . number_format($totalExpenses, 2) . "\n";
    $basePrompt .= "- Current Balance: $" . number_format($balance, 2) . "\n\n";
    $basePrompt .= "SPENDING BREAKDOWN BY CATEGORY:\n" . $categoryAnalysis . "\n";

    // Customize prompt based on preset
    switch ($preset) {
        case 'aggressive-savings':
            $basePrompt .= "FOCUS: Create an aggressive savings plan that maximizes savings while maintaining essential expenses.\n\n";
            $basePrompt .= "Please provide a budget plan that includes:\n";
            $basePrompt .= "1. A recommended 70/20/10 budget allocation (needs/savings/wants)\n";
            $basePrompt .= "2. Aggressive spending cuts in non-essential categories\n";
            $basePrompt .= "3. High savings rate targets (aim for 30-50% of income)\n";
            $basePrompt .= "4. Emergency fund building strategies\n";
            $basePrompt .= "5. Investment recommendations for saved money\n";
            $basePrompt .= "6. Timeline for reaching financial independence\n\n";
            break;

        case 'debt-payoff':
            $basePrompt .= "FOCUS: Create a debt elimination plan that prioritizes paying off debts while maintaining basic living expenses.\n\n";
            $basePrompt .= "Please provide a budget plan that includes:\n";
            $basePrompt .= "1. Debt snowball or avalanche method recommendations\n";
            $basePrompt .= "2. Minimum budget allocation for essential needs only\n";
            $basePrompt .= "3. Maximum allocation to debt payments\n";
            $basePrompt .= "4. Strategies to cut expenses during debt payoff period\n";
            $basePrompt .= "5. Timeline for becoming debt-free\n";
            $basePrompt .= "6. Post-debt financial planning\n\n";
            break;

        case 'retirement':
            $basePrompt .= "FOCUS: Create a retirement-focused budget plan that prioritizes long-term savings and investment.\n\n";
            $basePrompt .= "Please provide a budget plan that includes:\n";
            $basePrompt .= "1. Retirement savings targets (15-20% of income)\n";
            $basePrompt .= "2. Investment allocation recommendations\n";
            $basePrompt .= "3. Retirement timeline planning\n";
            $basePrompt .= "4. Tax-advantaged account strategies\n";
            $basePrompt .= "5. Lifestyle adjustments for retirement savings\n";
            $basePrompt .= "6. Retirement income projections\n\n";
            break;

        case 'emergency-fund':
            $basePrompt .= "FOCUS: Create a plan specifically for building a robust emergency fund.\n\n";
            $basePrompt .= "Please provide a budget plan that includes:\n";
            $basePrompt .= "1. Emergency fund target (3-12 months of expenses)\n";
            $basePrompt .= "2. Timeline for building emergency fund\n";
            $basePrompt .= "3. High savings rate during emergency fund building phase\n";
            $basePrompt .= "4. Strategies to protect emergency fund\n";
            $basePrompt .= "5. Post-emergency fund financial planning\n";
            $basePrompt .= "6. Emergency fund investment options\n\n";
            break;

        case 'investment':
            $basePrompt .= "FOCUS: Create an investment-focused budget plan that balances saving and investing.\n\n";
            $basePrompt .= "Please provide a budget plan that includes:\n";
            $basePrompt .= "1. Investment allocation (stocks, bonds, real estate)\n";
            $basePrompt .= "2. Risk assessment and portfolio diversification\n";
            $basePrompt .= "3. Long-term investment strategies\n";
            $basePrompt .= "4. Tax-efficient investment approaches\n";
            $basePrompt .= "5. Regular investment contribution plans\n";
            $basePrompt .= "6. Investment performance tracking\n\n";
            break;

        default: // balanced
            $basePrompt .= "FOCUS: Create a balanced budget plan using the traditional 50/30/20 rule.\n\n";
            $basePrompt .= "Please provide a budget plan that includes:\n";
            $basePrompt .= "1. A recommended 50/30/20 budget allocation (needs/wants/savings)\n";
            $basePrompt .= "2. Specific spending limits for each category based on their current spending\n";
            $basePrompt .= "3. Areas where they can reduce expenses\n";
            $basePrompt .= "4. Emergency fund recommendations\n";
            $basePrompt .= "5. Short-term and long-term financial goals\n";
            $basePrompt .= "6. Actionable steps to improve their financial situation\n\n";
            break;
    }

    $basePrompt .= "7. Provide a short and concise summary of the budget plan\n\n";
    $basePrompt .= "8. return output only in html with nice css format. make sure it is user read friendly and easy to understand.Do not include any other text or comments.\n\n";
    $basePrompt .= "IMPORTANT: Use 'Montserrat', sans-serif as the font-family for all text elements. Do not use Segoe UI or any other fonts.\n\n";
    $basePrompt .= "Make the advice practical, specific to their situation, and encouraging. Format the response in a clear, easy-to-read structure with headings and bullet points.";

    return $basePrompt;
}

// Function to generate extra ideas prompt
function generateExtraIdeasPrompt($userData, $preset = 'quick-wins') {
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

    $basePrompt = "You are a creative financial advisor AI assistant. Based on the following user's financial situation, provide innovative and practical ideas to save money and increase income.\n\n";
    $basePrompt .= "USER FINANCIAL SITUATION:\n";
    $basePrompt .= "- Monthly Income: $" . number_format($totalIncome, 2) . "\n";
    $basePrompt .= "- Monthly Expenses: $" . number_format($totalExpenses, 2) . "\n";
    $basePrompt .= "- Current Balance: $" . number_format($balance, 2) . "\n";
    $basePrompt .= "- Top Spending Categories: " . $categoryList . "\n\n";

    // Customize prompt based on preset
    switch ($preset) {
        case 'long-term':
            $basePrompt .= "FOCUS: Provide long-term financial strategies that will have significant impact over 1-5 years.\n\n";
            $basePrompt .= "Please provide ideas in these categories:\n";
            $basePrompt .= "1. MAJOR SAVINGS OPPORTUNITIES: Big changes that save hundreds per month\n";
            $basePrompt .= "2. CAREER ADVANCEMENT: Strategies to increase earning potential significantly\n";
            $basePrompt .= "3. INVESTMENT STRATEGIES: Long-term wealth building approaches\n";
            $basePrompt .= "4. BUSINESS OPPORTUNITIES: Starting side businesses or freelance careers\n";
            $basePrompt .= "5. MAJOR LIFESTYLE CHANGES: Fundamental shifts for financial freedom\n\n";
            break;

        case 'side-hustles':
            $basePrompt .= "FOCUS: Provide practical side hustle ideas that can generate significant additional income.\n\n";
            $basePrompt .= "Please provide ideas in these categories:\n";
            $basePrompt .= "1. ONLINE BUSINESS IDEAS: E-commerce, content creation, digital products\n";
            $basePrompt .= "2. SERVICE-BASED HUSTLES: Consulting, tutoring, freelance services\n";
            $basePrompt .= "3. LOCAL BUSINESS OPPORTUNITIES: Physical products or local services\n";
            $basePrompt .= "4. PASSIVE INCOME STREAMS: Businesses that eventually run themselves\n";
            $basePrompt .= "5. SKILL MONETIZATION: Turning existing skills into income sources\n\n";
            break;

        case 'lifestyle':
            $basePrompt .= "FOCUS: Provide lifestyle optimization ideas that reduce expenses through habit changes.\n\n";
            $basePrompt .= "Please provide ideas in these categories:\n";
            $basePrompt .= "1. DAILY HABIT CHANGES: Small changes that add up to big savings\n";
            $basePrompt .= "2. CONSUMPTION OPTIMIZATION: Smarter buying and usage patterns\n";
            $basePrompt .= "3. ENERGY AND UTILITY SAVINGS: Home and utility cost reductions\n";
            $basePrompt .= "4. TRANSPORTATION OPTIMIZATION: Cheaper and more efficient mobility\n";
            $basePrompt .= "5. ENTERTAINMENT ALTERNATIVES: Free or low-cost fun activities\n\n";
            break;

        case 'investment':
            $basePrompt .= "FOCUS: Provide smart investment ideas for growing wealth over time.\n\n";
            $basePrompt .= "Please provide ideas in these categories:\n";
            $basePrompt .= "1. STOCK MARKET BASICS: Beginner-friendly investment approaches\n";
            $basePrompt .= "2. REAL ESTATE INVESTMENT: Property-related wealth building\n";
            $basePrompt .= "3. RETIREMENT ACCOUNTS: Tax-advantaged savings vehicles\n";
            $basePrompt .= "4. ALTERNATIVE INVESTMENTS: Cryptocurrency, commodities, collectibles\n";
            $basePrompt .= "5. AUTOMATED INVESTING: Set-it-and-forget-it wealth building\n\n";
            break;

        case 'tax-saving':
            $basePrompt .= "FOCUS: Provide tax optimization strategies to legally reduce tax burden.\n\n";
            $basePrompt .= "Please provide ideas in these categories:\n";
            $basePrompt .= "1. DEDUCTION STRATEGIES: Maximizing tax deductions and credits\n";
            $basePrompt .= "2. TAX-ADVANTAGED ACCOUNTS: Retirement and education savings\n";
            $basePrompt .= "3. BUSINESS TAX BENEFITS: If self-employed or business owner\n";
            $basePrompt .= "4. YEAR-END TAX PLANNING: Strategies for tax efficiency\n";
            $basePrompt .= "5. LONG-TERM TAX STRATEGIES: Multi-year tax optimization\n\n";
            break;

        default: // quick-wins
            $basePrompt .= "FOCUS: Provide quick, actionable ideas that can save money immediately with minimal effort.\n\n";
            $basePrompt .= "Please provide ideas in these categories:\n";
            $basePrompt .= "1. MONEY-SAVING HACKS: Specific, actionable ways to reduce expenses in their top spending categories\n";
            $basePrompt .= "2. INCOME BOOSTING IDEAS: Creative ways to increase their monthly income\n";
            $basePrompt .= "3. LIFESTYLE OPTIMIZATIONS: Smart changes to their daily habits that save money\n";
            $basePrompt .= "4. INVESTMENT OPPORTUNITIES: Low-risk ways to make their money work for them\n";
            $basePrompt .= "5. SIDE HUSTLE SUGGESTIONS: Realistic ways to earn extra income\n\n";
            break;
    }

    $basePrompt .= "7. Provide a short and concise summary of the ideas\n\n";
    $basePrompt .= "8. return output only in html with nice css format. make sure it is user read friendly and easy to understand.Do not include any other text or comments.\n\n";
    $basePrompt .= "IMPORTANT: Use 'Montserrat', sans-serif as the font-family for all text elements. Do not use Segoe UI or any other fonts.\n\n";
    $basePrompt .= "Make the suggestions specific to their spending patterns and income level. Include both quick wins and long-term strategies.\n\n";
    $basePrompt .= "Return output in HTML with nice CSS format. Make sure it is user-friendly and easy to understand with clear headings, bullet points, and visual formatting.";

    return $basePrompt;
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
        $preset = $input['preset'] ?? null;

        $prompt = '';

        switch ($action) {
            case 'generate_budget_plan':
                $prompt = generateBudgetPlanPrompt($userData, $preset);
                break;

            case 'generate_extra_ideas':
                $prompt = generateExtraIdeasPrompt($userData, $preset);
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
