import * as fs from "fs";

// This script expects you to write the output to
// ts/WoltLabSuite/WebComponent/fa-metadata.js

if (process.argv.length !== 3) {
  throw new Error("Expected the path to the `icons.json` metadata to be the only argument.");
}

const iconsJson = process.argv[2];
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
  const codepoint = String.fromCharCode(parseInt(icon.unicode, 16));
  values.push([name, [codepoint, icon.styles.includes("regular")]]);

  if (icon.aliases && Array.isArray(icon.aliases.names)) {
    icon.aliases.names.forEach((alias) => {
      aliases.push([alias, name]);
    });
  }
});

const output = `(() => {
  const aliases = new Map(
    ${JSON.stringify(aliases)}
  );
  const metadata = new Map(
    ${JSON.stringify(values)}
  );

  window.getFontAwesome6IconMetadata = (name) => {
    return metadata.get(aliases.get(name) || name);
  };
})();`;

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
