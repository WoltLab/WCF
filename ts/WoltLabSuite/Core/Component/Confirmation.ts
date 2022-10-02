import { ConfirmationCustom } from "./Confirmation/Custom";
import { ConfirmationDelete } from "./Confirmation/Delete";
import { ConfirmationSoftDelete } from "./Confirmation/SoftDelete";

class ConfirmationSetup {
  custom(question: string): ConfirmationCustom {
    return new ConfirmationCustom(question);
  }

  delete(question: string): ConfirmationDelete {
    return new ConfirmationDelete(question);
  }

  restore(question: string): ConfirmationCustom {
    return this.custom(question);
  }

  softDelete(question: string): ConfirmationSoftDelete {
    return new ConfirmationSoftDelete(question);
  }
}

export function confirmationFactory(): ConfirmationSetup {
  return new ConfirmationSetup();
}
