/**
 * Inline editor action to enable an object.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { Action, InlineEditor } from "WoltLabSuite/Core/Component/Inline/Editor";
import { DropdownBuilderItemData } from "WoltLabSuite/Core/Ui/Dropdown/Builder";

export class Enable implements Action {
  protected readonly inlineEditor: InlineEditor;
  protected readonly endpoint: string | URL;

  constructor(inlineEditor: InlineEditor, endpoint: string | URL) {
    this.inlineEditor = inlineEditor;
    this.endpoint = endpoint;
  }

  get item(): DropdownBuilderItemData {
    return {
      label: "wcf.global.button.enable",
      callback: async () => {
        // TODO
      },
    };
  }

  isVisible(): boolean {
    return (
      this.inlineEditor.getPermissions()["canEnable"] &&
      !this.inlineEditor.getState("isDeleted") &&
      this.inlineEditor.getState("isDisabled")
    );
  }
}
