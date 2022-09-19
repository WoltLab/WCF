import { DialogSetup } from "./Dialog/Setup";
import { setup as setupFormControl } from "./Form/form-control";

export function dialogFactory(): DialogSetup {
  setupFormControl();

  return new DialogSetup();
}

export * from "./Dialog/modal-dialog";
