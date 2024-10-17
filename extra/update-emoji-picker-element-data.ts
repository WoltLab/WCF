import * as fs from "fs";
import { promisify } from "util";
import * as path from "path";
import { I18n } from "emoji-picker-element/shared";

const copyFile = promisify(fs.copyFile);
const writeFile = promisify(fs.writeFile);

if (process.argv.length !== 4) {
  throw new Error(
    "Expects the path to the directory in which the emoji data is saved as the #1 argument and the path to the Localisation.ts as the #2 argument.",
  );
}

const repository = process.argv[2];
if (!fs.existsSync(repository)) {
  throw new Error(`The path '${repository}' does not exist.`);
}

const localisation = process.argv[3];
if (!fs.existsSync(localisation)) {
  throw new Error(`The path '${localisation}' does not exist.`);
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

(async () => {
  const en = await import("emoji-picker-element/i18n/en");
  let importedLanguages = new Map<
    string,
    {
      default: I18n;
    }
  >();
  for (const language of languages.filter((language) => language !== "en")) {
    try {
      importedLanguages.set(language, await import(`emoji-picker-element/i18n/${language}`));
    } catch {
      // localizations not found, will be fallback to en
    }
  }

  let localisationContent = `import { I18n } from "emoji-picker-element/shared";

export function getLocalizationData(localization: string): I18n {
  switch (localization) {
    ${Array.from(importedLanguages.entries())
      .map(([languageCode, language]) => {
        return `case "${languageCode}":
      return ${JSON.stringify(language.default)};`;
      })
      .join("\n    ")}
    default:
      return ${JSON.stringify(en.default)};
  }
}
`;

  for (const language of languages) {
    await copyFile(
      path.join(__dirname, `node_modules/emoji-picker-element-data/${language}/cldr-native/data.json`),
      path.join(repository, `${language}.json`),
    );
  }

  await writeFile(localisation, localisationContent);
})();
