/**
 * Fetches the map markers located within the given boundaries from an RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "../Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getMapMarkers = getMapMarkers;
    async function getMapMarkers(endpoint, boundaries, excludedObjectIDs, additionalParameters = {}) {
        const url = new URL(`${window.WSC_RPC_API_URL}${endpoint}`);
        return (0, Result_1.fromInfallibleApiRequest)(() => {
            return (0, Backend_1.prepareRequest)(url)
                .post({
                ...additionalParameters,
                northLatitude: boundaries.northLatitude,
                southLatitude: boundaries.southLatitude,
                eastLongitude: boundaries.eastLongitude,
                westLongitude: boundaries.westLongitude,
                excludedObjectIDs,
            })
                .disableLoadingIndicator()
                .fetchAsJson();
        });
    }
});
