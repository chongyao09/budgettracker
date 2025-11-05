// Dark Mode Toggle Functionality
(function() {
    'use strict';
    
    // Initialize dark mode on page load
    function initDarkMode() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Use saved preference, or default to dark if no saved preference
        const theme = savedTheme === 'dark' || (!localStorage.getItem('theme')) ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', theme);
        updateToggleButton(theme);
    }
    
    // Update toggle button appearance
    function updateToggleButton(theme) {
        const toggleButtons = document.querySelectorAll('.dark-mode-toggle');
        toggleButtons.forEach(button => {
            const icon = button.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'fas fa-sun';
                    button.setAttribute('aria-label', 'Switch to light mode');
                } else {
                    icon.className = 'fas fa-moon';
                    button.setAttribute('aria-label', 'Switch to dark mode');
                }
            }
        });
    }
    
    // Toggle dark mode
    function toggleDarkMode() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateToggleButton(newTheme);
    }
    
    // Add event listeners to all toggle buttons
    function setupToggleButtons() {
        const toggleButtons = document.querySelectorAll('.dark-mode-toggle');
        toggleButtons.forEach(button => {
            button.addEventListener('click', toggleDarkMode);
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initDarkMode();
            setupToggleButtons();
        });
    } else {
        initDarkMode();
        setupToggleButtons();
    }
    
    // Listen for system theme changes (optional)
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', function(e) {
            // Only update if user hasn't set a preference
            if (!localStorage.getItem('theme')) {
                const newTheme = e.matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                updateToggleButton(newTheme);
            }
        });
    }
})();
