import { DatabaseObjectActionResponse } from "../../Ajax/Data";
import { ClipboardActionData } from "../../Controller/ClipboardData";

export interface ObjectActionData {
  data: DatabaseObjectActionResponse;
  objectElement: HTMLElement;
}

export interface ClipboardData {
  data: ClipboardActionData;
  listItem: HTMLLIElement;
  responseData: DatabaseObjectActionResponse;
}
