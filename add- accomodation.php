Perfect! Let's build a **simple frontend form** (HTML + minimal JS) that can collect the data exactly in the structure
you showed and send it via AJAX or traditional form submission.

---

## ✅ **HTML Frontend Form**

```html
<form id="accommodationForm">
    <h3>Accommodation Info</h3>
    <input type="text" name="name" placeholder="Name" required>
    <textarea name="description" placeholder="Description" required></textarea>
    <select name="city_id" required>
        <option value="">-- Select City --</option>
        <option value="1">Haridwar</option>
        <option value="2">Rishikesh</option>
        <!-- Populate dynamically -->
    </select>
    <input type="text" name="address" placeholder="Address" required>
    <input type="text" name="phone" placeholder="Phone" required>
    <input type="url" name="map_location" placeholder="Map Location URL" required>
    <textarea name="review" placeholder="Review"></textarea>

    <h3>Facilities</h3>
    <div id="facilities">
        <div class="facility">
            <input type="text" name="facilities[0][name]" placeholder="Facility Name">
            <input type="text" name="facilities[0][icon]" placeholder="Icon URL">
        </div>
    </div>
    <button type="button" onclick="addFacility()">+ Add Facility</button>

    <h3>Nearby Places</h3>
    <div id="nearby_places">
        <div class="place">
            <input type="text" name="nearby_places[0][name]" placeholder="Place Name">
            <input type="number" step="0.1" name="nearby_places[0][distance]" placeholder="Distance (km)">
        </div>
    </div>
    <button type="button" onclick="addNearbyPlace()">+ Add Nearby Place</button>

    <br><br>
    <button type="submit">Submit</button>
</form>
```

---

## ✅ **JavaScript to Handle Dynamic Inputs**

```html
<script>
let facilityIndex = 1;
let placeIndex = 1;

function addFacility() {
    const html = `
    <div class="facility">
      <input type="text" name="facilities[${facilityIndex}][name]" placeholder="Facility Name">
      <input type="text" name="facilities[${facilityIndex}][icon]" placeholder="Icon URL">
    </div>`;
    document.getElementById('facilities').insertAdjacentHTML('beforeend', html);
    facilityIndex++;
}

function addNearbyPlace() {
    const html = `
    <div class="place">
      <input type="text" name="nearby_places[${placeIndex}][name]" placeholder="Place Name">
      <input type="number" step="0.1" name="nearby_places[${placeIndex}][distance]" placeholder="Distance (km)">
    </div>`;
    document.getElementById('nearby_places').insertAdjacentHTML('beforeend', html);
    placeIndex++;
}

// Optional: Submit via AJAX
document.getElementById('accommodationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('/your-submit-url', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // If using Laravel
            }
        })
        .then(response => response.json())
        .then(data => alert('Submitted successfully'))
        .catch(error => console.error('Error:', error));
});
</script>
```

---

## ✅ Example Resulting JSON

When this form is submitted, Laravel will automatically convert it to:

```json
{
"name": "Shree Dharamshala",
"description": "...",
"city_id": 2,
"address": "...",
"phone": "...",
"map_location": "...",
"review": "...",
"facilities": [
{ "name": "WiFi", "icon": "wifi-icon.png" },
{ "name": "Parking", "icon": "parking-icon.png" }
],
"nearby_places": [
{ "name": "Ganga Ghat", "distance": 1.2 },
{ "name": "Har Ki Pauri", "distance": 0.8 }
]
}
```

---

Want this done with a framework like Vue or React? Or keep it plain JS?