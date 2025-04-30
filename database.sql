-- Create the weather database
CREATE DATABASE IF NOT EXISTS weather_app;
USE weather_app;

-- Create table for cities
CREATE TABLE IF NOT EXISTS cities (
    city_id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    country_code VARCHAR(2) NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_city (city_name, country_code)
);

-- Create table for weather records
CREATE TABLE IF NOT EXISTS weather_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    temperature DECIMAL(5, 2) NOT NULL,
    humidity INT NOT NULL,
    pressure INT NOT NULL,
    wind_speed DECIMAL(5, 2) NOT NULL,
    weather_description VARCHAR(100) NOT NULL,
    weather_icon VARCHAR(10) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(city_id),
    INDEX idx_city_date (city_id, recorded_at)
);

-- Create table for user searches
CREATE TABLE IF NOT EXISTS search_history (
    search_id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    search_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    successful BOOLEAN DEFAULT TRUE,
    INDEX idx_search_time (search_timestamp)
);

-- Create table for favorite cities
CREATE TABLE IF NOT EXISTS favorite_cities (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    user_ip VARCHAR(45) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(city_id),
    UNIQUE KEY unique_favorite (city_id, user_ip)
);

-- Create views for analytics
CREATE OR REPLACE VIEW popular_cities AS
SELECT 
    c.city_name,
    c.country_code,
    COUNT(s.search_id) as search_count
FROM cities c
JOIN weather_records w ON c.city_id = w.city_id
JOIN search_history s ON c.city_name = s.city_name
GROUP BY c.city_id
ORDER BY search_count DESC;

CREATE OR REPLACE VIEW temperature_trends AS
SELECT 
    c.city_name,
    DATE(w.recorded_at) as date,
    AVG(w.temperature) as avg_temp,
    MIN(w.temperature) as min_temp,
    MAX(w.temperature) as max_temp
FROM cities c
JOIN weather_records w ON c.city_id = w.city_id
GROUP BY c.city_id, DATE(w.recorded_at)
ORDER BY date DESC; 