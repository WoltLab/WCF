import { DialogSetup } from "./Dialog/Setup";
import { setup as setupFormControl } from "../Element/woltlab-core-dialog-control";

export function dialogFactory(): DialogSetup {
  setupFormControl();

  return new DialogSetup();
}

export * from "../Element/woltlab-core-dialog";
