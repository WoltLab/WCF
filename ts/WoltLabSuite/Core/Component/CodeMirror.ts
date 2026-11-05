/**
 * Replaces a `<textarea>` with a code editor based on CodeMirror 6. The
 * textarea stays in the form and receives every change.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import {
  bracketMatching,
  Compartment,
  crosshairCursor,
  defaultKeymap,
  drawSelection,
  dropCursor,
  EditorState,
  EditorView,
  foldGutter,
  foldKeymap,
  highlightActiveLine,
  highlightActiveLineGutter,
  highlightSelectionMatches,
  highlightSpecialChars,
  history,
  historyKeymap,
  indentOnInput,
  indentUnit,
  indentWithTab,
  keymap,
  lineNumbers,
  loadLanguage,
  rectangularSelection,
  searchKeymap,
  syntaxHighlighting,
  tagHighlighter,
  tags,
} from "@woltlab/codemirror";
import DomUtil from "../Dom/Util";

export type CodeMirrorOptions = {
  language?: string;
  readOnly?: boolean;
};

const editors = new WeakMap<HTMLTextAreaElement, EditorView>();

// The colors are defined in `style/ui/codeMirror.scss` to follow the color
// scheme without reconfiguring the editors.
const highlighter = tagHighlighter(
  [
    { tag: [tags.comment, tags.meta, tags.processingInstruction], class: "codeMirrorToken--comment" },
    { tag: tags.punctuation, class: "codeMirrorToken--punctuation" },
    {
      tag: [tags.tagName, tags.propertyName, tags.deleted, tags.constant(tags.variableName)],
      class: "codeMirrorToken--property",
    },
    { tag: [tags.number, tags.bool, tags.null, tags.atom, tags.unit, tags.color], class: "codeMirrorToken--number" },
    { tag: [tags.string, tags.character, tags.attributeName, tags.inserted], class: "codeMirrorToken--string" },
    { tag: [tags.operator, tags.url, tags.escape], class: "codeMirrorToken--operator" },
    { tag: [tags.keyword, tags.modifier], class: "codeMirrorToken--keyword" },
    { tag: tags.attributeValue, class: "codeMirrorToken--attributeValue" },
    {
      tag: [
        tags.function(tags.variableName),
        tags.function(tags.propertyName),
        tags.className,
        tags.typeName,
        tags.macroName,
      ],
      class: "codeMirrorToken--function",
    },
    { tag: tags.special(tags.variableName), class: "codeMirrorToken--variable" },
    { tag: [tags.regexp, tags.special(tags.string)], class: "codeMirrorToken--regexp" },
    { tag: [tags.heading, tags.strong], class: "codeMirrorToken--strong" },
    { tag: tags.emphasis, class: "codeMirrorToken--emphasis" },
    { tag: tags.link, class: "codeMirrorToken--link" },
    { tag: tags.strikethrough, class: "codeMirrorToken--strikethrough" },
    { tag: tags.invalid, class: "codeMirrorToken--invalid" },
  ],
  { all: "codeMirrorToken" },
);

const theme = EditorView.theme({
  "&": {
    backgroundColor: "var(--wcfInputBackground)",
    border: "1px solid var(--wcfInputBorder)",
    borderRadius: "var(--wcfBorderRadius)",
    color: "var(--wcfInputText)",
    fontSize: "14px",
    maxHeight: "1000px",
    transition: "border-color 0.12s linear, box-shadow 0.12s linear",
  },
  "&:hover": {
    borderColor: "var(--wcfInputBorderActive)",
  },
  "&.cm-focused": {
    borderColor: "var(--wcfInputBorderActive)",
    boxShadow: "0 0 0 3px rgba(var(--wcfInputBorderActive-rgb) / 0.25)",
    outline: "none",
  },
  ".cm-scroller": {
    borderRadius: "inherit",
    fontFamily: "var(--wcfFontFamilyMonospace)",
    overflow: "auto",
  },
  ".cm-content, .cm-gutter": {
    minHeight: "300px",
  },
  ".cm-gutters": {
    backgroundColor: "var(--wcfInputBackground)",
    borderRight: "1px solid var(--wcfInputBorder)",
    color: "var(--wcfInputPlaceholder)",
  },
  ".cm-activeLine": {
    backgroundColor: "rgba(var(--wcfInputText-rgb) / 0.04)",
  },
  ".cm-activeLineGutter": {
    backgroundColor: "rgba(var(--wcfInputText-rgb) / 0.08)",
    color: "var(--wcfInputText)",
  },
  ".cm-cursor, .cm-dropCursor": {
    borderLeftColor: "var(--wcfInputText)",
  },
  "&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground, .cm-selectionBackground, .cm-content ::selection":
    {
      backgroundColor: "rgba(var(--wcfInputBorderActive-rgb) / 0.25)",
    },
  ".cm-foldPlaceholder": {
    backgroundColor: "var(--wcfEditorButtonBackground)",
    border: "1px solid var(--wcfInputBorder)",
    color: "var(--wcfEditorButtonText)",
  },
  ".cm-panels": {
    backgroundColor: "var(--wcfEditorButtonBackground)",
    color: "var(--wcfEditorButtonText)",
  },
  ".cm-panels.cm-panels-top": {
    borderBottom: "1px solid var(--wcfInputBorder)",
  },
  ".cm-panels.cm-panels-bottom": {
    borderTop: "1px solid var(--wcfInputBorder)",
  },
  ".cm-panel input, .cm-panel button, .cm-panel label": {
    fontFamily: "var(--wcfFontFamily)",
    fontSize: "12px",
  },
  ".cm-textfield": {
    backgroundColor: "var(--wcfInputBackground)",
    border: "1px solid var(--wcfInputBorder)",
    borderRadius: "var(--wcfBorderRadius)",
    color: "var(--wcfInputText)",
  },
  ".cm-button": {
    backgroundColor: "var(--wcfEditorButtonBackground)",
    backgroundImage: "none",
    border: "1px solid var(--wcfInputBorder)",
    borderRadius: "var(--wcfBorderRadius)",
    color: "var(--wcfEditorButtonText)",
  },
  ".cm-button:hover": {
    backgroundColor: "var(--wcfEditorButtonBackgroundActive)",
    color: "var(--wcfEditorButtonTextActive)",
  },
  ".cm-tooltip": {
    backgroundColor: "var(--wcfInputBackground)",
    border: "1px solid var(--wcfInputBorder)",
    color: "var(--wcfInputText)",
  },
});

function findScrollOffsetInput(textarea: HTMLTextAreaElement): HTMLInputElement | undefined {
  let sibling = textarea.nextElementSibling;
  while (sibling !== null) {
    if (sibling instanceof HTMLInputElement && sibling.classList.contains("codeMirrorScrollOffset")) {
      return sibling;
    }

    sibling = sibling.nextElementSibling;
  }

  return undefined;
}

function findLabel(textarea: HTMLTextAreaElement): HTMLLabelElement | null {
  if (textarea.id === "") {
    return null;
  }

  return document.querySelector<HTMLLabelElement>(`label[for="${CSS.escape(textarea.id)}"]`);
}

/**
 * The scroll position is restored once the editor becomes visible, because a
 * hidden editor (e.g. in an inactive tab) cannot scroll.
 */
function trackScrollOffset(view: EditorView, input: HTMLInputElement): void {
  const offset = parseInt(input.value, 10);

  view.scrollDOM.addEventListener(
    "scroll",
    () => {
      input.value = String(Math.round(view.scrollDOM.scrollTop));
    },
    { passive: true },
  );

  if (Number.isNaN(offset) || offset <= 0) {
    return;
  }

  const observer = new ResizeObserver(() => {
    if (view.dom.offsetHeight === 0) {
      return;
    }

    observer.disconnect();
    view.scrollDOM.scrollTop = offset;
  });
  observer.observe(view.dom);
}

export function setupCodeMirror(textarea: HTMLTextAreaElement, options: CodeMirrorOptions = {}): EditorView {
  const existingView = editors.get(textarea);
  if (existingView !== undefined) {
    return existingView;
  }

  const language = new Compartment();
  const readOnly = options.readOnly === true || textarea.disabled || textarea.readOnly;
  const label = findLabel(textarea);

  const view = new EditorView({
    doc: textarea.value,
    extensions: [
      lineNumbers(),
      highlightActiveLineGutter(),
      highlightSpecialChars(),
      history(),
      foldGutter(),
      drawSelection(),
      dropCursor(),
      EditorState.allowMultipleSelections.of(true),
      indentOnInput(),
      bracketMatching(),
      rectangularSelection(),
      crosshairCursor(),
      highlightActiveLine(),
      highlightSelectionMatches(),
      keymap.of([...defaultKeymap, ...searchKeymap, ...historyKeymap, ...foldKeymap, indentWithTab]),
      EditorState.tabSize.of(4),
      indentUnit.of("\t"),
      EditorView.lineWrapping,
      EditorState.readOnly.of(readOnly),
      EditorView.editorAttributes.of({ dir: "ltr" }),
      label !== null ? EditorView.contentAttributes.of({ "aria-labelledby": DomUtil.identify(label) }) : [],
      syntaxHighlighting(highlighter),
      theme,
      language.of([]),
      EditorView.updateListener.of((update) => {
        if (update.docChanged) {
          textarea.value = update.state.doc.toString();
        }
      }),
    ],
  });

  textarea.hidden = true;
  textarea.insertAdjacentElement("afterend", view.dom);
  editors.set(textarea, view);

  label?.addEventListener("click", () => view.focus());

  const scrollOffsetInput = findScrollOffsetInput(textarea);
  if (scrollOffsetInput !== undefined) {
    trackScrollOffset(view, scrollOffsetInput);
  }

  if (textarea.autofocus) {
    view.focus();
  }

  if (options.language !== undefined) {
    void loadLanguage(options.language).then((languageSupport) => {
      if (languageSupport !== undefined) {
        view.dispatch({ effects: language.reconfigure(languageSupport) });
      }
    });
  }

  return view;
}

export function getCodeMirror(textarea: HTMLTextAreaElement): EditorView | undefined {
  return editors.get(textarea);
}

/**
 * Replaces the current selection, or inserts at the cursor position.
 */
export function insertText(textarea: HTMLTextAreaElement, text: string): void {
  const view = editors.get(textarea);
  if (view === undefined) {
    throw new Error(`The textarea '${textarea.id}' has no code editor.`);
  }

  view.dispatch(view.state.replaceSelection(text), { scrollIntoView: true });
  view.focus();
}
