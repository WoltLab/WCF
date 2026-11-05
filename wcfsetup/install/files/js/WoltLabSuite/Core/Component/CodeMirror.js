/**
 * Replaces a `<textarea>` with a code editor based on CodeMirror 6. The
 * textarea stays in the form and receives every change.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "@woltlab/codemirror", "../Dom/Util"], function (require, exports, tslib_1, codemirror_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setupCodeMirror = setupCodeMirror;
    exports.getCodeMirror = getCodeMirror;
    exports.insertText = insertText;
    Util_1 = tslib_1.__importDefault(Util_1);
    const editors = new WeakMap();
    // The colors are defined in `style/ui/codeMirror.scss` to follow the color
    // scheme without reconfiguring the editors.
    const highlighter = (0, codemirror_1.tagHighlighter)([
        { tag: [codemirror_1.tags.comment, codemirror_1.tags.meta, codemirror_1.tags.processingInstruction], class: "codeMirrorToken--comment" },
        { tag: codemirror_1.tags.punctuation, class: "codeMirrorToken--punctuation" },
        {
            tag: [codemirror_1.tags.tagName, codemirror_1.tags.propertyName, codemirror_1.tags.deleted, codemirror_1.tags.constant(codemirror_1.tags.variableName)],
            class: "codeMirrorToken--property",
        },
        { tag: [codemirror_1.tags.number, codemirror_1.tags.bool, codemirror_1.tags.null, codemirror_1.tags.atom, codemirror_1.tags.unit, codemirror_1.tags.color], class: "codeMirrorToken--number" },
        { tag: [codemirror_1.tags.string, codemirror_1.tags.character, codemirror_1.tags.attributeName, codemirror_1.tags.inserted], class: "codeMirrorToken--string" },
        { tag: [codemirror_1.tags.operator, codemirror_1.tags.url, codemirror_1.tags.escape], class: "codeMirrorToken--operator" },
        { tag: [codemirror_1.tags.keyword, codemirror_1.tags.modifier], class: "codeMirrorToken--keyword" },
        { tag: codemirror_1.tags.attributeValue, class: "codeMirrorToken--attributeValue" },
        {
            tag: [
                codemirror_1.tags.function(codemirror_1.tags.variableName),
                codemirror_1.tags.function(codemirror_1.tags.propertyName),
                codemirror_1.tags.className,
                codemirror_1.tags.typeName,
                codemirror_1.tags.macroName,
            ],
            class: "codeMirrorToken--function",
        },
        { tag: codemirror_1.tags.special(codemirror_1.tags.variableName), class: "codeMirrorToken--variable" },
        { tag: [codemirror_1.tags.regexp, codemirror_1.tags.special(codemirror_1.tags.string)], class: "codeMirrorToken--regexp" },
        { tag: [codemirror_1.tags.heading, codemirror_1.tags.strong], class: "codeMirrorToken--strong" },
        { tag: codemirror_1.tags.emphasis, class: "codeMirrorToken--emphasis" },
        { tag: codemirror_1.tags.link, class: "codeMirrorToken--link" },
        { tag: codemirror_1.tags.strikethrough, class: "codeMirrorToken--strikethrough" },
        { tag: codemirror_1.tags.invalid, class: "codeMirrorToken--invalid" },
    ], { all: "codeMirrorToken" });
    const theme = codemirror_1.EditorView.theme({
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
        "&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground, .cm-selectionBackground, .cm-content ::selection": {
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
    function findScrollOffsetInput(textarea) {
        let sibling = textarea.nextElementSibling;
        while (sibling !== null) {
            if (sibling instanceof HTMLInputElement && sibling.classList.contains("codeMirrorScrollOffset")) {
                return sibling;
            }
            sibling = sibling.nextElementSibling;
        }
        return undefined;
    }
    function findLabel(textarea) {
        if (textarea.id === "") {
            return null;
        }
        return document.querySelector(`label[for="${CSS.escape(textarea.id)}"]`);
    }
    /**
     * The scroll position is restored once the editor becomes visible, because a
     * hidden editor (e.g. in an inactive tab) cannot scroll.
     */
    function trackScrollOffset(view, input) {
        const offset = parseInt(input.value, 10);
        view.scrollDOM.addEventListener("scroll", () => {
            input.value = String(Math.round(view.scrollDOM.scrollTop));
        }, { passive: true });
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
    function setupCodeMirror(textarea, options = {}) {
        const existingView = editors.get(textarea);
        if (existingView !== undefined) {
            return existingView;
        }
        const language = new codemirror_1.Compartment();
        const readOnly = options.readOnly === true || textarea.disabled || textarea.readOnly;
        const label = findLabel(textarea);
        const view = new codemirror_1.EditorView({
            doc: textarea.value,
            extensions: [
                (0, codemirror_1.lineNumbers)(),
                (0, codemirror_1.highlightActiveLineGutter)(),
                (0, codemirror_1.highlightSpecialChars)(),
                (0, codemirror_1.history)(),
                (0, codemirror_1.foldGutter)(),
                (0, codemirror_1.drawSelection)(),
                (0, codemirror_1.dropCursor)(),
                codemirror_1.EditorState.allowMultipleSelections.of(true),
                (0, codemirror_1.indentOnInput)(),
                (0, codemirror_1.bracketMatching)(),
                (0, codemirror_1.rectangularSelection)(),
                (0, codemirror_1.crosshairCursor)(),
                (0, codemirror_1.highlightActiveLine)(),
                (0, codemirror_1.highlightSelectionMatches)(),
                codemirror_1.keymap.of([...codemirror_1.defaultKeymap, ...codemirror_1.searchKeymap, ...codemirror_1.historyKeymap, ...codemirror_1.foldKeymap, codemirror_1.indentWithTab]),
                codemirror_1.EditorState.tabSize.of(4),
                codemirror_1.indentUnit.of("\t"),
                codemirror_1.EditorView.lineWrapping,
                codemirror_1.EditorState.readOnly.of(readOnly),
                codemirror_1.EditorView.editorAttributes.of({ dir: "ltr" }),
                label !== null ? codemirror_1.EditorView.contentAttributes.of({ "aria-labelledby": Util_1.default.identify(label) }) : [],
                (0, codemirror_1.syntaxHighlighting)(highlighter),
                theme,
                language.of([]),
                codemirror_1.EditorView.updateListener.of((update) => {
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
            void (0, codemirror_1.loadLanguage)(options.language).then((languageSupport) => {
                if (languageSupport !== undefined) {
                    view.dispatch({ effects: language.reconfigure(languageSupport) });
                }
            });
        }
        return view;
    }
    function getCodeMirror(textarea) {
        return editors.get(textarea);
    }
    /**
     * Replaces the current selection, or inserts at the cursor position.
     */
    function insertText(textarea, text) {
        const view = editors.get(textarea);
        if (view === undefined) {
            throw new Error(`The textarea '${textarea.id}' has no code editor.`);
        }
        view.dispatch(view.state.replaceSelection(text), { scrollIntoView: true });
        view.focus();
    }
});
