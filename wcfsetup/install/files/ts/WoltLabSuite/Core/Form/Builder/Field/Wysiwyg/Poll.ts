/**
 * Data handler for the poll options.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Form/Builder/Field/Wysiwyg/Poll
 * @since 5.2
 */

import Field from "../Field";
import * as Core from "../../../../Core";
import { FormBuilderData } from "../../Data";
import UiPollEditor from "../../../../Ui/Poll/Editor";

class Poll extends Field {
  protected _pollEditor: UiPollEditor;

  protected _getData(): FormBuilderData {
    return this._pollEditor.getData();
  }

  protected _readField(): void {
    // does nothing
  }

  public setPollEditor(pollEditor: UiPollEditor): void {
    this._pollEditor = pollEditor;
  }
}

Core.enableLegacyInheritance(Poll);

export = Poll;
