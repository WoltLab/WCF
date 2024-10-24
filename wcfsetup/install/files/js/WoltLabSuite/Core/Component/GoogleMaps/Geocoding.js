/**
 * Provides geocoding functions for searching map locations.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "../../Helper/Selector", "./Geocoding/Suggestion", "./Marker", "./woltlab-core-google-maps"], function (require, exports, Selector_1, Suggestion_1, Marker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    class Geocoding {
        #element;
        #map;
        #marker;
        #initialMarkerPosition;
        constructor(element, map) {
            this.#element = element;
            this.#map = map;
            this.#initEvents();
            void this.#map.getGeocoder().then((geocoder) => {
                if (this.#element.hasAttribute("data-google-maps-marker")) {
                    void this.#setupMarker();
                }
                if (element.value) {
                    void this.#moveMarkerToAddress(element.value);
                }
                (0, Suggestion_1.setup)(this.#element, geocoder, (item) => {
                    void this.#moveMarkerToAddress(item.dataset.label);
                    return true;
                });
            });
        }
        #initEvents() {
            this.#element.addEventListener("geocoding:move-marker", (event) => {
                void this.#moveMarkerToLocation(event.detail.latitude, event.detail.longitude);
            });
            this.#element.addEventListener("geocoding:resolve", (event) => {
                void this.#map.getGeocoder().then((geocoder) => {
                    const location = new google.maps.LatLng(event.detail.latitude, event.detail.longitude);
                    void geocoder.geocode({ location }, (results, status) => {
                        if (status === google.maps.GeocoderStatus.OK) {
                            event.detail.callback(results[0].formatted_address);
                        }
                    });
                });
            });
            this.#element.addEventListener("geocoding:reset-marker", () => {
                if (this.#initialMarkerPosition) {
                    void this.#moveMarkerToLocation(this.#initialMarkerPosition.lat(), this.#initialMarkerPosition.lng());
                }
            });
        }
        async #setupMarker() {
            this.#marker = await (0, Marker_1.addDraggableMarker)(this.#map);
            this.#initialMarkerPosition = this.#marker?.position;
            this.#marker.addListener("dragend", () => {
                void this.#map.getGeocoder().then((geocoder) => {
                    void geocoder.geocode({ location: this.#marker.position }, (results, status) => {
                        if (status === google.maps.GeocoderStatus.OK) {
                            this.#element.value = results[0].formatted_address;
                            this.#setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                        }
                    });
                });
            });
        }
        async #moveMarkerToLocation(latitude, longitude) {
            const location = new google.maps.LatLng(latitude, longitude);
            if (this.#marker) {
                this.#marker.position = location;
            }
            (await this.#map.getMap()).setCenter(location);
            this.#setLocation(latitude, longitude);
        }
        async #moveMarkerToAddress(address) {
            const geocoder = await this.#map.getGeocoder();
            void geocoder.geocode({ address }, async (results, status) => {
                if (status === google.maps.GeocoderStatus.OK) {
                    if (this.#marker) {
                        this.#marker.position = results[0].geometry.location;
                    }
                    (await this.#map.getMap()).setCenter(results[0].geometry.location);
                    this.#setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                }
            });
        }
        #setLocation(lat, lng) {
            this.#element.dataset.googleMapsLat = lat.toString();
            this.#element.dataset.googleMapsLng = lng.toString();
            const prefix = this.#element.dataset.googleMapsGeocodingStore;
            if (prefix != null && this.#element.form) {
                this.#store(prefix, lat, lng);
            }
        }
        #store(prefix, lat, lng) {
            const name = prefix + "coordinates";
            let input = this.#element.form.querySelector(`input[name="${name}"]`);
            if (!input) {
                input = document.createElement("input");
                input.type = "hidden";
                input.name = name;
                this.#element.form.append(input);
            }
            input.value = `${lat},${lng}`;
        }
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-google-maps-geocoding]", (element) => {
            const map = document.getElementById(element.dataset.googleMapsGeocoding);
            new Geocoding(element, map);
        });
    }
});
