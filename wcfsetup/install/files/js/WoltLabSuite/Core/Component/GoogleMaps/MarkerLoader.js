define(["require", "exports", "../../Ajax", "./woltlab-core-google-maps"], function (require, exports, Ajax_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    class MarkerLoader {
        #map;
        #actionClassName;
        #additionalParameters;
        #previousNorthEast;
        #previousSouthWest;
        #objectIDs = [];
        constructor(map, actionClassName, additionalParameters) {
            this.#map = map;
            this.#actionClassName = actionClassName;
            this.#additionalParameters = additionalParameters;
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
            if (data.infoWindow) {
                const infoWindow = new google.maps.InfoWindow({
                    content: data.infoWindow,
                });
                marker.addListener("click", () => {
                    infoWindow.open(this.#map, marker);
                });
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
    exports.setup = setup;
});
