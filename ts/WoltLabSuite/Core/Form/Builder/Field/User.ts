/**
 * Data handler for a user form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import Field from "./Field";
import { FormBuilderData } from "../Data";
import * as UiItemList from "../../../Ui/ItemList";

class User extends Field {
  protected _getData(): FormBuilderData {
    const usernames = UiItemList.getValues(this._fieldId).map((item) => item.value);

    return {
      [this._fieldId]: usernames.join(","),
    };
  }
}

export = User;
