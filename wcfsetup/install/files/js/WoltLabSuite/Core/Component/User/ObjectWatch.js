/**
 * Handles the object watch button.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Event/Handler", "WoltLabSuite/Core/Api/Users/ObjectWatches/Subscribe", "WoltLabSuite/Core/Api/Users/ObjectWatches/Unsubscribe", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Helper/PromiseMutex", "WoltLabSuite/Core/Helper/Selector", "../Snackbar"], function (require, exports, tslib_1, EventHandler, Subscribe_1, Unsubscribe_1, Language_1, PromiseMutex_1, Selector_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    EventHandler = tslib_1.__importStar(EventHandler);
    async function setSubscription(dropdown, isSubscribed) {
        const objectType = dropdown.dataset.objectType;
        const objectID = parseInt(dropdown.dataset.objectId, 10);
        if (isSubscribed) {
            await (0, Subscribe_1.subscribe)(objectType, objectID, true);
        }
        else {
            await (0, Unsubscribe_1.unsubscribe)(objectType, objectID);
        }
        updateDropdowns(objectType, objectID, isSubscribed);
        updateButtons(objectType, objectID, isSubscribed);
        EventHandler.fire("com.woltlab.wcf.objectWatch", "updatedSubscription");
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function updateDropdowns(objectType, objectID, isSubscribed) {
        document
            .querySelectorAll(`.userObjectWatchDropdown[data-object-type="${objectType}"][data-object-id="${objectID}"] .userObjectWatchSelect`)
            .forEach((element) => {
            element.classList.toggle("active", (element.dataset.subscribe === "1") === isSubscribed);
        });
    }
    function updateButtons(objectType, objectID, isSubscribed) {
        document
            .querySelectorAll(`.userObjectWatchDropdownToggle[data-object-type="${objectType}"][data-object-id="${objectID}"]`)
            .forEach((element) => {
            const icon = element.querySelector("fa-icon");
            const label = element.querySelector("span:not(.icon)");
            element.classList.toggle("active", isSubscribed);
            icon.setIcon("bookmark", isSubscribed);
            label.textContent = (0, Language_1.getPhrase)(isSubscribed ? "wcf.user.objectWatch.button.subscribed" : "wcf.user.objectWatch.button.subscribe");
            element.dataset.isSubscribed = isSubscribed ? "1" : "0";
        });
    }
    let isSetupCompleted = false;
    function setup() {
        if (isSetupCompleted) {
            return;
        }
        isSetupCompleted = true;
        (0, Selector_1.wheneverFirstSeen)(".userObjectWatchDropdown", (dropdown) => {
            if (!dropdown.dataset.objectId) {
                throw new Error("Missing objectId for '.userObjectWatchDropdown' element.");
            }
            if (!dropdown.dataset.objectType) {
                throw new Error("Missing objectType for '.userObjectWatchDropdown' element.");
            }
            const setSubscriptionOnce = (0, PromiseMutex_1.promiseMutex)((isSubscribed) => setSubscription(dropdown, isSubscribed));
            dropdown.querySelectorAll(".userObjectWatchSelect").forEach((element) => {
                if (!element.dataset.subscribe) {
                    throw new Error("Missing 'data-subscribe' attribute for '.userObjectWatchSelect' element.");
                }
                const isSubscribed = element.dataset.subscribe === "1";
                element.addEventListener("click", (event) => {
                    event.preventDefault();
                    setSubscriptionOnce(isSubscribed);
                });
            });
        });
    }
});
