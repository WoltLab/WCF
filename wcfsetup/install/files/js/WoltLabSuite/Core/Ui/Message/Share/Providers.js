/**
 * Manages the share providers shown in the share dialogs.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Message/Share/Providers
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getShareProviders = exports.addShareProviders = exports.addShareProvider = void 0;
    const providers = new Set();
    function addShareProvider(shareProvider) {
        providers.add(shareProvider);
    }
    exports.addShareProvider = addShareProvider;
    function addShareProviders(shareProviders) {
        shareProviders.forEach((shareProvider) => addShareProvider(shareProvider));
    }
    exports.addShareProviders = addShareProviders;
    function getShareProviders() {
        return providers;
    }
    exports.getShareProviders = getShareProviders;
});
