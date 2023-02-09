/**
 * Data handler for a wysiwyg attachment form builder field that stores the temporary hash.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.2
 */

import Value from "../Value";

class Attachment extends Value {
  constructor(fieldId: string) {
    super(fieldId + "_tmpHash");
  }
}

export = Attachment;
