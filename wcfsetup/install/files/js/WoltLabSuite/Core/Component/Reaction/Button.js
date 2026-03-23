/**
 * Handles the reactions buttons.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Api/Reactions/RevertReaction", "WoltLabSuite/Core/Api/Reactions/SetReaction", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Dom/Change/Listener", "focus-trap", "WoltLabSuite/Core/Ui/Alignment", "WoltLabSuite/Core/Ui/Screen", "WoltLabSuite/Core/Ui/CloseOverlay", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, RevertReaction_1, SetReaction_1, Selector_1, Listener_1, focus_trap_1, UiAlignment, UiScreen, CloseOverlay_1, PromiseMutex_1, Language_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    UiAlignment = tslib_1.__importStar(UiAlignment);
    UiScreen = tslib_1.__importStar(UiScreen);
    CloseOverlay_1 = tslib_1.__importDefault(CloseOverlay_1);
    const availableReactions = Object.values(window.REACTION_TYPES).sort((a, b) => a.showOrder - b.showOrder);
    class ReactionPopover {
        #resolve;
        #popover;
        #focusTrap;
        open(button) {
            if (this.#resolve !== undefined) {
                this.#resolve({ ok: false });
            }
            this.#showPopover(button);
            return new Promise((resolve) => {
                this.#resolve = resolve;
            });
        }
        cancel() {
            if (this.#resolve !== undefined) {
                this.#resolve({ ok: false });
            }
            this.#resolve = undefined;
            this.#hidePopover();
        }
        #click(reactionTypeId) {
            if (this.#resolve !== undefined) {
                this.#resolve({ ok: true, reactionTypeId });
            }
            this.#resolve = undefined;
            this.#hidePopover();
        }
        #createPopover() {
            if (this.#popover !== undefined) {
                return;
            }
            this.#popover = document.createElement("div");
            this.#popover.id = (0, Util_1.getUniqueId)();
            this.#popover.className = "reactionPopover forceHide";
            this.#popover.setAttribute("role", "listbox");
            this.#popover.setAttribute("aria-orientation", "horizontal");
            this.#popover.setAttribute("aria-label", (0, Language_1.getPhrase)("wcf.reactions.react"));
            const popoverContent = document.createElement("div");
            popoverContent.className = "reactionPopoverContent";
            const popoverButtonList = document.createElement("div");
            popoverButtonList.className = "reactionTypeButtonList";
            availableReactions.forEach((reactionType) => {
                const reactionTypeButton = document.createElement("button");
                reactionTypeButton.setAttribute("role", "option");
                reactionTypeButton.setAttribute("aria-selected", "false");
                reactionTypeButton.type = "button";
                reactionTypeButton.className = "reactionTypeButton jsTooltip";
                reactionTypeButton.title = reactionType.title;
                reactionTypeButton.dataset.reactionTypeId = reactionType.reactionTypeID.toString();
                reactionTypeButton.dataset.isAssignable = reactionType.isAssignable.toString();
                reactionTypeButton.innerHTML = reactionType.renderedIcon;
                const reactionTypeButtonTitle = document.createElement("span");
                reactionTypeButtonTitle.className = "reactionTypeButtonTitle";
                reactionTypeButtonTitle.innerHTML = reactionType.title;
                reactionTypeButton.appendChild(reactionTypeButtonTitle);
                reactionTypeButton.addEventListener("click", () => this.#click(reactionType.reactionTypeID));
                if (!reactionType.isAssignable) {
                    reactionTypeButton.hidden = true;
                }
                popoverButtonList.appendChild(reactionTypeButton);
            });
            popoverContent.appendChild(popoverButtonList);
            this.#popover.appendChild(popoverContent);
            document.body.appendChild(this.#popover);
            CloseOverlay_1.default.add("WoltLabSuite/Core/Component/Reaction/Button", () => this.cancel());
            window.addEventListener("resize", () => {
                this.cancel();
            }, { passive: true });
            this.#popover.addEventListener("keydown", (event) => {
                this.#handleKeydown(event);
            });
            Listener_1.default.trigger();
        }
        #showPopover(button) {
            const popover = this.#getPopover();
            button.setAttribute("aria-owns", popover.id);
            UiAlignment.set(popover, button, {
                horizontal: "center",
                vertical: UiScreen.is("screen-xs") ? "bottom" : "top",
            });
            // The popover could be rendered below the input field on mobile, in which case
            // the "first" button is displayed at the bottom and thus farthest away. Reversing
            // the display order will restore the logic by placing the "first" button as close
            // to the react button as possible.
            const inverseOrder = popover.style.getPropertyValue("bottom") === "auto";
            if (inverseOrder) {
                popover.classList.add("inverseOrder");
            }
            else {
                popover.classList.remove("inverseOrder");
            }
            popover.querySelectorAll(".reactionTypeButton.active").forEach((element) => {
                element.classList.remove("active");
                element.setAttribute("aria-selected", "false");
            });
            if (parseInt(button.dataset.reactionTypeId)) {
                const reactionTypeButton = popover.querySelector(`.reactionTypeButton[data-reaction-type-id="${button.dataset.reactionTypeId}"]`);
                reactionTypeButton.classList.add("active");
                reactionTypeButton.setAttribute("aria-selected", "true");
                reactionTypeButton.hidden = false;
            }
            popover.classList.remove("forceHide");
            popover.classList.add("active");
            this.#getFocusTrap().activate();
        }
        #hidePopover() {
            const popover = this.#getPopover();
            popover.classList.remove("active");
            popover
                .querySelectorAll('.reactionTypeButton[data-is-assignable="0"]')
                .forEach((button) => (button.hidden = true));
            this.#getFocusTrap().deactivate();
        }
        #getPopover() {
            if (this.#popover === undefined) {
                this.#createPopover();
            }
            return this.#popover;
        }
        #getFocusTrap() {
            if (this.#focusTrap === undefined) {
                this.#focusTrap = (0, focus_trap_1.createFocusTrap)(this.#getPopover(), {
                    allowOutsideClick: true,
                    escapeDeactivates: () => {
                        this.cancel();
                        return false;
                    },
                    preventScroll: true,
                });
            }
            return this.#focusTrap;
        }
        #handleKeydown(event) {
            const element = event.target;
            if (!(element instanceof HTMLElement)) {
                return;
            }
            const buttons = Array.from(document.querySelectorAll('.reactionTypeButton[data-is-assignable="1"]'));
            if (!buttons.length) {
                return;
            }
            switch (event.key) {
                case "Left":
                case "ArrowLeft": {
                    event.preventDefault();
                    const index = buttons.indexOf(element);
                    if (index - 1 >= 0) {
                        buttons[index - 1].focus();
                    }
                    break;
                }
                case "Right":
                case "ArrowRight": {
                    event.preventDefault();
                    const index = buttons.indexOf(element);
                    if (index + 1 < buttons.length) {
                        buttons[index + 1].focus();
                    }
                    break;
                }
                case "Home":
                    event.preventDefault();
                    buttons[0].focus();
                    break;
                case "End":
                    event.preventDefault();
                    buttons[buttons.length - 1].focus();
                    break;
            }
        }
    }
    function updateReactionSummary(objectType, objectId, cachedReactions, selectedReaction) {
        const reactions = new Map();
        Object.entries(cachedReactions).forEach(([key, value]) => {
            reactions.set(parseInt(key), value);
        });
        const component = document.querySelector(`woltlab-core-reaction-summary[object-type="${objectType}"][object-id="${objectId}"]`);
        component?.setData(reactions, selectedReaction);
    }
    function setupToggleButton() {
        (0, Selector_1.wheneverFirstSeen)("[data-reaction-object-type]", (button) => {
            button.addEventListener("click", (0, PromiseMutex_1.promiseMutex)(() => toggleButton(button)));
        });
    }
    async function toggleButton(button) {
        const objectId = parseInt(button.dataset.objectId);
        const objectType = button.dataset.reactionObjectType;
        let reactionTypeId = 0;
        let reactions;
        if (button.classList.contains("active")) {
            reactions = (await (0, RevertReaction_1.revertReaction)(objectType, objectId)).reactions;
            button.dataset.reactionTypeId = "0";
            button.classList.remove("active");
            button.setAttribute("aria-pressed", "false");
        }
        else {
            reactionTypeId = availableReactions[0].reactionTypeID;
            reactions = (await (0, SetReaction_1.setReaction)(objectType, objectId, reactionTypeId)).reactions;
            button.dataset.reactionTypeId = reactionTypeId.toString();
            button.classList.add("active");
            button.setAttribute("aria-pressed", "true");
        }
        updateReactionSummary(objectType, objectId, reactions, reactionTypeId);
    }
    function setupPopoverButton() {
        const reactionPopover = new ReactionPopover();
        (0, Selector_1.wheneverFirstSeen)("[data-reaction-object-type]", (button) => {
            let isOpen = false;
            button.setAttribute("aria-haspopup", "listbox");
            button.setAttribute("aria-expanded", "false");
            button.addEventListener("click", (event) => {
                event.stopPropagation(); // Necessary so that `Ui/CloseOverlay` does not close the popover immediately
                if (isOpen) {
                    reactionPopover.cancel();
                    isOpen = false;
                    return;
                }
                isOpen = true;
                button.setAttribute("aria-expanded", "true");
                void reactionPopover.open(button).then(async (result) => {
                    isOpen = false;
                    button.setAttribute("aria-expanded", "false");
                    button.removeAttribute("aria-owns");
                    if (!result.ok) {
                        return;
                    }
                    const oldReactionTypeId = parseInt(button.dataset.reactionTypeId);
                    const objectId = parseInt(button.dataset.objectId);
                    const objectType = button.dataset.reactionObjectType;
                    let reactionTypeId = 0;
                    let reactions;
                    if (result.reactionTypeId == oldReactionTypeId) {
                        reactions = (await (0, RevertReaction_1.revertReaction)(objectType, objectId)).reactions;
                        button.dataset.reactionTypeId = "0";
                        button.classList.remove("active");
                        button.setAttribute("aria-pressed", "false");
                    }
                    else {
                        reactionTypeId = result.reactionTypeId;
                        reactions = (await (0, SetReaction_1.setReaction)(objectType, objectId, reactionTypeId)).reactions;
                        button.dataset.reactionTypeId = reactionTypeId.toString();
                        button.classList.add("active");
                        button.setAttribute("aria-pressed", "true");
                    }
                    updateReactionSummary(objectType, objectId, reactions, reactionTypeId);
                });
            });
        });
    }
    function setup() {
        if (availableReactions.length === 1) {
            setupToggleButton();
        }
        else {
            setupPopoverButton();
        }
    }
});
