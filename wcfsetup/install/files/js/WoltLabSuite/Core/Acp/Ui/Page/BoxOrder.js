/**
 * Provides helper functions to sort boxes per page.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Dom/Change/Listener", "../../../Language", "../../../Ui/Confirmation", "../../../Ui/Notification"], function (require, exports, tslib_1, Ajax, Listener_1, Language, UiConfirmation, UiNotification) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    UiNotification = tslib_1.__importStar(UiNotification);
    class AcpUiPageBoxOrder {
        pageId;
        pbo;
        /**
         * Initializes the sorting capabilities.
         */
        constructor(pageId, boxes) {
            this.pageId = pageId;
            this.pbo = document.getElementById("pbo");
            boxes.forEach((boxData, position) => {
                const container = document.createElement("ul");
                boxData.forEach((box) => {
                    const item = document.createElement("li");
                    item.dataset.boxId = box.boxId.toString();
                    let icon = "";
                    if (box.isDisabled) {
                        icon = ` <span class="jsTooltip" title="${Language.get("wcf.acp.box.isDisabled")}">
            <fa-icon name="triangle-exclamation" solid></fa-icon>
          </span>`;
                    }
                    item.innerHTML = box.name + icon;
                    const sortableIndicator = document.createElement("fa-icon");
                    sortableIndicator.setIcon("up-down");
                    item.prepend(sortableIndicator);
                    container.appendChild(item);
                });
                if (boxData.length > 1) {
                    window.jQuery(container).sortable({
                        opacity: 0.6,
                        placeholder: "sortablePlaceholder",
                    });
                }
                const wrapper = this.pbo.querySelector(`[data-placeholder="${position}"]`);
                wrapper.appendChild(container);
            });
            const submitButton = document.querySelector('button[data-type="submit"]');
            submitButton.addEventListener("click", (ev) => this.save(ev));
            const buttonDiscard = document.querySelector(".jsButtonCustomShowOrder");
            if (buttonDiscard)
                buttonDiscard.addEventListener("click", (ev) => this.discard(ev));
            Listener_1.default.trigger();
        }
        /**
         * Saves the order of all boxes per position.
         */
        save(event) {
            event.preventDefault();
            const data = {};
            // collect data
            this.pbo.querySelectorAll("[data-placeholder]").forEach((position) => {
                const boxIds = Array.from(position.querySelectorAll("li"))
                    .map((element) => ~~element.dataset.boxId)
                    .filter((id) => id > 0);
                const placeholder = position.dataset.placeholder;
                data[placeholder] = boxIds;
            });
            Ajax.api(this, {
                parameters: {
                    position: data,
                },
            });
        }
        /**
         * Shows an dialog to discard the individual box show order for this page.
         */
        discard(event) {
            event.preventDefault();
            UiConfirmation.show({
                confirm: () => {
                    Ajax.api(this, {
                        actionName: "resetPosition",
                    });
                },
                message: Language.get("wcf.acp.page.boxOrder.discard.confirmMessage"),
            });
        }
        _ajaxSuccess(data) {
            switch (data.actionName) {
                case "updatePosition":
                    UiNotification.show();
                    break;
                case "resetPosition":
                    UiNotification.show(undefined, () => {
                        window.location.reload();
                    });
                    break;
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "updatePosition",
                    className: "wcf\\data\\page\\PageAction",
                    interfaceName: "wcf\\data\\ISortableAction",
                    objectIDs: [this.pageId],
                },
            };
        }
    }
    let acpUiPageBoxOrder;
    /**
     * Initializes the sorting capabilities.
     */
    function init(pageId, boxes) {
        if (!acpUiPageBoxOrder) {
            acpUiPageBoxOrder = new AcpUiPageBoxOrder(pageId, boxes);
        }
    }
});
