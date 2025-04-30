<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'weather_app');

// API configuration
define('OPENWEATHER_API_KEY', 'YOUR_API_KEY'); // Replace with your actual API key
define('OPENWEATHER_API_URL', 'http://api.openweathermap.org/data/2.5/forecast');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('UTC');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Cache configuration
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 1800); // 30 minutes

// Logging configuration
define('LOG_ENABLED', true);
define('LOG_FILE', 'weather_app.log');

// Security configuration
define('RATE_LIMIT', 60); // requests per minute
define('RATE_LIMIT_WINDOW', 60); // seconds 