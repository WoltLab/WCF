import { DatabaseObjectActionResponse } from "../../Ajax/Data";

export interface ClipboardOptions {
  hasMarkedItems: boolean;
  pageClassName: string;
  pageObjectId?: number;
}

export interface ContainerData {
  checkboxes: HTMLCollectionOf<HTMLInputElement>;
  element: HTMLElement;
  markAll: HTMLInputElement | null;
  markedObjectIds: Set<number>;
}

export interface ClipboardItemData {
  items: { [key: string]: ClipboardActionData };
  label: string;
  reloadPageOnSuccess: string[];
}

export interface ClipboardActionData {
  actionName: string;
  internalData: ArbitraryObject;
  label: string;
  parameters: {
    actionName?: string;
    className?: string;
    objectIDs: number[];
    template: string;
  };
  url: string;
}

export interface AjaxResponseMarkedItems {
  [key: string]: number[];
}

export interface AjaxResponse extends DatabaseObjectActionResponse {
  returnValues: {
    action: string;
    items?: {
      // The key is the `typeName`
      [key: string]: ClipboardItemData;
    };
    markedItems?: AjaxResponseMarkedItems;
    objectType: string;
  };
}
