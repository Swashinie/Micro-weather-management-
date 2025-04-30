const searchBox = document.querySelector(".search-bar");
const searchBtn = document.querySelector(".search button");
const weatherIcon = document.querySelector(".weather-icon");
const weatherCard = document.querySelector(".weather-card");
const forecast = document.querySelector(".forecast");

function setWeatherTheme(description) {
    const body = document.body;
    // Remove all weather classes
    body.classList.remove('weather-clear', 'weather-clouds', 'weather-rain', 'weather-snow', 'weather-thunderstorm');
    
    // Add appropriate class based on weather
    if (description.includes('clear')) {
        body.classList.add('weather-clear');
    } else if (description.includes('cloud')) {
        body.classList.add('weather-clouds');
    } else if (description.includes('rain') || description.includes('drizzle')) {
        body.classList.add('weather-rain');
    } else if (description.includes('snow')) {
        body.classList.add('weather-snow');
    } else if (description.includes('thunder')) {
        body.classList.add('weather-thunderstorm');
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    // Reset time parts for comparison
    today.setHours(0, 0, 0, 0);
    tomorrow.setHours(0, 0, 0, 0);
    yesterday.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);

    if (date.getTime() === today.getTime()) {
        return 'Today';
    } else if (date.getTime() === tomorrow.getTime()) {
        return 'Tomorrow';
    } else if (date.getTime() === yesterday.getTime()) {
        return 'Yesterday';
    }

    return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        month: 'short', 
        day: 'numeric' 
    });
}

async function checkWeather(city) {
    try {
        const response = await fetch(`data.php?city=${encodeURIComponent(city)}`);
        const data = await response.json();
        
        if (!response.ok || data.error) {
            throw new Error(data.message || 'City not found');
        }

        console.log("Data received:", data);
        
        if (!Array.isArray(data) || data.length === 0) {
            throw new Error('No weather data available');
        }

        // Hide error and show weather
        document.querySelector(".error").style.display = "none";
        weatherCard.style.display = "block";

        // Find today's data (middle of the array)
        const currentData = data[Math.floor(data.length / 2)];

        // Set weather theme
        setWeatherTheme(currentData.weather_description.toLowerCase());

        // Update weather information
        document.querySelector(".city").innerHTML = `${currentData.city}, ${currentData.country}`;
        document.querySelector(".temp").innerHTML = `${Math.round(currentData.temp)}°C`;
        document.querySelector(".des").innerHTML = currentData.weather_description;
        weatherIcon.src = `https://openweathermap.org/img/w/${currentData.weather_icon}.png`;
        
        document.querySelector(".humidity").innerHTML = `${currentData.humidity}%`;
        document.querySelector(".wind").innerHTML = `${currentData.speed} m/s`;
        document.querySelector(".pressure").innerHTML = `${currentData.pressure} hPa`;

        // Update forecast with staggered animation
        forecast.innerHTML = "";
        data.forEach((dayData, index) => {
            const card = document.createElement("div");
            card.className = "forecast-card";
            card.style.setProperty('--card-index', index);
            
            // Add relative position class
            const dateText = formatDate(dayData.date);
            const isToday = dateText === 'Today';
            if (isToday) {
                card.classList.add('current-day');
            }
            
            card.innerHTML = `
                <h3>${dateText}</h3>
                <img src="https://openweathermap.org/img/w/${dayData.weather_icon}.png" 
                     alt="${dayData.weather_description}" 
                     class="weather-icon">
                <div class="weather-description">${dayData.weather_description}</div>
                <div class="temp">${Math.round(dayData.temp)}°C</div>
                <p><i class="fas fa-tint"></i> ${dayData.humidity}%</p>
                <p><i class="fas fa-wind"></i> ${dayData.speed} m/s</p>
                <p><i class="fas fa-compress-alt"></i> ${dayData.pressure} hPa</p>
            `;

            forecast.appendChild(card);
        });

    } catch (error) {
        console.error("Error:", error);
        document.querySelector(".error p").textContent = error.message;
        document.querySelector(".error").style.display = "block";
        weatherCard.style.display = "none";
        forecast.innerHTML = "";
    }
}

// Initial call with default city
checkWeather('orai');

// Search functionality
searchBtn.addEventListener("click", (e) => {
    e.preventDefault();
    const city = searchBox.value.trim();
    if (city) {
        checkWeather(city);
    }
});

searchBox.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
        e.preventDefault();
        const city = searchBox.value.trim();
        if (city) {
            checkWeather(city);
        }
    }
});

// Update time every second
setInterval(() => {
    const currentDate = new Date();
    document.querySelector('.time').textContent = currentDate.toLocaleString();
}, 1000);
