import WoltlabCoreDialogElement, { WoltlabCoreDialogFormControl } from "../../Element/woltlab-core-dialog";
import * as Language from "../../Language";

type AlertOptions = {
  primary: string;
};

type ConfirmationOptions = {
  primary: string;
};

type PromptOptions = {
  cancel: string;
  extra: string;
  primary: string;
};

export class DialogControls {
  readonly #dialog: WoltlabCoreDialogElement;

  constructor(dialog: WoltlabCoreDialogElement) {
    this.#dialog = dialog;
  }

  asAlert(options?: Partial<AlertOptions>): WoltlabCoreDialogElement {
    const formControlOptions: WoltlabCoreDialogFormControl = {
      cancel: undefined,
      extra: undefined,
      isAlert: true,
      primary: options?.primary || "",
    };

    this.#dialog.attachFormControls(formControlOptions);

    return this.#dialog;
  }

  asConfirmation(options?: Partial<ConfirmationOptions>): WoltlabCoreDialogElement {
    const formControlOptions: WoltlabCoreDialogFormControl = {
      cancel: "",
      extra: undefined,
      isAlert: true,
      primary: options?.primary || Language.get("wcf.dialog.button.primary.confirm"),
    };

    this.#dialog.attachFormControls(formControlOptions);

    return this.#dialog;
  }

  asPrompt(options?: Partial<PromptOptions>): WoltlabCoreDialogElement {
    const formControlOptions: WoltlabCoreDialogFormControl = {
      cancel: options?.cancel || "",
      extra: options?.extra,
      isAlert: false,
      primary: options?.primary || Language.get("wcf.dialog.button.primary.submit"),
    };

    this.#dialog.attachFormControls(formControlOptions);

    return this.#dialog;
  }

  withoutControls(): WoltlabCoreDialogElement {
    return this.#dialog;
  }
}

export default DialogControls;
