import { DialogSetup } from "./Dialog/Setup";

export function createDialog(): DialogSetup {
  return new DialogSetup();
}

export * from "./Dialog/modal-dialog";
