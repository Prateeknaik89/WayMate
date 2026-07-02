    /**
 * WayMate - Unified Location & UI Handler
 * Handles: Autocomplete, Secure GPS (via Proxy), and Ticket Menus
 */

document.addEventListener("DOMContentLoaded", function() {

    // ==========================================
    // 1. AUTOCOMPLETE SEARCH ENGINE (SECURE)
    // ==========================================
    const setupSearch = (inputId, resultsId, hiddenId, clearBtnId) => {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        const hidden = document.getElementById(hiddenId);
        const clearBtn = document.getElementById(clearBtnId);
        
        if (!input || !results || !hidden || !clearBtn) return;

        let debounceTimer;

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value;

            if (query.length > 2) {
                clearBtn.classList.remove('hidden');
                
                debounceTimer = setTimeout(() => {
                    // Talking to your secure PHP proxy
                    fetch(`../autocomplete.php?text=${encodeURIComponent(query)}`)
                        .then(res => {
                            if (!res.ok) throw new Error("Network response was not ok");
                            return res.json();
                        })
                        .then(data => {
                            results.innerHTML = '';
                            
                            if (data.results && data.results.length > 0) {
                                results.classList.remove('hidden');
                                
                                data.results.forEach(loc => {
                                    const item = document.createElement('div');
                                    item.className = "p-4 hover:bg-indigo-50 cursor-pointer border-b border-slate-50 last:border-0 transition-colors";
                                    
                                    const mainText = loc.city || loc.name || loc.county || "Unknown Area";
                                    const subText = loc.state ? `, ${loc.state}` : '';
                                    
                                    item.innerHTML = `
                                        <div class="font-bold text-slate-700 text-sm">📍 ${mainText}${subText}</div>
                                        <div class="text-[10px] text-slate-400 mt-1 truncate">${loc.formatted}</div>
                                    `;
                                    
                                   item.onclick = () => {
                                        input.value = `${mainText}${subText}`;
                                        hidden.value = loc.place_id; 
                                        // Save the exact GPS coordinates
                                        if (hiddenId === 'source-id' || hiddenId === 'source_id') {
                                            document.getElementById('source-lat').value = loc.lat;
                                            document.getElementById('source-lon').value = loc.lon;
                                        } else if (hiddenId === 'dest-id' || hiddenId === 'destination_id') {
                                            document.getElementById('dest-lat').value = loc.lat;
                                            document.getElementById('dest-lon').value = loc.lon;
                                        }
                                        // Hide the dropdown
                                        results.classList.add('hidden');
                                    };
                                    results.appendChild(item);
                                });
                            } else {
                                results.classList.add('hidden');
                            }
                        })
                        .catch(err => console.error("Search Proxy Error:", err));
                }, 300);
            } else {
                clearBtn.classList.add('hidden');
                results.classList.add('hidden');
            }
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            hidden.value = '';
            clearBtn.classList.add('hidden');
            results.classList.add('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.classList.add('hidden');
            }
        });
    };

    // Initialize both search boxes
    setupSearch('source-input', 'source-results', 'source-id', 'clear-source');
    setupSearch('dest-input', 'dest-results', 'dest-id', 'clear-dest');


    // ==========================================
    // 2. SECURE GPS DETECTION (SECURE)
    // ==========================================
    const locationBtn = document.getElementById('getLocationBtn');
    if (locationBtn) {
        locationBtn.addEventListener('click', function() {
            const btn = this;
            const iconArea = document.getElementById('location-icon');
            const sourceInput = document.getElementById('source-input');
            const sourceId = document.getElementById('source-id');
            const clearBtn = document.getElementById('clear-source');
            
            btn.classList.add('animate-spin');

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // Talking to your secure reverse geocoding proxy
                        const proxyUrl = `../get_location.php?lat=${lat}&lng=${lng}`;

                        fetch(proxyUrl)
                            .then(response => {
                                if (!response.ok) throw new Error("Proxy error");
                                return response.json();
                            })
                            .then(data => {
                                btn.classList.remove('animate-spin');

                                if (data.features && data.features.length > 0) {
                                    const locationData = data.features[0].properties;
                                    const cityName = locationData.city || locationData.county || locationData.name;

                                    sourceInput.value = cityName + (locationData.state ? ", " + locationData.state : ""); 
                                    sourceId.value = locationData.place_id; 
                                    clearBtn.classList.remove('hidden');

                                    // Success UI Feedback
                                    iconArea.innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
                                    btn.classList.replace('text-indigo-500', 'text-emerald-500');

                                    setTimeout(() => {
                                        iconArea.innerHTML = '<circle cx="12" cy="12" r="3"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="M2 12h2"></path><path d="M20 12h2"></path>';
                                        btn.classList.replace('text-emerald-500', 'text-indigo-500');
                                    }, 3000);
                                }
                            })
                            .catch(error => {
                                console.error("GPS Proxy Error:", error);
                                btn.classList.remove('animate-spin');
                                alert("Server connection failed. Check file paths!");
                            });
                    },
                    function(error) {
                        btn.classList.remove('animate-spin');
                        alert("Could not detect GPS. Please ensure permissions are on.");
                    },
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            } else {
                alert("Browser does not support Geolocation.");
            }
        });
    }


    // ==========================================
    // 3. UI HELPERS (Menus)
    // ==========================================
    window.toggleTicketMenu = function(e) {
        e.preventDefault(); 
        e.stopPropagation(); 
        const menu = document.getElementById('ticket-menu');
        if(menu) menu.classList.toggle('hidden');
    };

    document.addEventListener('click', (e) => {
        const menu = document.getElementById('ticket-menu');
        if (menu && !menu.classList.contains('hidden') && !e.target.closest('#ticket-menu') && !e.target.closest('button[onclick="toggleTicketMenu(event)"]')) {
            menu.classList.add('hidden');
        }
    });

});