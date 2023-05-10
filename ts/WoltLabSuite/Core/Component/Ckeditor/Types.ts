/**
 * Collects imports for core CKEditor types in a central location.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

export type { ClassicEditor } from "@ckeditor/ckeditor5-editor-classic";
export type { CodeBlockConfig } from "@ckeditor/ckeditor5-code-block";
export type { EditorConfig } from "@ckeditor/ckeditor5-core";
export type { Element } from "@ckeditor/ckeditor5-engine";

// We only want these for the configuration augmentation.
// `import type "foo"` is not valid syntax, but simply importing
// nothing works properly. The augmentation will happen, but
// no symbols will be emitted.
import type {} from "@ckeditor/ckeditor5-autosave";
import type {} from "@ckeditor/ckeditor5-mention";
