# AI Assistant Setup Guide

## Overview
The AI Assistant feature provides personalized financial advice using Google's Gemini 2.0 Flash model. It offers two main functionalities:
- **Generate Budget Plan**: Creates personalized budget recommendations based on your financial data
- **Generate Extra Ideas**: Suggests creative ways to save money and increase income

## Setup Instructions

### 1. Get Google Gemini API Key
1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Sign in with your Google account
3. Click "Create API Key"
4. Copy the generated API key

### 2. Configure the API Key
1. Open `config.php` in your project root
2. Replace `YOUR_GEMINI_API_KEY_HERE` with your actual API key:
   ```php
   define('GEMINI_API_KEY', 'your_actual_api_key_here');
   ```

### 3. Test the Integration
1. Navigate to the AI Assistant page in your application
2. Try generating a budget plan or extra ideas
3. Check the browser console for any errors

## Features

### Generate Budget Plan
- Analyzes your income, expenses, and spending categories
- Provides 50/30/20 budget allocation recommendations
- Suggests specific spending limits for each category
- Identifies areas for expense reduction
- Recommends emergency fund targets
- Sets short-term and long-term financial goals

### Generate Extra Ideas
- Analyzes your spending patterns
- Suggests money-saving hacks for your top spending categories
- Provides income-boosting ideas
- Recommends lifestyle optimizations
- Suggests investment opportunities
- Offers side hustle suggestions

## Security Notes
- Keep your API key secure and never commit it to version control
- The `config.php` file should be added to `.gitignore`
- Consider using environment variables for production deployments

## Troubleshooting

### Common Issues
1. **"Unauthorized" error**: Make sure you're logged in to the application
2. **API key errors**: Verify your Gemini API key is correct and active
3. **No response**: Check your internet connection and API quota limits

### API Limits
- Google Gemini has usage limits based on your account type
- Free tier has limited requests per day
- Consider upgrading for higher usage limits

## File Structure
```
├── ai-assistant.html          # Frontend AI Assistant page
├── ai-assistant.php           # Backend API handler
├── config.php                 # API key configuration
└── AI_ASSISTANT_SETUP.md     # This setup guide
```

## Support
For issues with the Gemini API, check the [Google AI documentation](https://ai.google.dev/docs).
