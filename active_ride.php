<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WayMate | Active Trip</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        #map { height: 60vh; width: 100%; z-index: 1; }
        .trip-sheet {
            height: 45vh;
            background: white;
            border-top-left-radius: 2.5rem;
            border-top-right-radius: 2.5rem;
            box-shadow: 0 -10px 25px rgba(0,0,0,0.1);
            position: relative;
            z-index: 10;
            margin-top: -5vh;
        }
    </style>
</head>
<body class="bg-slate-100">

    <div id="map"></div>

    <div class="trip-sheet px-8 py-6">
        <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto mb-6"></div>

        <div class="flex justify-between items-start mb-6">
            <div>
                <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-600 text-[10px] font-black uppercase rounded-full mb-2">Trip Ongoing</span>
                <h1 class="text-2xl font-extrabold text-slate-800">Heading to Campus</h1>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Est. Arrival</p>
                <p class="text-xl font-black text-indigo-600">8 Mins</p>
            </div>
        </div>

        <div class="space-y-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                <p class="text-sm font-medium text-slate-500">Pickup: <span class="text-slate-800 font-bold">Hostel Block A</span></p>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-2 h-2 rounded-full bg-indigo-600"></div>
                <p class="text-sm font-medium text-slate-500">Drop: <span class="text-slate-800 font-bold">Main Library</span></p>
            </div>
        </div>

        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center font-bold text-indigo-600 border border-slate-100 text-lg uppercase">
                    A
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-800">Arjun Sharma</p>
                    <p class="text-[10px] text-slate-400">Maruti Swift • KA-01-1234</p>
                </div>
            </div>
            <a href="tel:0000000000" class="p-3 bg-white rounded-xl shadow-sm border border-slate-100 hover:bg-slate-50 transition">
                📞
            </a>
        </div>

        <button onclick="finishTrip()" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold text-sm shadow-xl active:scale-95 transition">
            End Trip & Verify Location
        </button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize Map (Light Theme)
        const map = L.map('map', { zoomControl: false }).setView([14.8120, 74.1311], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Vehicle Icon
        const carIcon = L.divIcon({
            className: 'car-icon',
            html: `<div style="width:24px; height:24px; background:#4f46e5; border:4px solid white; border-radius:50%; box-shadow:0 0 20px rgba(0,0,0,0.2);"></div>`,
            iconSize: [24, 24]
        });

        let marker = L.marker([14.8120, 74.1311], { icon: carIcon }).addTo(map);

        // Tracking Logic
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition((pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                
                marker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            }, null, { enableHighAccuracy: true });
        }

        function finishTrip() {
            // Here you would check the GPS against the destination
            alert("Checking GPS coordinates... Processing payment.");
        }
    </script>
</body>
</html>