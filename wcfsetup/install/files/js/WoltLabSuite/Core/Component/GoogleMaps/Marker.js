define(["require", "exports", "@googlemaps/markerclusterer", "./woltlab-core-google-maps"], function (require, exports, markerclusterer_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addDraggableMarker = exports.addMarker = void 0;
    const markerClusterers = new WeakMap();
    async function addMarker(googleMaps, latitude, longitude, title, focus) {
        const map = await googleMaps.getMap();
        const marker = new google.maps.Marker({
            map,
            position: new google.maps.LatLng(latitude, longitude),
            title,
        });
        if (focus) {
            map.setCenter(marker.getPosition());
        }
        let clusterer = markerClusterers.get(googleMaps);
        if (clusterer === undefined) {
            clusterer = new markerclusterer_1.MarkerClusterer({
                map,
            });
            markerClusterers.set(googleMaps, clusterer);
        }
        clusterer.addMarker(marker);
    }
    exports.addMarker = addMarker;
    async function addDraggableMarker(googleMaps, latitude, longitude) {
        const map = await googleMaps.getMap();
        if (latitude === undefined) {
            latitude = googleMaps.lat;
        }
        if (longitude === undefined) {
            longitude = googleMaps.lng;
        }
        const marker = new google.maps.Marker({
            map,
            position: new google.maps.LatLng(latitude, longitude),
            draggable: true,
            clickable: false,
        });
        map.setCenter(marker.getPosition());
        return marker;
    }
    exports.addDraggableMarker = addDraggableMarker;
});
