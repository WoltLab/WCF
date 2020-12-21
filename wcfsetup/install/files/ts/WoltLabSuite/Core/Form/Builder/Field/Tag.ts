/**
 * Data handler for a tag form builder field in an Ajax form.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Tag
 * @since 5.2
 */

import Field from "./Field";
import * as UiItemList from "../../../Ui/ItemList";
import { FormBuilderData } from "../Data";
import * as Core from "../../../Core";

class Tag extends Field {
  protected _getData(): FormBuilderData {
    const values: string[] = UiItemList.getValues(this._fieldId).map((item) => item.value);

    return {
      [this._fieldId]: values,
    };
  }
}

Core.enableLegacyInheritance(Tag);

export = Tag;
