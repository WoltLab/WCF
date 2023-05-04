function stripLegacySpacerParagraphs(div: HTMLElement): void {
  div.querySelectorAll("p").forEach((paragraph) => {
    if (paragraph.childElementCount === 1) {
      const child = paragraph.children[0] as HTMLElement;
      if (child.tagName === "BR" && child.dataset.ckeFiller !== "true") {
        if (paragraph.textContent!.trim() === "") {
          paragraph.remove();
        }
      }
    }
  });
}

export function normalizeLegacyMessage(element: HTMLElement): void {
  if (!(element instanceof HTMLTextAreaElement)) {
    throw new TypeError("Expected the element to be a <textarea>.");
  }

  const div = document.createElement("div");
  div.innerHTML = element.value;

  stripLegacySpacerParagraphs(div);

  element.value = div.innerHTML;
}
