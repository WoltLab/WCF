/**
 * Helper module to expose a fluent API to create
 * dialogs through `dialogFactory()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import WoltlabCoreDialogElement, { WoltlabCoreDialogControlOptions } from "../../Element/woltlab-core-dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";

type AlertOptions = {
  primary: string;
};

type ConfirmationOptions = {
  primary: string;
};

type PromptOptions = {
  extra: string;
  primary: string;
};

export class DialogControls {
  readonly #dialog: WoltlabCoreDialogElement;

  constructor(dialog: WoltlabCoreDialogElement) {
    this.#dialog = dialog;
  }

  asAlert(options?: Partial<AlertOptions>): WoltlabCoreDialogElement {
    const formControlOptions: WoltlabCoreDialogControlOptions = {
      cancel: undefined,
      extra: undefined,
      isAlert: true,
      primary: options?.primary || getPhrase("wcf.dialog.button.primary"),
    };

    this.#dialog.attachControls(formControlOptions);

    return this.#dialog;
  }

  asConfirmation(options?: Partial<ConfirmationOptions>): WoltlabCoreDialogElement {
    const formControlOptions: WoltlabCoreDialogControlOptions = {
      cancel: "",
      extra: undefined,
      isAlert: true,
      primary: options?.primary || getPhrase("wcf.dialog.button.primary.confirm"),
    };

    this.#dialog.attachControls(formControlOptions);

    return this.#dialog;
  }

  asPrompt(options?: Partial<PromptOptions>): WoltlabCoreDialogElement {
    const formControlOptions: WoltlabCoreDialogControlOptions = {
      cancel: "",
      extra: options?.extra,
      isAlert: false,
      primary: options?.primary || getPhrase("wcf.dialog.button.primary.submit"),
    };

    this.#dialog.attachControls(formControlOptions);

    return this.#dialog;
  }

  withoutControls(): WoltlabCoreDialogElement {
    return this.#dialog;
  }
}

export default DialogControls;
