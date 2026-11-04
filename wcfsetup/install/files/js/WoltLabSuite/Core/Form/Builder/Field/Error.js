/**
 * Displays or removes client-side error messages of form builder fields and keeps the
 * ARIA attributes of the field in sync.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setFieldError = setFieldError;
    function getAriaTarget(fieldId, container) {
        if (container.getAttribute("role") === "group") {
            return container;
        }
        const label = document.getElementById(fieldId + "Label");
        if (label instanceof HTMLLabelElement && label.htmlFor !== "") {
            return document.getElementById(label.htmlFor);
        }
        return document.getElementById(fieldId);
    }
    function updateDescribedBy(element, id, add) {
        const ids = (element.getAttribute("aria-describedby") ?? "").split(" ").filter((value) => {
            return value !== "" && value !== id;
        });
        if (add) {
            ids.unshift(id);
        }
        if (ids.length === 0) {
            element.removeAttribute("aria-describedby");
        }
        else {
            element.setAttribute("aria-describedby", ids.join(" "));
        }
    }
    function updateAriaAttributes(fieldId, container, hasError) {
        const target = getAriaTarget(fieldId, container);
        if (target === null) {
            return;
        }
        const errorId = fieldId + "Error";
        if (target === container) {
            // `aria-invalid` and `aria-errormessage` are not supported by `role="group"`
            updateDescribedBy(target, errorId, hasError);
        }
        else if (hasError) {
            target.setAttribute("aria-invalid", "true");
            target.setAttribute("aria-errormessage", errorId);
        }
        else {
            target.removeAttribute("aria-invalid");
            target.removeAttribute("aria-errormessage");
        }
    }
    function getErrorContainer(fieldId, container) {
        const errorId = fieldId + "Error";
        let errorContainer = document.getElementById(errorId);
        if (errorContainer !== null) {
            return errorContainer;
        }
        errorContainer = document.createElement("div");
        errorContainer.id = errorId;
        errorContainer.classList.add("formField__errors");
        const description = document.getElementById(fieldId + "Description");
        if (description !== null && container.contains(description)) {
            description.before(errorContainer);
        }
        else {
            const input = container.querySelector(":scope > .formField__input");
            if (input !== null) {
                input.after(errorContainer);
            }
            else {
                container.append(errorContainer);
            }
        }
        return errorContainer;
    }
    /**
     * Displays the given error message for the field with the given id, replacing any existing
     * error messages, or removes all error messages if `null` or an empty string is given.
     */
    function setFieldError(fieldId, message, isHtml = false) {
        const container = document.getElementById(fieldId + "Container");
        if (container === null) {
            throw new Error(`Unknown field with id '${fieldId}'.`);
        }
        if (message === null || message === "") {
            document.getElementById(fieldId + "Error")?.remove();
            container.classList.remove("formError");
            updateAriaAttributes(fieldId, container, false);
            return;
        }
        const error = document.createElement("p");
        error.classList.add("formField__error");
        if (isHtml) {
            error.innerHTML = message;
        }
        else {
            error.textContent = message;
        }
        getErrorContainer(fieldId, container).replaceChildren(error);
        container.classList.add("formError");
        updateAriaAttributes(fieldId, container, true);
    }
});
