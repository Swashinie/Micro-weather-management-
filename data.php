<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('connect.php');

try {
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // OpenWeatherMap API configuration
    $api_key = '5111f1c812b9140e6cf0974f9a5f8e01';
    $api_url = 'http://api.openweathermap.org/data/2.5/forecast';

    // Check if city parameter exists
    if (!isset($_GET['city'])) {
        throw new Exception('City parameter is required');
    }

    $city = trim($_GET['city']);

    // Calculate date range (3 days before and 3 days after)
    $today = new DateTime();
    $startDate = (new DateTime())->modify('-3 days');
    $endDate = (new DateTime())->modify('+3 days');

    // Fetch weather data from OpenWeatherMap API
    $api_query = http_build_query([
        'q' => $city,
        'appid' => $api_key,
        'units' => 'metric',
        'cnt' => 40 // Get maximum data points
    ]);

    $response = @file_get_contents("$api_url?$api_query");
    
    if ($response === FALSE) {
        throw new Exception('Failed to fetch weather data');
    }

    $data = json_decode($response, true);
    
    if (!$data || isset($data['cod']) && $data['cod'] !== '200') {
        throw new Exception($data['message'] ?? 'Failed to fetch weather data');
    }

    // Clear existing data for this city
    $stmt = $conn->prepare("DELETE FROM weather WHERE city = ?");
    $stmt->bind_param("s", $data['city']['name']);
    $stmt->execute();

    // Process forecast data
    $forecast = [];
    $seenDates = [];
    $processedDays = 0;

    foreach ($data['list'] as $item) {
        $date = new DateTime();
        $date->setTimestamp($item['dt']);
        $dateKey = $date->format('Y-m-d');
        
        // Skip if we already have this date or if it's outside our range
        if (isset($seenDates[$dateKey]) || 
            $date < $startDate || 
            $date > $endDate) {
            continue;
        }

        $seenDates[$dateKey] = true;
        $processedDays++;

        // Store weather record
        $stmt = $conn->prepare("
            INSERT INTO weather 
            (city, country, temp, pressure, humidity, speed, weather_icon, 
             day_of_week, weather_description, weather_when)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $dayOfWeek = $date->format('l');
        $weatherWhen = $date->format('Y-m-d H:i:s');
        
        $stmt->bind_param("ssdiidssss",
            $data['city']['name'],
            $data['city']['country'],
            $item['main']['temp'],
            $item['main']['pressure'],
            $item['main']['humidity'],
            $item['wind']['speed'],
            $item['weather'][0]['icon'],
            $dayOfWeek,
            $item['weather'][0]['description'],
            $weatherWhen
        );
        $stmt->execute();

        // Format data for response
        $forecast[] = [
            'city' => $data['city']['name'],
            'country' => $data['city']['country'],
            'temp' => $item['main']['temp'],
            'humidity' => $item['main']['humidity'],
            'pressure' => $item['main']['pressure'],
            'speed' => $item['wind']['speed'],
            'weather_description' => $item['weather'][0]['description'],
            'weather_icon' => $item['weather'][0]['icon'],
            'weather_when' => $weatherWhen,
            'day_of_week' => $dayOfWeek,
            'date' => $dateKey
        ];

        // Break if we have processed 7 days
        if ($processedDays >= 7) {
            break;
        }
    }

    // Sort forecast by date
    usort($forecast, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });

    echo json_encode($forecast);

} catch (Exception $e) {
    error_log("Weather API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
