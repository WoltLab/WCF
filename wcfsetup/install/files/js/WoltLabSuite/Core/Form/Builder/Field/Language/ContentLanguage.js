/**
 * Data handler for a content language form builder field in an Ajax form.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Language/ContentLanguage
 * @since	5.2
 */
define(['Core', 'WoltLabSuite/Core/Language/Chooser', '../Value'], function (Core, LanguageChooser, FormBuilderFieldValue) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldContentLanguage(fieldId) {
        this.init(fieldId);
    }
    ;
    Core.inherit(FormBuilderFieldContentLanguage, FormBuilderFieldValue, {
        /**
         * @see	WoltLabSuite/Core/Form/Builder/Field/Field#destroy
         */
        destroy: function () {
            LanguageChooser.removeChooser(this._fieldId);
        }
    });
    return FormBuilderFieldContentLanguage;
});
