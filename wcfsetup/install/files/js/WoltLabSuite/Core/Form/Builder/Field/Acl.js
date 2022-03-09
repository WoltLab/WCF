/**
 * Data handler for a acl form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Acl
 * @since 5.2.3
 */
define(["require", "exports", "tslib", "./Field", "../../../Core"], function (require, exports, tslib_1, Field_1, Core) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    Core = tslib_1.__importStar(Core);
    class Acl extends Field_1.default {
        _getData() {
            return {
                [this._fieldId]: this._aclList.getData(),
            };
        }
        _readField() {
            // does nothing
        }
        setAclList(aclList) {
            this._aclList = aclList;
            return this;
        }
    }
    Core.enableLegacyInheritance(Acl);
    return Acl;
});
