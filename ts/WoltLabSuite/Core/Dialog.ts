import { DialogSetup } from "./Dialog/Setup";
import { setup as setupFormControl } from "./Dialog/woltlab-core-dialog-control";

export function dialogFactory(): DialogSetup {
  setupFormControl();

  return new DialogSetup();
}

export * from "./Dialog/woltlab-core-dialog";
