/**
 * Provides the program logic for the import mapping reset.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Core", "../../../Ui/Confirmation"], function (require, exports, tslib_1, Ajax, Core, UiConfirmation) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    function setup() {
        const link = document.getElementById("deleteMapping");
        link.addEventListener("click", (event) => {
            event.preventDefault();
            UiConfirmation.show({
                confirm() {
                    Ajax.apiOnce({
                        data: {
                            actionName: "resetMapping",
                            className: "wcf\\system\\importer\\ImportHandler",
                        },
                        success() {
                            window.location.reload();
                        },
                        url: "index.php?ajax-invoke&t=" + Core.getXsrfToken(),
                    });
                },
                message: link.dataset.confirmMessage,
            });
        });
    }
});
