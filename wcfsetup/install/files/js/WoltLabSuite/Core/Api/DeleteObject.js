/**
 * Deletes an object.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend", "./Result"], function (require, exports, Backend_1, Result_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.deleteObject = deleteObject;
    async function deleteObject(endpoint) {
        try {
            await (0, Backend_1.prepareRequest)(endpoint).delete().fetchAsJson();
        }
        catch (e) {
            return (0, Result_1.apiResultFromError)(e);
        }
        return (0, Result_1.apiResultFromValue)([]);
    }
});
