import { deleteObject } from "WoltLabSuite/Core/Api/DeleteObject";
import { confirmationFactory } from "../../Confirmation";
import * as UiNotification from "WoltLabSuite/Core/Ui/Notification";

async function handleDelete(row: HTMLTableRowElement, objectName: string, endpoint: string): Promise<void> {
  const confirmationResult = await confirmationFactory().delete(objectName);
  if (!confirmationResult) {
    return;
  }

  const result = await deleteObject(endpoint);
  if (!result.ok) {
    return;
  }

  row.remove();

  // TODO: This shows a generic success message and should be replaced with a more specific message.
  UiNotification.show();
}

export function setup(table: HTMLTableElement): void {
  table.addEventListener("action", (event: CustomEvent) => {
    if (event.detail.action === "delete") {
      void handleDelete(event.target as HTMLTableRowElement, event.detail.objectName!, event.detail.endpoint!);
    }
  });
}
