import typescriptEslint from "@typescript-eslint/eslint-plugin";
import tsParser from "@typescript-eslint/parser";
import path from "node:path";
import { fileURLToPath } from "node:url";
import js from "@eslint/js";
import { FlatCompat } from "@eslint/eslintrc";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
	baseDirectory: __dirname,
	recommendedConfig: js.configs.recommended,
	allConfig: js.configs.all,
});

export default [
	{
		ignores: ["**/*.js", "**/extra", "node_modules/**/*"],
	},
	...compat.extends(
		"eslint:recommended",
		"plugin:@typescript-eslint/recommended",
		"plugin:@typescript-eslint/recommended-requiring-type-checking",
		"prettier",
	),
	{
		plugins: {
			"@typescript-eslint": typescriptEslint,
		},

		languageOptions: {
			parser: tsParser,
			ecmaVersion: 5,
			sourceType: "script",

			parserOptions: {
				tsconfigRootDir: "/opt/homebrew/var/www/wcf/WCF",
				project: ["./tsconfig.json", "./ts/WoltLabSuite/WebComponent/tsconfig.json"],
			},
		},

		rules: {
			"@typescript-eslint/ban-types": [
				"error",
				{
					types: {
						object: false,
					},

					extendDefaults: true,
				},
			],

			"@typescript-eslint/no-explicit-any": 0,
			"@typescript-eslint/no-non-null-assertion": 0,
			"@typescript-eslint/no-unsafe-argument": 0,
			"@typescript-eslint/no-unsafe-assignment": 0,
			"@typescript-eslint/no-unsafe-call": 0,
			"@typescript-eslint/no-unsafe-member-access": 0,
			"@typescript-eslint/no-unsafe-return": 0,

			"@typescript-eslint/no-unused-vars": [
				"error",
				{
					argsIgnorePattern: "^_",
					varsIgnorePattern: "^_",
				},
			],

			"@typescript-eslint/no-misused-promises": [
				"error",
				{
					checksVoidReturn: false,
				},
			],
		},
	},
];
