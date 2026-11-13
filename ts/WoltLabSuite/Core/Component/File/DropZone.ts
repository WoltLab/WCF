/**
 * Turns an element into a drop zone for a drop target, usually a
 * `<woltlab-core-file-upload>` element. Files dropped anywhere on the `area`
 * are passed to the target's `uploadFiles()` and clicking the zone calls its
 * `click()` to open the file picker.
 *
 * A caller passes its own target when the files do not go to a single upload
 * element, e.g. to route them to one of several upload elements.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

export type DropTarget = {
  click(): void;
  uploadFiles(files: File[]): void;
};

function hasFiles(event: DragEvent): boolean {
  return event.dataTransfer?.types.includes("Files") === true;
}

export function setupDropZone(zone: HTMLElement, area: HTMLElement, target: DropTarget): void {
  zone.addEventListener("click", () => {
    target.click();
  });

  let dragCounter = 0;
  const reset = () => {
    dragCounter = 0;
    area.classList.remove("fileDropZone--dragOver");
  };

  area.addEventListener("dragenter", (event) => {
    if (!hasFiles(event)) {
      return;
    }

    event.preventDefault();
    dragCounter++;
    area.classList.add("fileDropZone--dragOver");
  });
  area.addEventListener("dragover", (event) => {
    if (hasFiles(event)) {
      event.preventDefault();
    }
  });
  area.addEventListener("dragleave", (event) => {
    if (!hasFiles(event)) {
      return;
    }

    dragCounter = Math.max(0, dragCounter - 1);
    if (dragCounter === 0) {
      reset();
    }
  });
  area.addEventListener("drop", (event) => {
    if (!hasFiles(event)) {
      return;
    }

    event.preventDefault();
    reset();

    const files = Array.from(event.dataTransfer!.files);
    if (files.length > 0) {
      target.uploadFiles(files);
    }
  });
}
