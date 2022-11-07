// This is a workaround for TS2669.
export {};

// This is duplicated from the regular `global.ts` that we cannot
// use because of the `import` and the conflicting `module` target.
type Codepoint = string;
type HasRegularVariant = boolean;
type IconMetadata = [Codepoint, HasRegularVariant];

import type * as LanguageStore from "./LanguageStore";
import { Template } from "./Template";

declare global {
  interface Window {
    TIME_NOW: number;

    getFontAwesome6IconMetadata: (name: string) => IconMetadata | undefined;

    WoltLabLanguageStore: typeof LanguageStore;
    WoltLabTemplate: typeof Template;
  }
}
