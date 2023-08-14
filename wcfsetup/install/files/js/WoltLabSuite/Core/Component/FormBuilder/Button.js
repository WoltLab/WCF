/**
 * Binds to button-like elements with the attribute [data-formbuilder] and invokes
 * the endpoint to request the form builder dialog.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "../Dialog", "../../Helper/Selector"], function (require, exports, Dialog_1, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    const reponseIdentifier = "__Psr15DialogFormResponse";
    async function requestForm(element) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(element.dataset.endpoint);
        if (!ok) {
            return;
        }
        const event = new CustomEvent("formBuilder:result", {
            cancelable: true,
            detail: {
                result,
            },
        });
        element.dispatchEvent(event);
        if (event.defaultPrevented) {
            return;
        }
        if (typeof result === "object" && result !== null && Object.hasOwn(result, reponseIdentifier)) {
            const payload = result.payload;
            if ("reload" in payload) {
                window.location.reload();
            }
            else {
                window.location.href = payload.redirectUrl;
            }
            return;
        }
    }
    function setup() {
        (0, Selector_1.wheneverSeen)("[data-formbuilder]", (element) => {
            if (element.tagName !== "A" && element.tagName !== "BUTTON") {
                throw new TypeError("Cannot initialize the FormBuilder on non button-like elements", {
                    cause: {
                        element,
                    },
                });
            }
            if (!element.dataset.endpoint) {
                throw new Error("Missing the [data-endpoint] attribute.", {
                    cause: {
                        element,
                    },
                });
            }
            element.addEventListener("click", (event) => {
                event.preventDefault();
                void requestForm(element);
            });
        });
    }
    exports.setup = setup;
});
