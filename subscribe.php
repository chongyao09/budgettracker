<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to subscribe'
    ]);
    exit;
}

// Database connection
require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Get payment data from request
$input = json_decode(file_get_contents('php://input'), true);
$paymentMethod = $input['paymentMethod'] ?? null;

// Validate payment method
$validMethods = ['credit_card', 'debit_card', 'paypal', 'touch_n_go'];
if (!in_array($paymentMethod, $validMethods)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid payment method selected'
    ]);
    exit;
}

// Process payment based on method
$paymentProcessed = false;
$paymentMessage = '';

switch ($paymentMethod) {
    case 'credit_card':
    case 'debit_card':
        // For card payments, validate card data
        $cardNumber = $input['cardNumber'] ?? '';
        $expiryDate = $input['expiryDate'] ?? '';
        $cvv = $input['cvv'] ?? '';
        $cardholderName = $input['cardholderName'] ?? '';
        
        if (empty($cardNumber) || empty($expiryDate) || empty($cvv) || empty($cardholderName)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide all card details'
            ]);
            exit;
        }
        
        // In production, you would integrate with payment gateway (Stripe, etc.)
        // For demo purposes, we'll simulate a successful payment
        $paymentProcessed = true;
        $paymentMessage = 'Payment processed successfully via ' . ($paymentMethod === 'credit_card' ? 'Credit Card' : 'Debit Card');
        break;
        
    case 'paypal':
        // In production, you would redirect to PayPal or use PayPal API
        // For demo purposes, we'll simulate a successful payment
        $paymentProcessed = true;
        $paymentMessage = 'Payment processed successfully via PayPal';
        break;
        
    case 'touch_n_go':
        $phone = $input['phone'] ?? '';
        
        if (empty($phone)) {
            echo json_encode([
                'success' => false,
                'message' => 'Please provide your Touch \'n Go phone number'
            ]);
            exit;
        }
        
        // In production, you would integrate with Touch 'n Go API
        // For demo purposes, we'll simulate a successful payment
        $paymentProcessed = true;
        $paymentMessage = 'Payment processed successfully via Touch \'n Go (Phone: ' . htmlspecialchars($phone) . ')';
        break;
}

if ($paymentProcessed) {
    // Update user to premium status
    // In production, you might want to store payment method and transaction details
    $stmt = $conn->prepare('UPDATE users SET is_premium = 1 WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        // Store premium status in session
        $_SESSION['is_premium'] = true;
        $_SESSION['payment_method'] = $paymentMethod;
        
        echo json_encode([
            'success' => true,
            'message' => 'Successfully subscribed to Premium! ' . $paymentMessage . ' You now have access to all premium features.',
            'payment_method' => $paymentMethod
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process subscription. Please try again.'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Payment processing failed. Please try again.'
    ]);
}

$conn->close();
?>

