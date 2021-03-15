import { DatabaseObjectActionResponse } from "../../Ajax/Data";
import { ClipboardActionData } from "../../Controller/Clipboard/Data";

export interface ObjectActionData {
  data: DatabaseObjectActionResponse;
  objectElement: HTMLElement;
}

export interface ClipboardData {
  data: ClipboardActionData;
  listItem: HTMLLIElement;
  responseData: DatabaseObjectActionResponse;
}
