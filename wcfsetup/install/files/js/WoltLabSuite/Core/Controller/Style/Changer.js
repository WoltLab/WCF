/**
 * Dialog based style changer.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Language", "../../Ui/Dialog"], function (require, exports, tslib_1, Ajax, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.showDialog = showDialog;
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class ControllerStyleChanger {
        /**
         * Adds the style changer to the bottom navigation.
         */
        constructor() {
            document.querySelectorAll(".jsButtonStyleChanger").forEach((link) => {
                link.addEventListener("click", (ev) => this.showDialog(ev));
            });
        }
        /**
         * Loads and displays the style change dialog.
         */
        showDialog(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        _dialogSetup() {
            return {
                id: "styleChanger",
                options: {
                    disableContentPadding: true,
                    title: Language.get("wcf.style.changeStyle"),
                },
                source: {
                    data: {
                        actionName: "getStyleChooser",
                        className: "wcf\\data\\style\\StyleAction",
                    },
                    after: (content) => {
                        content.querySelectorAll(".styleList > li").forEach((style) => {
                            style.classList.add("pointer");
                            style.addEventListener("click", (ev) => this.click(ev));
                        });
                    },
                },
            };
        }
        /**
         * Changes the style and reloads current page.
         */
        click(event) {
            event.preventDefault();
            const listElement = event.currentTarget;
            Ajax.apiOnce({
                data: {
                    actionName: "changeStyle",
                    className: "wcf\\data\\style\\StyleAction",
                    objectIDs: [listElement.dataset.styleId],
                },
                success: function () {
                    window.location.reload();
                },
            });
        }
    }
    let controllerStyleChanger;
    /**
     * Adds the style changer to the bottom navigation.
     */
    function setup() {
        if (!controllerStyleChanger) {
            new ControllerStyleChanger();
        }
    }
    /**
     * Loads and displays the style change dialog.
     */
    function showDialog(event) {
        controllerStyleChanger.showDialog(event);
    }
});
