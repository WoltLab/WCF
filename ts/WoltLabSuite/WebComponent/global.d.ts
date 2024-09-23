// This is a workaround for TS2669.
import { I18n } from "emoji-picker-element/shared";
import type * as Language from "./Language";
import { Template } from "./Template";

export {};

// This is duplicated from the regular `global.ts` that we cannot
// use because of the `import` and the conflicting `module` target.
type Codepoint = string;
type HasRegularVariant = boolean;
type IconMetadata = [Codepoint, HasRegularVariant];

interface Reaction {
  title: string;
  renderedIcon: string;
  iconPath: string;
  showOrder: number;
  reactionTypeID: number;
  isAssignable: 1 | 0;
}

type IconSize = 16 | 24 | 32 | 48 | 64 | 96 | 128 | 144;

declare global {
  interface Window {
    REACTION_TYPES: {
      [key: string]: Reaction;
    };
    TIME_NOW: number;
    LANGUAGE_CODE: string;

    getFontAwesome6IconMetadata: (name: string) => IconMetadata | undefined;

    HTMLParsedElement: HTMLParsedElement;
    WoltLabLanguage: typeof Language;
    WoltLabTemplate: typeof Template;
    EmojiPickerLocales: {
      [key: string]: I18n;
    };
  }

  class HTMLParsedElement extends HTMLElement {
    parsedCallback(): void;
  }

  interface FaIcon extends HTMLElement {
    readonly name: string;
    readonly solid: boolean;
    size: IconSize;

    setIcon: (name: string, forceSolid?: boolean) => void;
  }

  interface HTMLElementTagNameMap {
    "fa-icon": FaIcon;
  }
}
