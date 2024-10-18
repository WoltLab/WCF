import * as fs from "fs";
import { promisify } from "util";
import * as path from "path";
import { I18n } from "emoji-picker-element/shared";
import de from "emoji-picker-element/i18n/de";
import en from "emoji-picker-element/i18n/en";
import es from "emoji-picker-element/i18n/es";
import fr from "emoji-picker-element/i18n/fr";
import it from "emoji-picker-element/i18n/it";
import nl from "emoji-picker-element/i18n/nl";
import pl from "emoji-picker-element/i18n/pl";
import pt_PT from "emoji-picker-element/i18n/pt_PT";
import ru_RU from "emoji-picker-element/i18n/ru_RU";

const copyFile = promisify(fs.copyFile);
const writeFile = promisify(fs.writeFile);
const rm = promisify(fs.rm);

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

const languages: LanguageItem[] = [
  { local: "da" },
  { local: "nl", i18n: nl },
  { local: "en", i18n: en },
  { local: "en-gb" },
  { local: "et" },
  { local: "fi" },
  { local: "fr", i18n: fr },
  { local: "de", i18n: de },
  { local: "hu" },
  { local: "it", i18n: it },
  { local: "lt" },
  { local: "nb" },
  { local: "pl", i18n: pl },
  { local: "pt", i18n: pt_PT },
  { local: "ru", i18n: ru_RU },
  { local: "es", i18n: es },
  { local: "sv" },
  { local: "uk" },
];

(async () => {
  let localisationContent = `import { I18n } from "emoji-picker-element/shared";

export function getLocalizationData(localization: string): I18n {
  if (localization.includes("-")) {
    localization = localization.split("-")[0];
  }

  switch (localization) {
    ${languages
      .filter((item) => {
        return item.local !== "en";
      })
      .filter((language) => language.i18n)
      .map((item) => {
        return `case "${item.local}":
      // prettier-ignore
      return ${JSON.stringify(item.i18n)};`;
      })
      .join("\n    ")}
    default:
      // prettier-ignore
      return ${JSON.stringify(en)};
  }
}
`;

  fs.readdirSync(repository)
    .filter((file) => {
      return file.endsWith(".json");
    })
    .forEach((file) => {
      rm(path.join(repository, file));
    });

  for (const language of languages) {
    await copyFile(
      path.join(__dirname, `node_modules/emoji-picker-element-data/${language.local}/cldr-native/data.json`),
      path.join(repository, `${language.local}.json`),
    );
  }

  await writeFile(localisation, localisationContent);
})();

interface LanguageItem {
  local: string;
  i18n?: I18n;
}
