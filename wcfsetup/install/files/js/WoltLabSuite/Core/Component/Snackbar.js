/**
 * Shows snackbar like notifications.
 *
 * @author    Marcwl Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Helper/PageOverlay"], function (require, exports, Language_1, PageOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showSuccessSnackbar = showSuccessSnackbar;
    exports.showProgressSnackbar = showProgressSnackbar;
    exports.showDefaultSuccessSnackbar = showDefaultSuccessSnackbar;
    var SnackbarType;
    (function (SnackbarType) {
        SnackbarType[SnackbarType["Success"] = 0] = "Success";
        SnackbarType[SnackbarType["Progress"] = 1] = "Progress";
    })(SnackbarType || (SnackbarType = {}));
    // eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
    class Snackbar extends EventTarget {
        #message = "";
        #type;
        #snackbarElement;
        constructor(message, type) {
            super();
            this.#message = message;
            this.#type = type;
            this.#render();
        }
        get message() {
            return this.#message;
        }
        set message(message) {
            this.#message = message;
            this.#snackbarElement.querySelector(".snackbar__message").textContent = message;
        }
        markAsDone() {
            this.#type = SnackbarType.Success;
            this.#updateVisualType();
            const icon = this.#snackbarElement.querySelector(".snackbar__icon fa-icon");
            icon.setIcon("check");
            this.#setHideTimeout();
        }
        #render() {
            const iconWrapper = document.createElement("div");
            iconWrapper.classList.add("snackbar__icon");
            const icon = document.createElement("fa-icon");
            icon.size = 24;
            icon.setIcon(this.isProgress() ? "spinner" : "check");
            iconWrapper.append(icon);
            const message = document.createElement("div");
            message.classList.add("snackbar__message");
            if (this.isProgress()) {
                message.setAttribute("aria-live", "polite");
            }
            message.append(this.message);
            this.#snackbarElement = document.createElement("div");
            this.#snackbarElement.classList.add("snackbar");
            this.#snackbarElement.setAttribute("role", "status");
            this.#updateVisualType();
            this.#snackbarElement.addEventListener("click", () => {
                if (this.isProgress()) {
                    return;
                }
                this.close();
            });
            this.#snackbarElement.append(iconWrapper, message);
            getSnackbarContainer().addSnackbar(this);
            if (!this.isProgress()) {
                this.#setHideTimeout();
            }
        }
        #updateVisualType() {
            if (this.isProgress()) {
                this.#snackbarElement.classList.add("snackbar--progress");
                this.#snackbarElement.classList.remove("snackbar--success");
            }
            else {
                this.#snackbarElement.classList.remove("snackbar--progress");
                this.#snackbarElement.classList.add("snackbar--success");
            }
        }
        #setHideTimeout() {
            window.setTimeout(() => {
                this.close();
            }, 3_000);
        }
        isProgress() {
            return this.#type == SnackbarType.Progress;
        }
        isVisible() {
            if (this.#snackbarElement.parentElement === null) {
                return false;
            }
            if (this.#snackbarElement.classList.contains("snackbar--closing")) {
                return false;
            }
            return true;
        }
        close() {
            if (!this.isVisible()) {
                return;
            }
            this.dispatchEvent(new CustomEvent("snackbar:close"));
            // The animation to move the element vertically relative to its height
            // requires the value to be computed first.
            const height = Math.trunc(getSnackbarContainer().getGapValue() + this.#snackbarElement.getBoundingClientRect().height);
            this.#snackbarElement.style.setProperty("--height", `${height}px`);
            this.#snackbarElement.classList.add("snackbar--closing");
            this.#snackbarElement.addEventListener("animationend", () => {
                this.#snackbarElement.remove();
            });
        }
        get element() {
            return this.#snackbarElement;
        }
    }
    let snackbarContainer;
    function getSnackbarContainer() {
        if (!snackbarContainer) {
            snackbarContainer = new SnackbarContainer();
        }
        return snackbarContainer;
    }
    class SnackbarContainer {
        #element;
        #snackbars = [];
        constructor() {
            this.#element = document.createElement("div");
            this.#element.classList.add("snackbarContainer");
            (0, PageOverlay_1.getPageOverlayContainer)().append(this.#element);
        }
        addSnackbar(snackbar) {
            const existingStaticSnackbars = this.#snackbars.filter((snackbar) => !snackbar.isProgress());
            if (existingStaticSnackbars.length > 2) {
                const oldSnackbar = existingStaticSnackbars.shift();
                oldSnackbar.close();
            }
            this.#snackbars.push(snackbar);
            this.#element.prepend(snackbar.element);
            void snackbar.addEventListener("snackbar:close", () => {
                const i = this.#snackbars.indexOf(snackbar);
                if (i !== -1) {
                    this.#snackbars = this.#snackbars.splice(i, 1);
                }
            });
        }
        getGapValue() {
            const gap = window.getComputedStyle(this.#element).gap;
            const match = gap.match(/^(\d+)px$/);
            if (match === null) {
                return 0;
            }
            return parseInt(match[1]);
        }
    }
    class SnackbarProgress {
        #snackbar;
        #label;
        #length;
        #iteration = 0;
        constructor(label, length) {
            this.#label = label;
            this.#length = length;
            this.#snackbar = new Snackbar(this.#getMessage(), SnackbarType.Progress);
        }
        setIteration(iteration) {
            this.#iteration = iteration;
        }
        markAsDone() {
            this.#snackbar.markAsDone();
        }
        get element() {
            return this.#snackbar;
        }
        #getMessage() {
            return (0, Language_1.getPhrase)("wcf.global.snackbar.progress", {
                label: this.#label,
                iteration: this.#iteration,
                length: this.#length,
            });
        }
    }
    function showSuccessSnackbar(message) {
        return new Snackbar(message, SnackbarType.Success);
    }
    function showProgressSnackbar(label, length) {
        return new SnackbarProgress(label, length);
    }
    function showDefaultSuccessSnackbar() {
        return showSuccessSnackbar((0, Language_1.getPhrase)("wcf.global.success"));
    }
});
