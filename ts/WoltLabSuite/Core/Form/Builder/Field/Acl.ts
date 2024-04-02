/**
 * Data handler for a acl form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2.3
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import AclList from "WoltLabSuite/Core/Ui/Acl/List";

class Acl extends Field {
  protected _aclList: AclList;

  protected _getData(): FormBuilderData {
    return {
      [this._fieldId]: this._aclList.getData(),
    };
  }

  protected _readField(): void {
    // does nothing
  }

  public setAclList(aclList: AclList): Acl {
    this._aclList = aclList;

    return this;
  }
}

export = Acl;
