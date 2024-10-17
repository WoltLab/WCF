import * as fs from "fs";
import { promisify } from "util";
import * as path from "path";

const copyFile = promisify(fs.copyFile);

if (process.argv.length !== 3) {
  throw new Error("Expects the path to the directory in which the emoji data is saved as the only argument.");
}

const repository = process.argv[2];
if (!fs.existsSync(repository)) {
  throw new Error(`The path '${repository}' does not exist.`);
}

const languages = [
  "da",
  "nl",
  "en",
  "en-gb",
  "et",
  "fi",
  "fr",
  "de",
  "hu",
  "it",
  "lt",
  "nb",
  "pl",
  "pt",
  "ru",
  "es",
  "sv",
  "uk",
];

languages.forEach(async (language) => {
  await copyFile(
    path.join(__dirname, `node_modules/emoji-picker-element-data/${language}/cldr-native/data.json`),
    path.join(repository, `${language}.json`),
  );
});
