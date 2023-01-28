import { getStoragePrefix } from "../../Core";

type Payload = {
  html: string;
  timestamp: number;
};

function getLocalStorageKey(identifier: string): string {
  return `${getStoragePrefix()}ckeditor5-${identifier}`;
}

export function saveDraft(identifier: string, html: string): void {
  const payload: Payload = {
    html,
    timestamp: Date.now(),
  };

  try {
    window.localStorage.setItem(getLocalStorageKey(identifier), JSON.stringify(payload));
  } catch (e) {
    console.warn("Unable to write to the local storage.", e);
  }
}

export function removeExpiredDrafts(): void {
  const oneWeekAgo = Date.now() - 7 * 86_400;

  Object.keys(localStorage)
    .filter((key) => key.startsWith(`ckeditor5-`))
    .forEach((key) => {
      let value: string | null;

      try {
        value = window.localStorage.getItem(key);
      } catch {
        // Nothing we can do, forget it.
        return;
      }

      if (value === null) {
        // The value is no longer available.
        return;
      }

      let payload: Payload | undefined = undefined;
      try {
        payload = JSON.parse(value);
      } catch {
        // `payload` remains set to `undefined`.
      }

      if (payload === undefined || payload.timestamp < oneWeekAgo) {
        try {
          localStorage.removeItem(key);
        } catch {
          // Nothing we can do, forget it.
        }
      }
    });
}
