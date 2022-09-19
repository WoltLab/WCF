import { DialogSetup } from "./Dialog/Setup";

export function dialogFactory(): DialogSetup {
  return new DialogSetup();
}

export * from "./Dialog/modal-dialog";
