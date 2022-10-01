import { ConfirmationDelete } from "./Confirmation/Delete";
import { ConfirmationSoftDelete } from "./Confirmation/SoftDelete";

class ConfirmationSetup {
  delete(question: string): ConfirmationDelete {
    return new ConfirmationDelete(question);
  }

  softDelete(question: string): ConfirmationSoftDelete {
    return new ConfirmationSoftDelete(question);
  }
}

export function confirmationFactory(): ConfirmationSetup {
  return new ConfirmationSetup();
}
