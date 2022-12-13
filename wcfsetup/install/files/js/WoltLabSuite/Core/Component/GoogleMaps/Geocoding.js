define(["require", "exports", "../../Helper/Selector", "./Geocoding/Suggestion", "./Marker", "./woltlab-core-google-maps"], function (require, exports, Selector_1, Suggestion_1, Marker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-google-maps-geocoding]", async (element) => {
            const map = document.getElementById(element.dataset.googleMapsGeocoding);
            const marker = await (0, Marker_1.addDraggableMarker)(map);
            const geocoder = await map.getGeocoder();
            marker.addListener("dragend", () => {
                void geocoder.geocode({ location: marker.getPosition() }, (results, status) => {
                    if (status === google.maps.GeocoderStatus.OK) {
                        element.value = results[0].formatted_address;
                    }
                });
            });
            (0, Suggestion_1.setup)(element, geocoder);
        });
    }
    exports.setup = setup;
});
