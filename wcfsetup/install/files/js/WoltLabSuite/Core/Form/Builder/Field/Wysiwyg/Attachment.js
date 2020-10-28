/**
 * Data handler for a wysiwyg attachment form builder field that stores the temporary hash.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Form/Builder/Field/Checked
 * @since	5.2
 */
define(['Core', '../Value'], function (Core, FormBuilderFieldValue) {
    "use strict";
    /**
     * @constructor
     */
    function FormBuilderFieldAttachment(fieldId) {
        this.init(fieldId + '_tmpHash');
    }
    ;
    Core.inherit(FormBuilderFieldAttachment, FormBuilderFieldValue, {});
    return FormBuilderFieldAttachment;
});
