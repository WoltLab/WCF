/**
 * Provides a shortcut to submit the editor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "./Event"], function (require, exports, Event_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupSubmitShortcut = setupSubmitShortcut;
    function getSubmitShortcut(submitButton) {
        return (event) => {
            if (event.code !== "KeyS") {
                return;
            }
            let shouldSubmit;
            if (window.navigator.platform.startsWith("Mac")) {
                shouldSubmit = event.ctrlKey && event.altKey;
            }
            else {
                shouldSubmit = event.altKey && !event.ctrlKey;
            }
            if (!shouldSubmit) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            submitButton.click();
        };
    }
    function setupSubmitShortcut(ckeditor) {
        const container = ckeditor.element.closest("form, .message, .jsOuterEditorContainer");
        if (container === null) {
            return;
        }
        const formSubmit = container.querySelector(".formSubmit");
        if (formSubmit === null) {
            return;
        }
        const submitButton = formSubmit.querySelector('input[type="submit"], button[data-type="save"], button[accesskey="s"]');
        if (submitButton === null) {
            return;
        }
        submitButton.removeAttribute("accesskey");
        const submitShortcut = getSubmitShortcut(submitButton);
        container.addEventListener("keydown", submitShortcut);
        (0, Event_1.listenToCkeditor)(ckeditor.element).destroy(() => {
            container.removeEventListener("keydown", submitShortcut);
        });
    }
});
