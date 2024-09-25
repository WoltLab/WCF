/**
 * Manages the share providers shown in the share dialogs.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.addShareProvider = addShareProvider;
    exports.addShareProviders = addShareProviders;
    exports.getShareProviders = getShareProviders;
    const providers = new Set();
    function addShareProvider(shareProvider) {
        providers.add(shareProvider);
    }
    function addShareProviders(shareProviders) {
        shareProviders.forEach((shareProvider) => addShareProvider(shareProvider));
    }
    function getShareProviders() {
        return providers;
    }
});
