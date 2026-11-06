/**
 * Data handler for a wysiwyg attachment form builder field that stores the temporary hash.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import Field from "../Field";
import { FormBuilderData } from "../../Data";

class Attachment extends Field {
  protected _getData(): FormBuilderData {
    const tmpHashes = Array.from(
      document.querySelectorAll<HTMLInputElement>(`input[name="${this._fieldId}_tmpHash[]"]`),
      (input) => input.value,
    );
    if (tmpHashes.length === 0) {
      return {};
    }

    return {
      [this._fieldId + "_tmpHash"]: tmpHashes,
    };
  }

  protected _readField(): void {
    // The temporary hashes are stored in multiple hidden inputs that share the same name.
  }
}

export = Attachment;
