/**
 * Inline editor action to restore an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { InlineEditor } from "WoltLabSuite/Core/Component/Inline/Editor";
import { Simple } from "WoltLabSuite/Core/Component/Inline/Actions/Simple";

export class Restore extends Simple {
  constructor(inlineEditor: InlineEditor, endpoint: string | URL) {
    super(inlineEditor, "wcf.global.button.restore", endpoint);
  }

  responseOk(): void {
    this.inlineEditor.update({
      isDeleted: 0,
    });
  }

  isVisible(): boolean {
    return this.inlineEditor.getPermissions()["canRestore"] && this.inlineEditor.getState("isDeleted");
  }
}
