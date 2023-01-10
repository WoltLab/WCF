/**
 * Provides geocoding functions for searching map locations.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Component/GoogleMaps/Geocoding
 */
define(["require", "exports", "../../Helper/Selector", "./Geocoding/Suggestion", "./Marker", "./woltlab-core-google-maps"], function (require, exports, Selector_1, Suggestion_1, Marker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    class Geocoding {
        #element;
        #geocoder;
        #map;
        #marker;
        constructor(element, map, geocoder) {
            this.#element = element;
            this.#map = map;
            this.#geocoder = geocoder;
            if (this.#element.hasAttribute("data-google-maps-marker")) {
                void this.#setupMarker();
            }
            if (element.value) {
                this.#moveMarker(element.value);
            }
            (0, Suggestion_1.setup)(this.#element, this.#geocoder, (item) => {
                this.#moveMarker(item.dataset.label);
                return true;
            });
        }
        async #setupMarker() {
            this.#marker = await (0, Marker_1.addDraggableMarker)(this.#map);
            this.#marker.addListener("dragend", () => {
                void this.#geocoder.geocode({ location: this.#marker.getPosition() }, (results, status) => {
                    if (status === google.maps.GeocoderStatus.OK) {
                        this.#element.value = results[0].formatted_address;
                        this.#setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                    }
                });
            });
        }
        #moveMarker(address) {
            void this.#geocoder.geocode({ address }, async (results, status) => {
                if (status === google.maps.GeocoderStatus.OK) {
                    this.#marker?.setPosition(results[0].geometry.location);
                    (await this.#map.getMap()).setCenter(results[0].geometry.location);
                    this.#setLocation(results[0].geometry.location.lat(), results[0].geometry.location.lng());
                }
            });
        }
        #setLocation(lat, lng) {
            this.#element.dataset.googleMapsLat = lat.toString();
            this.#element.dataset.googleMapsLng = lng.toString();
            if (this.#element.hasAttribute("data-google-maps-geocoding-store") && this.#element.form) {
                this.#store("latitude", lat);
                this.#store("longitude", lng);
            }
        }
        #store(name, value) {
            let input = this.#element.form.querySelector(`input[name="${name}"]`);
            if (!input) {
                input = document.createElement("input");
                input.type = "hidden";
                input.name = name;
                this.#element.form.append(input);
            }
            input.value = value.toString();
        }
    }
    function setup() {
        (0, Selector_1.wheneverFirstSeen)("[data-google-maps-geocoding]", async (element) => {
            const map = document.getElementById(element.dataset.googleMapsGeocoding);
            const geocoder = await map.getGeocoder();
            new Geocoding(element, map, geocoder);
        });
    }
    exports.setup = setup;
});
