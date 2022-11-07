/**
 * @deprecated 6.0 Access window.WoltLabLanguageStore directly.
 */

export function get(key: string, parameters: object = {}): string {
  return window.WoltLabLanguageStore.get(key, parameters);
}

export function add(key: string, value: Phrase): void {
  window.WoltLabLanguageStore.add(key, value);
}

export type Phrase = (parameters: object) => string;
