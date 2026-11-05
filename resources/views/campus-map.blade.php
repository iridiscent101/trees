<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore verified tree locations across the NVSU Bayombong campus.">
    <title>Campus Map | NVSU Tree Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <link rel="stylesheet" href="{{ asset('css/campus-map.css') }}?v={{ filemtime(public_path('css/campus-map.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-header.css') }}?v={{ filemtime(public_path('css/public-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-polish.css') }}">
</head>
<body>
    <x-public-header active="map" />

    <main>
        <section class="page-heading"><h1>Campus Map</h1><p>Explore verified inventory trees and provisional survey coordinates across the NVSU Bayombong Campus.</p></section>
        <div class="map-layout">
            <aside class="filter-panel panel">
                <h2>Filter Trees on Map</h2>
                <form id="map-filters">
                    <label for="map-search">Search Tree</label>
                    <div class="search-wrap"><input id="map-search" name="q" type="search" placeholder="Name, scientific name, ID..."><span aria-hidden="true">⌕</span></div>

                    <label for="campus-area">Campus Area</label>
                    <select id="campus-area" name="area"><option value="">All Areas</option>@foreach($campusAreas as $area)<option value="{{ $area->id }}">{{ $area->name }}</option>@endforeach</select>

                    <label for="tree-condition">Tree Condition</label>
                    <select id="tree-condition" name="condition"><option value="">All Conditions</option>@foreach($conditions as $condition)<option value="{{ $condition->value }}">{{ str($condition->value)->title() }}</option>@endforeach</select>

                    <fieldset>
                        <legend>Display Options</legend>
                        <label><input id="show-markers" type="checkbox" checked> Show Verified Tree Markers</label>
                        <label><input id="cluster-markers" type="checkbox" checked> Cluster Nearby Markers</label>
                        <input name="survey" type="hidden" value="0">
                        <label><input id="show-survey" name="survey" type="checkbox" value="1" checked> Show Survey Coordinates</label>
                    </fieldset>
                    <button class="apply-button" type="submit">▽ &nbsp; Apply Filters</button>
                    <button class="reset-button" type="reset">↻ &nbsp; Reset Filters</button>
                </form>

                <div class="coordinate-picker" aria-label="Map coordinate picker">
                    <button id="coordinate-picker-toggle" class="coordinate-picker-toggle" type="button" aria-controls="coordinate-picker-panel" aria-expanded="false" aria-pressed="false">
                        <span aria-hidden="true">⌖</span> Pick coordinates
                    </button>
                    <section id="coordinate-picker-panel" class="coordinate-picker-panel" hidden>
                        <div>
                            <strong>Selected coordinates</strong>
                            <span>Click the map, then drag the pin to fine-tune.</span>
                        </div>
                        <dl>
                            <div><dt>Latitude</dt><dd id="picked-latitude">—</dd></div>
                            <div><dt>Longitude</dt><dd id="picked-longitude">—</dd></div>
                        </dl>
                        <button id="copy-coordinates" type="button" disabled>Copy latitude &amp; longitude</button>
                        <p id="coordinate-copy-status" role="status" aria-live="polite"></p>
                    </section>
                </div>
            </aside>

            <section class="campus-map" aria-label="Interactive map of NVSU Bayombong Campus">
                <div id="nvsu-map"></div>
                <div id="map-loading" class="map-loading"><span></span><p>Loading mapped tree locations…</p></div>
                <div id="map-error" class="map-error" hidden><strong>Map data could not be loaded.</strong><button type="button">Try again</button></div>
            </section>

            <aside class="map-sidebar">
                <section class="panel tree-information">
                    <h2>Tree Information</h2>
                    <div id="empty-selection"><p>Select a marker on the map to view its verified record.</p><div class="tree-symbol">♣</div><div class="selection-note">Select a tree marker<br>to view information.</div></div>
                    <div id="tree-details" hidden>
                        <span class="detail-id"></span><h3 class="detail-name"></h3><em class="detail-scientific"></em>
                        <p><b>Record type</b><span class="detail-record-type"></span></p><p><b>Campus area</b><span class="detail-area"></span></p><p><b>Location</b><span class="detail-location"></span></p><p><b>Condition</b><span class="detail-condition"></span></p><p class="detail-height-row"><b>Recorded height</b><span class="detail-height"></span></p>
                        <a class="detail-profile" href="#">View Tree Profile</a>
                    </div>
                </section>
                <section class="panel map-summary"><h2>Map Summary</h2><dl><div><dt>All Mapped Points</dt><dd data-summary="mapped_points">—</dd></div><div><dt>Survey Coordinates</dt><dd data-summary="survey_points">—</dd></div><div><dt>Verified Records</dt><dd data-summary="verified_records">—</dd></div><div><dt>Invalid XLS Rows</dt><dd data-summary="invalid_rows">—</dd></div></dl></section>
                <section class="panel full-legend"><h2>Map Legend</h2><ul><li><i class="survey">●</i> Provisional Survey Point</li>@foreach($conditions as $condition)<li><i class="{{ $condition->value }}">●</i> {{ str($condition->value)->title() }}</li>@endforeach</ul></section>
            </aside>
        </div>
        <aside class="data-note"><span><b>Note:</b> Blue points are provisional XLS survey coordinates; tree information can be completed later.</span><span id="map-updated">Live inventory data</span></aside>
    </main>

    <footer><span>© {{ date('Y') }} Nueva Vizcaya State University.</span><span>College of Information Technology Education &nbsp; | &nbsp; College of Forestry</span><span>Map data © OpenStreetMap contributors.</span></footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        (() => {
            const center = [16.480696, 121.140771];
            const conditionColors = { healthy:'#159447', fair:'#eab308', poor:'#f97316', damaged:'#dc2626', diseased:'#7c3aed', dead:'#714329', other:'#737b77', unknown:'#178f9e' };
            const map = L.map('nvsu-map', { center, zoom:18, zoomControl:true });
            const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom:20,
                // OSM's standard layer has no native level-20 tiles. Leaflet can
                // upscale level 19 when a cluster spiderfies at maximum zoom.
                maxNativeZoom:19,
                keepBuffer:4,
                attribution:'&copy; OpenStreetMap contributors',
            }).addTo(map);
            const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom:20,
                keepBuffer:4,
                attribution:'Tiles &copy; Esri',
            });
            L.control.layers({ Map:street, Satellite:satellite }, null, { position:'topleft' }).addTo(map);

            const form = document.querySelector('#map-filters');
            const loading = document.querySelector('#map-loading');
            const error = document.querySelector('#map-error');
            const empty = document.querySelector('#empty-selection');
            const details = document.querySelector('#tree-details');
            const mapContainer = document.querySelector('.campus-map');
            const coordinatePickerToggle = document.querySelector('#coordinate-picker-toggle');
            const coordinatePickerPanel = document.querySelector('#coordinate-picker-panel');
            const pickedLatitude = document.querySelector('#picked-latitude');
            const pickedLongitude = document.querySelector('#picked-longitude');
            const copyCoordinates = document.querySelector('#copy-coordinates');
            const coordinateCopyStatus = document.querySelector('#coordinate-copy-status');
            let clusterLayer = L.markerClusterGroup({ showCoverageOnHover:false, maxClusterRadius:45 });
            let plainLayer = L.layerGroup();
            let markers = [];
            let markerRecords = [];
            let lastTrees = [];
            let coordinatePickerEnabled = false;
            let coordinatePickerMarker = null;
            let selectedCoordinates = null;
            clusterLayer.on('spiderfied', () => requestAnimationFrame(() => map.invalidateSize({ pan:false })));
            const escapeHtml = value => String(value).replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[character]));

            const markerIcon = tree => tree.record_type === 'survey'
                ? L.divIcon({ className:'survey-marker-shell', html:'<span class="survey-map-marker"></span>', iconSize:[14,14], iconAnchor:[7,7] })
                : L.divIcon({ className:'tree-marker-shell', html:`<span class="tree-map-marker" style="--marker-color:${conditionColors[tree.condition] || conditionColors.other}">♣</span>`, iconSize:[34,42], iconAnchor:[17,40], popupAnchor:[0,-36] });
            const selectTree = tree => {
                empty.hidden = true; details.hidden = false;
                details.querySelector('.detail-id').textContent = tree.code;
                details.querySelector('.detail-name').textContent = tree.name;
                details.querySelector('.detail-scientific').textContent = tree.scientific_name;
                details.querySelector('.detail-record-type').textContent = tree.record_type === 'survey' ? 'Provisional coordinate' : 'Verified inventory tree';
                details.querySelector('.detail-area').textContent = tree.area;
                details.querySelector('.detail-location').textContent = tree.location;
                details.querySelector('.detail-condition').textContent = tree.record_type === 'survey' ? 'Not yet recorded' : tree.condition.replace('_',' ').replace(/^./, letter => letter.toUpperCase());
                const heightRow = details.querySelector('.detail-height-row');
                heightRow.hidden = tree.height_m === null || tree.height_m === undefined;
                details.querySelector('.detail-height').textContent = tree.height_m === null || tree.height_m === undefined ? '' : `${tree.height_m} m`;
                const profile = details.querySelector('.detail-profile');
                profile.hidden = !tree.profile_url;
                profile.href = tree.profile_url || '#';
            };
            const renderMarkers = trees => {
                clusterLayer.clearLayers(); plainLayer.clearLayers(); markers = []; markerRecords = [];
                trees.forEach(tree => {
                    const marker = L.marker([tree.latitude, tree.longitude], { icon:markerIcon(tree), title:`${tree.code} ${tree.name}` })
                        .bindTooltip(`<b>${escapeHtml(tree.name)}</b><br>${escapeHtml(tree.code)} · ${escapeHtml(tree.location)}`, { direction:'top', offset:[0,-32] })
                        .on('click', () => selectTree(tree));
                    markerRecords.push({ marker, tree });
                });
                updateLayerMode();
            };
            const updateLayerMode = () => {
                map.removeLayer(clusterLayer); map.removeLayer(plainLayer);
                clusterLayer.clearLayers(); plainLayer.clearLayers();
                const showVerified = document.querySelector('#show-markers').checked;
                const showSurvey = document.querySelector('#show-survey').checked;
                const visibleRecords = markerRecords.filter(({ tree }) => tree.record_type === 'survey' ? showSurvey : showVerified);
                markers = visibleRecords.map(({ marker }) => marker);
                markers.forEach(marker => { clusterLayer.addLayer(marker); plainLayer.addLayer(marker); });
                if (!markers.length) return;
                map.addLayer(document.querySelector('#cluster-markers').checked ? clusterLayer : plainLayer);
            };
            const loadTrees = async () => {
                loading.hidden = false; error.hidden = true;
                const params = new URLSearchParams(new FormData(form));
                try {
                    const response = await fetch(`{{ route('campus-map.data') }}?${params}`, { headers:{ Accept:'application/json' } });
                    if (!response.ok) throw new Error('Request failed');
                    const data = await response.json(); lastTrees = [...data.survey_points, ...data.trees]; renderMarkers(lastTrees);
                    Object.entries(data.summary).forEach(([key,value]) => { const target=document.querySelector(`[data-summary="${key}"]`); if(target) target.textContent=Number(value).toLocaleString(); });
                    document.querySelector('#map-updated').textContent = data.updated_at ? `Updated ${new Date(data.updated_at).toLocaleDateString()}` : 'Live inventory data';
                    const hasActiveFilter = ['q','area','condition'].some(key => params.get(key));
                    if (markers.length && hasActiveFilter) map.fitBounds(L.featureGroup(markers).getBounds().pad(.2), { maxZoom:19 });
                } catch (exception) { error.hidden = false; }
                finally { loading.hidden = true; }
            };
            const updateSelectedCoordinates = latlng => {
                selectedCoordinates = {
                    latitude: Number(latlng.lat).toFixed(7),
                    longitude: Number(latlng.lng).toFixed(7),
                };
                pickedLatitude.textContent = selectedCoordinates.latitude;
                pickedLongitude.textContent = selectedCoordinates.longitude;
                copyCoordinates.disabled = false;
                coordinateCopyStatus.textContent = '';
            };
            const placeCoordinatePickerMarker = latlng => {
                if (!coordinatePickerMarker) {
                    const pickerIcon = L.divIcon({
                        className:'coordinate-picker-marker-shell',
                        html:'<span class="coordinate-picker-marker" aria-hidden="true">+</span>',
                        iconSize:[34,34],
                        iconAnchor:[17,17],
                    });
                    coordinatePickerMarker = L.marker(latlng, {
                        draggable:true,
                        icon:pickerIcon,
                        title:'Selected coordinates. Drag to fine-tune.',
                        zIndexOffset:1000,
                    }).addTo(map);
                    coordinatePickerMarker.on('dragend', event => updateSelectedCoordinates(event.target.getLatLng()));
                } else {
                    coordinatePickerMarker.setLatLng(latlng);
                }
                updateSelectedCoordinates(latlng);
            };
            const copyText = async value => {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(value);
                    return;
                }

                const temporaryInput = document.createElement('textarea');
                temporaryInput.value = value;
                temporaryInput.setAttribute('readonly', '');
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';
                document.body.appendChild(temporaryInput);
                temporaryInput.select();
                const copied = document.execCommand('copy');
                temporaryInput.remove();

                if (!copied) throw new Error('Copy command failed');
            };

            form.addEventListener('submit', event => { event.preventDefault(); loadTrees(); });
            form.addEventListener('reset', () => setTimeout(() => { map.setView(center,18); empty.hidden=false; details.hidden=true; loadTrees(); },0));
            document.querySelector('#show-markers').addEventListener('change', updateLayerMode);
            document.querySelector('#cluster-markers').addEventListener('change', updateLayerMode);
            document.querySelector('#show-survey').addEventListener('change', () => { updateLayerMode(); loadTrees(); });
            error.querySelector('button').addEventListener('click', loadTrees);
            coordinatePickerToggle.addEventListener('click', () => {
                coordinatePickerEnabled = !coordinatePickerEnabled;
                coordinatePickerPanel.hidden = false;
                coordinatePickerToggle.setAttribute('aria-expanded', 'true');
                coordinatePickerToggle.setAttribute('aria-pressed', String(coordinatePickerEnabled));
                coordinatePickerToggle.innerHTML = coordinatePickerEnabled
                    ? '<span aria-hidden="true">⌖</span> Tap a point on the map'
                    : '<span aria-hidden="true">⌖</span> Pick another point';
                mapContainer.classList.toggle('coordinate-picker-active', coordinatePickerEnabled);
                coordinateCopyStatus.textContent = coordinatePickerEnabled ? 'Coordinate picking is active.' : 'Coordinate picking is paused.';
            });
            map.on('click', event => {
                if (!coordinatePickerEnabled) return;

                placeCoordinatePickerMarker(event.latlng);
            });
            copyCoordinates.addEventListener('click', async () => {
                if (!selectedCoordinates) return;

                try {
                    await copyText(`${selectedCoordinates.latitude}, ${selectedCoordinates.longitude}`);
                    coordinateCopyStatus.textContent = 'Coordinates copied. Paste them into the Add Tree form.';
                } catch (exception) {
                    coordinateCopyStatus.textContent = 'Copy failed. Select the values above and copy them manually.';
                }
            });
            document.querySelector('.public-menu-toggle')?.addEventListener('click', event => { const nav=document.querySelector('#public-navigation'); const open=event.currentTarget.getAttribute('aria-expanded')==='true'; event.currentTarget.setAttribute('aria-expanded',String(!open)); nav.classList.toggle('open',!open); });
            new ResizeObserver(() => map.invalidateSize()).observe(document.querySelector('#nvsu-map'));
            requestAnimationFrame(() => map.invalidateSize());
            loadTrees();
        })();
    </script>
</body>
</html>
