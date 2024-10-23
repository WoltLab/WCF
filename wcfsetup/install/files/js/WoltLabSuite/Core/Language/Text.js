/**
 * I18n interface for wysiwyg input fields.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./Input", "../Component/Ckeditor"], function (require, exports, tslib_1, LanguageInput, Ckeditor_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    LanguageInput = tslib_1.__importStar(LanguageInput);
    /**
     * Refreshes the editor content on language switch.
     */
    function callbackSelect(element) {
        (0, Ckeditor_1.getCkeditor)(element).setHtml(element.value);
    }
    /**
     * Refreshes the input element value on submit.
     */
    function callbackSubmit(element) {
        element.value = (0, Ckeditor_1.getCkeditor)(element).getHtml();
    }
    /**
     * Initializes an WYSIWYG input field.
     */
    function init(elementId, values, availableLanguages, forceSelection) {
        const element = document.getElementById(elementId);
        if (!element || element.nodeName !== "TEXTAREA" || !element.classList.contains("wysiwygTextarea")) {
            throw new Error(`Expected <textarea class="wysiwygTextarea" /> for id '${elementId}'.`);
        }
        LanguageInput.init(elementId, values, availableLanguages, forceSelection);
        LanguageInput.registerCallback(elementId, "select", callbackSelect);
        LanguageInput.registerCallback(elementId, "submit", callbackSubmit);
        // CKEditor does not permanently mirror the contents to the <textarea>.
        LanguageInput.registerCallback(elementId, "beforeSelect", callbackSubmit);
    }
});
