import type { ClassicEditor, EditorConfig } from "WoltLabSuite/Core/Component/Ckeditor/Types";

declare module "ckeditor5-bundle" {
  function create(element: HTMLElement, configuration: EditorConfig): Promise<ClassicEditor>;
}
