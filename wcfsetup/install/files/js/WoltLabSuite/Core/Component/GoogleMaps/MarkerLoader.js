/**
 * Handles a large map with many markers where (new) markers are loaded via AJAX.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../Ajax", "../Dialog", "../../Dom/Util", "@googlemaps/markerclusterer", "./woltlab-core-google-maps"], function (require, exports, tslib_1, Ajax_1, Dialog_1, Util_1, markerclusterer_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    class MarkerLoader {
        #map;
        #actionClassName;
        #additionalParameters;
        #clusterer;
        #previousNorthEast;
        #previousSouthWest;
        #objectIDs = [];
        constructor(map, actionClassName, additionalParameters) {
            this.#map = map;
            this.#actionClassName = actionClassName;
            this.#additionalParameters = additionalParameters;
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
            const response = (await (0, Ajax_1.dboAction)("getMapMarkers", this.#actionClassName)
                .payload({
                ...this.#additionalParameters,
                excludedObjectIDs: JSON.stringify(this.#objectIDs),
                eastLongitude: northEast.lng(),
                northLatitude: northEast.lat(),
                southLatitude: southWest.lat(),
                westLongitude: southWest.lng(),
            })
                .dispatch());
            response.markers.forEach((data) => {
                this.#addMarker(data);
            });
        }
        #addMarker(data) {
            const marker = new google.maps.Marker({
                map: this.#map,
                position: new google.maps.LatLng(data.latitude, data.longitude),
                title: data.title,
            });
            this.#clusterer.addMarker(marker);
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
    async function setup(googleMaps, actionClassName, additionalParameters) {
        const map = await googleMaps.getMap();
        new MarkerLoader(map, actionClassName, additionalParameters);
    }
});
