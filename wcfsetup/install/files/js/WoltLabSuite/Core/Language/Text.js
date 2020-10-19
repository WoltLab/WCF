/**
 * I18n interface for wysiwyg input fields.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Language/Text
 */
define(['Core', './Input'], function (Core, LanguageInput) {
    "use strict";
    /**
     * @exports     WoltLabSuite/Core/Language/Text
     */
    return {
        /**
         * Initializes an WYSIWYG input field.
         *
         * @param       {string}        elementId               input element id
         * @param       {Object}        values                  preset values per language id
         * @param       {Object}        availableLanguages      language names per language id
         * @param       {boolean}       forceSelection          require i18n input
         */
        init: function (elementId, values, availableLanguages, forceSelection) {
            var element = elById(elementId);
            if (!element || element.nodeName !== 'TEXTAREA' || !element.classList.contains('wysiwygTextarea')) {
                throw new Error("Expected <textarea class=\"wysiwygTextarea\" /> for id '" + elementId + "'.");
            }
            LanguageInput.init(elementId, values, availableLanguages, forceSelection);
            //noinspection JSUnresolvedFunction
            LanguageInput.registerCallback(elementId, 'select', this._callbackSelect.bind(this));
            //noinspection JSUnresolvedFunction
            LanguageInput.registerCallback(elementId, 'submit', this._callbackSubmit.bind(this));
        },
        /**
         * Refreshes the editor content on language switch.
         *
         * @param       {Element}       element         input element
         * @protected
         */
        _callbackSelect: function (element) {
            if (window.jQuery !== undefined) {
                window.jQuery(element).redactor('code.set', element.value);
            }
        },
        /**
         * Refreshes the input element value on submit.
         *
         * @param       {Element}       element         input element
         * @protected
         */
        _callbackSubmit: function (element) {
            if (window.jQuery !== undefined) {
                element.value = window.jQuery(element).redactor('code.get');
            }
        }
    };
});
