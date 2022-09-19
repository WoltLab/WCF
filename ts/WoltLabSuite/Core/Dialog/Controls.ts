import { ModalDialog, ModalDialogFormControl } from "./modal-dialog";

type AlertOptions = {
  primary: string;
};

type ConfirmationOptions = {
  cancel: string;
  extra: string;
  primary: string;
};

type PromptOptions = {
  cancel: string;
  extra: string;
  primary: string;
};

export class DialogControls {
  readonly #dialog: ModalDialog;

  constructor(dialog: ModalDialog) {
    this.#dialog = dialog;
  }

  asAlert(options?: Partial<AlertOptions>): ModalDialog {
    const formControlOptions: ModalDialogFormControl = {
      cancel: undefined,
      extra: undefined,
      isAlert: true,
      primary: options?.primary || "",
    };

    this.#dialog.attachFormControls(formControlOptions);

    return this.#dialog;
  }

  asConfirmation(options?: Partial<ConfirmationOptions>): ModalDialog {
    const formControlOptions: ModalDialogFormControl = {
      cancel: options?.cancel || "",
      extra: options?.extra,
      isAlert: true,
      primary: options?.primary || "",
    };

    this.#dialog.attachFormControls(formControlOptions);

    return this.#dialog;
  }

  asPrompt(options?: Partial<PromptOptions>): ModalDialog {
    const formControlOptions: ModalDialogFormControl = {
      cancel: options?.cancel || "",
      extra: options?.extra,
      isAlert: false,
      primary: options?.primary || "",
    };

    this.#dialog.attachFormControls(formControlOptions);

    return this.#dialog;
  }

  withoutControls(): ModalDialog {
    return this.#dialog;
  }
}

export default DialogControls;
