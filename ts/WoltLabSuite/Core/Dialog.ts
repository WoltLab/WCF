import { DialogSetup } from "./Dialog/Setup";
import { setup as setupFormControl } from "./Dialog/form-control";

export function dialogFactory(): DialogSetup {
  setupFormControl();

  return new DialogSetup();
}

export * from "./Dialog/modal-dialog";
