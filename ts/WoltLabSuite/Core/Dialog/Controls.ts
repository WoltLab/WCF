import { ModalDialog, ModalDialogFormControl } from "./modal-dialog";

type AlertOptions = {
  primary: string;
};

export class DialogControls {
  readonly #dialog: ModalDialog;

  constructor(dialog: ModalDialog) {
    this.#dialog = dialog;
  }

  asAlert(options?: Partial<AlertOptions>): ModalDialog {
    options = Object.assign(
      {
        primary: "",
      },
      options,
    );

    this.#dialog.attachFormControls(options as ModalDialogFormControl);

    return this.#dialog;
  }

  withoutControls(): ModalDialog {
    return this.#dialog;
  }
}

export default DialogControls;
