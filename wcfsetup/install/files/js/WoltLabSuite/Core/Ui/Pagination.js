define(["require", "exports"], function (require, exports) {
    "use strict";
    /**
     * Callback-based pagination.
     *
     * @author Alexander Ebert
     * @copyright 2001-2022 WoltLab GmbH
     * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @module WoltLabSuite/Core/Ui/Pagination
     * @deprecated 6.0 Use `<woltlab-core-pagination>` directly.
     */
    class UiPagination {
        #callbackSwitch;
        #callbackShouldSwitch;
        #pagination;
        constructor(element, options) {
            if (typeof options.callbackSwitch === "function") {
                this.#callbackSwitch = options.callbackSwitch;
            }
            if (typeof options.callbackShouldSwitch === "function") {
                this.#callbackShouldSwitch = options.callbackShouldSwitch;
            }
            this.#pagination = document.createElement("woltlab-core-pagination");
            this.#pagination.count = options.maxPage;
            this.#pagination.page = options.activePage;
            element.append(this.#pagination);
            this.#setupEventForwarding();
        }
        #setupEventForwarding() {
            this.#pagination.addEventListener("switchPage", (event) => {
                if (this.#callbackShouldSwitch !== undefined) {
                    if (this.#callbackShouldSwitch(event.detail) === false) {
                        event.preventDefault();
                        return;
                    }
                }
                if (this.#callbackSwitch) {
                    this.#callbackSwitch(event.detail);
                }
            });
        }
        getActivePage() {
            return this.#pagination.page;
        }
        getElement() {
            return this.#pagination.parentElement;
        }
        getMaxPage() {
            return this.#pagination.count;
        }
        /**
         * Switches to given page number.
         */
        switchPage(pageNo, event) {
            if (event instanceof MouseEvent) {
                event.preventDefault();
                const target = event.currentTarget;
                // force tooltip to vanish and strip positioning
                if (target && target.dataset.tooltip) {
                    const tooltip = document.getElementById("balloonTooltip");
                    if (tooltip) {
                        const event = new Event("mouseleave", {
                            bubbles: true,
                            cancelable: true,
                        });
                        target.dispatchEvent(event);
                        tooltip.style.removeProperty("top");
                        tooltip.style.removeProperty("bottom");
                    }
                }
            }
            pageNo = ~~pageNo;
            if (pageNo > 0 && this.#pagination.page !== pageNo && pageNo <= this.#pagination.count) {
                if (this.#callbackShouldSwitch !== undefined) {
                    if (!this.#callbackShouldSwitch(pageNo)) {
                        return;
                    }
                }
                this.#pagination.jumpToPage(pageNo);
                if (this.#callbackSwitch !== undefined) {
                    this.#callbackSwitch(pageNo);
                }
            }
        }
    }
    return UiPagination;
});
