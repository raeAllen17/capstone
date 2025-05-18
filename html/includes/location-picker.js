let map, marker;

function openMap() {
  document.getElementById("mapModal").style.display = "block";
  document.getElementById("overlay").style.display = "block";

  if (!map) {
    map = L.map('map').setView([12.8797, 121.7740], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    map.on('click', function (e) {
      const { lat, lng } = e.latlng;
      setMarker(lat, lng);
      reverseGeocode(lat, lng);
    });
  } else {
    map.invalidateSize(); // ⬅️ This fixes layout issues on modal open
  }
}

function closeMap() {
  document.getElementById("mapModal").style.display = "none";
  document.getElementById("overlay").style.display = "none";
}

function searchMap() {
  const query = document.getElementById("mapSearch").value;
  if (!query) return;

  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(data => {
      if (data.length > 0) {
        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);
        map.setView([lat, lon], 16);
        setMarker(lat, lon);
        document.getElementById("locationInput").value = data[0].display_name;
        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lon;
        closeMap();
      } else {
        alert("Location not found.");
      }
    });
}

function setMarker(lat, lng) {
  if (marker) {
    marker.setLatLng([lat, lng]);
  } else {
    marker = L.marker([lat, lng]).addTo(map);
  }
}

function reverseGeocode(lat, lng) {
  fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
    .then(res => res.json())
    .then(data => {
      const address = data.address;
      const barangay = address.suburb || address.village || address.hamlet || '';
      const town = address.town || address.city || address.municipality || '';
      const province = address.state || address.county || '';

      // Format the location string
      const locationStr = [barangay, town, province].filter(Boolean).join(', ');

      // Update the fields
      document.getElementById("locationInput").value = locationStr || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
      document.getElementById("latitude").value = lat;
      document.getElementById("longitude").value = lng;
      closeMap();
    });
}
