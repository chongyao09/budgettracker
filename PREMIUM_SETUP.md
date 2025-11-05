# Premium Subscription Setup Guide

## Overview
The premium subscription feature allows users to access AI-powered financial features. This includes AI budget planning and money-saving ideas generation.

## Setup Instructions

### 1. Database Migration
Run the SQL migration to add the `is_premium` column to the users table:

```sql
ALTER TABLE `users` ADD COLUMN `is_premium` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`;
```

Or run the provided SQL file:
```bash
mysql -u root -p budgettracker < add-premium-column.sql
```

### 2. Files Created/Modified

**New Files:**
- `premium.html` - Premium subscription page
- `subscribe.php` - Handles subscription requests
- `check-premium.php` - Checks user's premium status
- `add-premium-column.sql` - Database migration script

**Modified Files:**
- `ai-assistant.php` - Added premium status check before processing AI requests
- `ai-assistant.html` - Added paywall for non-premium users
- `dashboard.html` - Added Premium link to navigation

### 3. How It Works

1. **Premium Check**: When users try to access the AI Assistant, the system checks their premium status
2. **Paywall**: Non-premium users see a paywall with an option to subscribe
3. **Subscription**: Users can click "Subscribe Now" on the premium page to upgrade
4. **Access Control**: The `ai-assistant.php` endpoint verifies premium status before processing requests

### 4. Testing

1. Access the AI Assistant page - non-premium users should see the paywall
2. Go to the Premium page and click "Subscribe Now"
3. Return to the AI Assistant page - it should now show the full interface
4. Try generating a budget plan or extra ideas - they should work for premium users

### 5. Premium Features

Premium subscribers get access to:
- AI-Powered Budget Planning
- Money-Saving Ideas Generation
- Personalized Financial Advice
- Priority Customer Support

## Notes

- The subscription system currently sets `is_premium = 1` in the database
- For production, you may want to add:
  - Payment processing integration (Stripe, PayPal, etc.)
  - Subscription expiration dates
  - Cancellation functionality
  - Email notifications

