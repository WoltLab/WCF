/**
 * Data handler for a file processor form builder field in an Ajax form.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */

import Field from "WoltLabSuite/Core/Form/Builder/Field/Field";
import { FormBuilderData } from "WoltLabSuite/Core/Form/Builder/Data";
import { getValues } from "WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor";

export default class FileProcessor extends Field {
  protected _getData(): FormBuilderData {
    const value = getValues(this._fieldId);
    if (value === undefined) {
      return {};
    }

    const data: FormBuilderData = {
      [this._fieldId]: value,
    };

    // The files were uploaded with the token, the server must receive it again.
    const uploaderToken = document.getElementById(`${this._fieldId}_uploaderToken`) as HTMLInputElement | null;
    if (uploaderToken !== null) {
      data[`${this._fieldId}_uploaderToken`] = uploaderToken.value;
    }

    return data;
  }

  protected _readField(): void {
    // does nothing
  }
}
