import * as fs from "fs";

// This script expects you to write the output to
// wcfsetup/install/files/js/WoltLabSuite/WebComponent/fa-metadata.js

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
Object.entries(json).forEach(([name, icon]) => {
  const codepoint = String.fromCharCode(parseInt(icon.unicode, 16));
  values.push([name, [codepoint, icon.styles]]);
});

const output = `(() => {
  const styles = new Map(
    ${JSON.stringify(values)}
  );

  window.getFontAwesome6IconMetadata = (name) => styles.get(name);
})();`;

process.stdout.write(output);

type IconStyles = string[];

type MetadataIcons = {
  [key: string]: MetadataIcon;
};

type MetadataIcon = {
  styles: IconStyles;
  unicode: string;
};

type Codepoint = string;

type IconData = [string, IconMetadata];

type IconMetadata = [Codepoint, IconStyles];
