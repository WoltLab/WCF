/**
 * Provides functions to add markers to a map.
 *
 * @author  Marcel Werk
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "./woltlab-core-google-maps"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addMarker = addMarker;
    exports.addDraggableMarker = addDraggableMarker;
    async function addMarker(element, latitude, longitude, title, focus) {
        const map = await element.getMap();
        const marker = new google.maps.marker.AdvancedMarkerElement({
            map,
            position: new google.maps.LatLng(latitude, longitude),
            title,
        });
        if (focus) {
            map.setCenter(marker.position);
        }
    }
    async function addDraggableMarker(element, latitude, longitude) {
        const map = await element.getMap();
        if (latitude === undefined) {
            latitude = element.lat;
        }
        if (longitude === undefined) {
            longitude = element.lng;
        }
        const marker = new google.maps.marker.AdvancedMarkerElement({
            map,
            position: new google.maps.LatLng(latitude, longitude),
            gmpDraggable: true,
            gmpClickable: false,
        });
        map.setCenter(marker.position);
        return marker;
    }
});
