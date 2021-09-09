/**
 * I18n interface for wysiwyg input fields.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Language/Text
 */
define(["require", "exports", "tslib", "./Input"], function (require, exports, tslib_1, LanguageInput) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    LanguageInput = (0, tslib_1.__importStar)(LanguageInput);
    /**
     * Refreshes the editor content on language switch.
     */
    function callbackSelect(element) {
        if (window.jQuery !== undefined) {
            window.jQuery(element).redactor("code.set", element.value);
        }
    }
    /**
     * Refreshes the input element value on submit.
     */
    function callbackSubmit(element) {
        if (window.jQuery !== undefined) {
            element.value = window.jQuery(element).redactor("code.get");
        }
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
    }
    exports.init = init;
});
