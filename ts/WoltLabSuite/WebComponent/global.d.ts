// This is a workaround for TS2669.
export {};

// This is duplicated from the regular `global.ts` that we cannot
// use because of the `import` and the conflicting `module` target.
type Codepoint = string;
type IconStyles = string[];
type IconMetadata = [Codepoint, IconStyles];

declare global {
  interface Window {
    getFontAwesome6IconMetadata: (name: string) => IconMetadata | undefined;
  }
}
