import { DialogSetup } from "./Dialog/Setup";
import { setup as setupDialog } from "../Element/woltlab-core-dialog";
import { setup as setupDialogControl } from "../Element/woltlab-core-dialog-control";

export function dialogFactory(): DialogSetup {
  setupDialog();
  setupDialogControl();

  return new DialogSetup();
}
