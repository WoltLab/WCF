/**
 * Offers to install packages from the list of licensed products.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/PromiseMutex", "../Ui/Package/PrepareInstallation"], function (require, exports, tslib_1, PromiseMutex_1, PrepareInstallation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    PrepareInstallation_1 = tslib_1.__importDefault(PrepareInstallation_1);
    function installPackage(button) {
        const installation = new PrepareInstallation_1.default();
        return installation.start(button.dataset.package, button.dataset.packageVersion, "license");
    }
    function setup() {
        const callback = (0, PromiseMutex_1.promiseMutex)((button) => installPackage(button));
        document.querySelectorAll(".jsInstallPackage").forEach((button) => {
            button.addEventListener("click", () => {
                callback(button);
            });
        });
    }
});
