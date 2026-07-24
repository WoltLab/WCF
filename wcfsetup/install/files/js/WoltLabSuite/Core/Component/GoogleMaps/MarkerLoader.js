/**
 * Handles a large map with many markers where (new) markers are loaded via AJAX.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../Ajax", "../Dialog", "../../Dom/Util", "@googlemaps/markerclusterer", "../../Api/GoogleMaps/GetMapMarkers", "./woltlab-core-google-maps"], function (require, exports, tslib_1, Ajax_1, Dialog_1, Util_1, markerclusterer_1, GetMapMarkers_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.setupWithEndpoint = setupWithEndpoint;
    Util_1 = tslib_1.__importDefault(Util_1);
    class MarkerLoader {
        #map;
        #fetchMarkers;
        #clusterer;
        #previousNorthEast;
        #previousSouthWest;
        #objectIDs = [];
        constructor(map, fetchMarkers) {
            this.#map = map;
            this.#fetchMarkers = fetchMarkers;
            this.#clusterer = new markerclusterer_1.MarkerClusterer({
                map,
            });
            void this.#initLoadMarkers();
        }
        async #initLoadMarkers() {
            if (this.#map.getBounds()) {
                // The map has already been loaded and the 'idle'
                // event listener is therefore not called initially.
                await this.#loadMarkers();
            }
            this.#map.addListener("idle", () => {
                void this.#loadMarkers();
            });
        }
        async #loadMarkers() {
            const northEast = this.#map.getBounds().getNorthEast();
            const southWest = this.#map.getBounds().getSouthWest();
            if (!this.#checkPreviousLocation(northEast, southWest)) {
                return;
            }
            const response = await this.#fetchMarkers(northEast, southWest, this.#objectIDs);
            response.markers.forEach((data) => {
                this.#addMarker(data);
            });
            this.#clusterer.render();
        }
        #addMarker(data) {
            const marker = new google.maps.marker.AdvancedMarkerElement({
                map: this.#map,
                position: new google.maps.LatLng(data.latitude, data.longitude),
                title: data.title,
            });
            this.#clusterer.addMarker(marker, true);
            if (data.infoWindow) {
                const content = document.createElement("div");
                content.classList.add("googleMapsInfoWindow");
                Util_1.default.setInnerHtml(content, data.infoWindow);
                const infoWindow = new google.maps.InfoWindow({
                    headerContent: data.title,
                    content,
                });
                marker.addListener("click", () => {
                    infoWindow.open(this.#map, marker);
                });
                if (data.dialog) {
                    let dialog;
                    infoWindow.addListener("domready", () => {
                        const button = content.querySelector(".jsButtonShowDialog");
                        button?.addEventListener("click", () => {
                            if (!dialog) {
                                dialog = (0, Dialog_1.dialogFactory)().fromHtml(data.dialog).withoutControls();
                            }
                            dialog.show(button.dataset.title || button.textContent);
                        });
                    });
                }
            }
            if (data.objectID) {
                this.#objectIDs.push(data.objectID);
            }
            if (data.objectIDs) {
                this.#objectIDs.push(...data.objectIDs);
            }
        }
        /**
         * Checks if the user has zoomed in, then all markers are already displayed.
         */
        #checkPreviousLocation(northEast, southWest) {
            if (this.#previousNorthEast &&
                this.#previousNorthEast.lat() >= northEast.lat() &&
                this.#previousNorthEast.lng() >= northEast.lng() &&
                this.#previousSouthWest.lat() <= southWest.lat() &&
                this.#previousSouthWest.lng() <= southWest.lng()) {
                return false;
            }
            this.#previousNorthEast = northEast;
            this.#previousSouthWest = southWest;
            return true;
        }
    }
    /**
     * Loads the markers using the legacy `getMapMarkers` DBO action of the given class.
     *
     * @deprecated 6.3 use `setupWithEndpoint()` with a dedicated RPC endpoint instead
     */
    async function setup(googleMaps, actionClassName, additionalParameters) {
        const map = await googleMaps.getMap();
        new MarkerLoader(map, (northEast, southWest, excludedObjectIDs) => {
            return (0, Ajax_1.dboAction)("getMapMarkers", actionClassName)
                .payload({
                ...additionalParameters,
                excludedObjectIDs: JSON.stringify(excludedObjectIDs),
                eastLongitude: northEast.lng(),
                northLatitude: northEast.lat(),
                southLatitude: southWest.lat(),
                westLongitude: southWest.lng(),
            })
                .dispatch();
        });
    }
    /**
     * Loads the markers using an RPC endpoint, e.g. `calendar/events/map-markers`.
     *
     * @since 6.3
     */
    async function setupWithEndpoint(googleMaps, endpoint, additionalParameters = {}) {
        const map = await googleMaps.getMap();
        new MarkerLoader(map, (northEast, southWest, excludedObjectIDs) => {
            return (0, GetMapMarkers_1.getMapMarkers)(endpoint, {
                northLatitude: northEast.lat(),
                southLatitude: southWest.lat(),
                eastLongitude: northEast.lng(),
                westLongitude: southWest.lng(),
            }, excludedObjectIDs, additionalParameters);
        });
    }
});
