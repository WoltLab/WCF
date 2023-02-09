/**
 * Data handler for an item list form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import Field from "./Field";
import * as UiItemListStatic from "../../../Ui/ItemList/Static";
import { FormBuilderData } from "../Data";

class ItemList extends Field {
  protected _getData(): FormBuilderData {
    const values: string[] = [];
    UiItemListStatic.getValues(this._fieldId).forEach((item) => {
      if (item.objectId) {
        values[item.objectId] = item.value;
      } else {
        values.push(item.value);
      }
    });

    return {
      [this._fieldId]: values,
    };
  }
}

export = ItemList;
