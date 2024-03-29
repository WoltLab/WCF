/**
 * Data handler for a acl form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2.3
 */
define(["require", "exports", "tslib", "./Field"], function (require, exports, tslib_1, Field_1) {
    "use strict";
    Field_1 = tslib_1.__importDefault(Field_1);
    class Acl extends Field_1.default {
        _aclList;
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
    return Acl;
});
