/**
 * Data handler for a wysiwyg attachment form builder field that stores the temporary hash.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Field/Wysiwyg/Attachment
 * @since 5.2
 */

import Value from "../Value";
import * as Core from "../../../../Core";

class Attachment extends Value {
  constructor(fieldId: string) {
    super(fieldId + "_tmpHash");
  }
}

Core.enableLegacyInheritance(Attachment);

export = Attachment;
