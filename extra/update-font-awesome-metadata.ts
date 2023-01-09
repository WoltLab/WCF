import * as fs from "fs";

// This script expects you to write the output to
// ts/WoltLabSuite/WebComponent/fa-metadata.js

if (process.argv.length !== 4) {
  throw new Error(
    "Expected the path to the `icons.json` metadata as argument #1 and the output format (js, php) as argument #2.",
  );
}

const iconsJson = process.argv[2];
const outputFormat = process.argv[3];

if (!fs.existsSync(iconsJson)) {
  throw new Error(`The path '${iconsJson}' does not exist.`);
}

const content = fs.readFileSync(iconsJson, { encoding: "utf8" });
let json: MetadataIcons;
try {
  json = JSON.parse(content);
} catch (e) {
  throw new Error(`Unable to parse the metadata file: ${e.message}`);
}

const values: IconData[] = [];
const aliases: IconAlias[] = [];
Object.entries(json).forEach(([name, icon]) => {
  // Skip brand icons, because those are only supported as SVG
  // through the `{icon}` template helper.
  if (icon.styles.includes("brands")) {
    return;
  }

  const codepoint = String.fromCharCode(parseInt(icon.unicode, 16));
  values.push([name, [codepoint, icon.styles.includes("regular")]]);

  if (icon.aliases && Array.isArray(icon.aliases.names)) {
    icon.aliases.names.forEach((alias) => {
      aliases.push([alias, name]);
    });
  }
});

let output;
switch (outputFormat) {
  case "js":
    output = `(() => {
  const aliases = new Map(
    ${JSON.stringify(aliases)}
  );
  const metadata = new Map(
    ${JSON.stringify(values)}
  );

  window.getFontAwesome6Metadata = () => {
    return new Map(metadata);
  };

  window.getFontAwesome6IconMetadata = (name) => {
    return metadata.get(aliases.get(name) || name);
  };
})();\n`;
    break;
  case "php":
    output = `<?php

return [
${values
  .map(([name]) => name)
  .sort()
  .map((name) => `    '${name}' => true,`)
  .join("\n")}

    /* Aliases */
${aliases
  .map(([alias]) => alias)
  .sort()
  .map((name) => `    '${name}' => true,`)
  .join("\n")}
];\n`;
    break;
  default:
    throw new Error("Invalid output format");
}

process.stdout.write(output);

type MetadataIcons = {
  [key: string]: MetadataIcon;
};

type MetadataIconAliases = {
  names?: string[];
};

type MetadataIcon = {
  aliases?: MetadataIconAliases;
  styles: string[];
  unicode: string;
};

type Codepoint = string;
type HasRegularVariant = boolean;

type IconAlternateName = string;
type IconName = string;
type IconAlias = [IconAlternateName, IconName];

type IconData = [string, IconMetadata];

type IconMetadata = [Codepoint, HasRegularVariant];
