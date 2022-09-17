import { ModalDialog } from "./modal-dialog";

export class DialogControls {
  readonly #dialog: ModalDialog;

  constructor(dialog: ModalDialog) {
    this.#dialog = dialog;
  }

  withoutControls(): ModalDialog {
    return this.#dialog;
  }
}

export default DialogControls;
