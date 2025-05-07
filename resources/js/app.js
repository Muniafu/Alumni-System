import 'bootstrap';
import Chart from 'chart.js/auto';
window.Chart = Chart;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('alumniMap')) {
        initMap();
    }
});

function initMap() {
    const map = L.map('alumniMap').setView([20, 0], 2);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Fetch alumni locations from API
    axios.get('/api/alumni/locations')
        .then(response => {
            response.data.forEach(alumni => {
                L.marker([alumni.latitude, alumni.longitude])
                    .addTo(map)
                    .bindPopup(`<b>${alumni.name}</b><br>${alumni.location}`);
            });
        })
        .catch(error => {
            console.error('Error fetching alumni locations:', error);
        });
}