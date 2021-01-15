import { DatabaseObjectActionPayload } from "../../Ajax/Data";

export type CallbackDropdownInit = (list: HTMLUListElement) => void;

export type CallbackSelect = (item: HTMLElement) => boolean;

export interface SearchInputOptions {
  ajax?: Partial<DatabaseObjectActionPayload>;
  autoFocus?: boolean;
  callbackDropdownInit?: CallbackDropdownInit;
  callbackSelect?: CallbackSelect;
  delay?: number;
  excludedSearchValues?: string[];
  minLength?: number;
  noResultPlaceholder?: string;
  preventSubmit?: boolean;
}
