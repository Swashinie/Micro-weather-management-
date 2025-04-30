CREATE DATABASE IF NOT EXISTS weather1;
USE weather1;

DROP TABLE IF EXISTS weather;
CREATE TABLE weather (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(255),
    country VARCHAR(255),
    temp DECIMAL(5,2),
    pressure INT,
    humidity INT,
    speed DECIMAL(5,2),
    weather_icon VARCHAR(255),
    day_of_week VARCHAR(255),
    weather_description VARCHAR(255),
    weather_when DATETIME
); 