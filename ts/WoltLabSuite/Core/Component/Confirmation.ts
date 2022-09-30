import { ConfirmationDelete } from "./Confirmation/Delete";

class ConfirmationSetup {
  delete(question: string): ConfirmationDelete {
    return new ConfirmationDelete(question);
  }
}

export function confirmationFactory(): ConfirmationSetup {
  return new ConfirmationSetup();
}
