/**
 * An image cropper that allows the user to crop an image before uploading it.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */

import ImageResizer from "WoltLabSuite/Core/Image/Resizer";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import Cropper, { CropperCanvas, CropperImage, CropperSelection } from "cropperjs";
import type { Selection } from "@cropper/element-selection";
import { getPhrase } from "WoltLabSuite/Core/Language";
import WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import * as ExifUtil from "WoltLabSuite/Core/Image/ExifUtil";
import ExifReader from "exifreader";
import DomUtil from "WoltLabSuite/Core/Dom/Util";

export interface CropperConfiguration {
  aspectRatio: number;
  type: "minMax" | "exact";
  sizes: {
    width: number;
    height: number;
  }[];
}

function inSelection(selection: Selection, maxSelection: Selection): boolean {
  return (
    Math.round(selection.x) >= maxSelection.x &&
    Math.round(selection.y) >= maxSelection.y &&
    Math.round(selection.x + selection.width) <= Math.round(maxSelection.x + maxSelection.width) &&
    Math.round(selection.y + selection.height) <= Math.round(maxSelection.y + maxSelection.height)
  );
}

function clampValue(position: number, length: number, availableLength: number): number {
  if (position < 0) {
    return 0;
  }

  if (position + length > availableLength) {
    return Math.floor(availableLength - length);
  }

  return Math.floor(position);
}

abstract class ImageCropper {
  readonly configuration: CropperConfiguration;
  readonly file: File;
  readonly element: WoltlabCoreFileUploadElement;
  readonly resizer: ImageResizer;
  protected image?: HTMLImageElement | HTMLCanvasElement;
  protected cropperCanvas?: CropperCanvas | null;
  protected cropperImage?: CropperImage | null;
  protected cropperSelection?: CropperSelection | null;
  protected dialog?: WoltlabCoreDialogElement;
  protected exif?: ExifUtil.Exif;
  protected orientation?: number;
  protected cropperCanvasRect?: DOMRect;
  #cropper?: Cropper;

  constructor(element: WoltlabCoreFileUploadElement, file: File, configuration: CropperConfiguration) {
    this.configuration = configuration;
    this.element = element;
    this.file = file;
    this.resizer = new ImageResizer();
  }

  protected get width() {
    switch (this.orientation) {
      case 90:
      case 270:
        return this.image!.height;
      default:
        return this.image!.width;
    }
  }

  protected get height() {
    switch (this.orientation) {
      case 90:
      case 270:
        return this.image!.width;
      default:
        return this.image!.height;
    }
  }

  abstract get minSize(): { width: number; height: number };

  abstract get maxSize(): { width: number; height: number };

  public async loadImage() {
    const { image, exif } = await this.resizer.loadFile(this.file);
    this.image = image;
    this.exif = exif;
    const tags = await ExifReader.load(this.file);
    if (tags.Orientation) {
      switch (tags.Orientation.value) {
        case 3:
          this.orientation = 180;
          break;
        case 6:
          this.orientation = 90;
          break;
        case 8:
          this.orientation = 270;
          break;
        // Any other rotation is unsupported.
      }
    }
  }

  public async showDialog(): Promise<File> {
    this.dialog = dialogFactory().fromElement(this.image!).asPrompt({
      extra: this.getDialogExtra(),
    });
    this.dialog.show(getPhrase("wcf.upload.crop.image"));

    this.createCropper();

    const resize = () => {
      this.centerSelection();
    };

    window.addEventListener("resize", resize, { passive: true });

    return new Promise<File>((resolve, reject) => {
      let callReject = true;
      this.dialog!.addEventListener("afterClose", () => {
        window.removeEventListener("resize", resize);

        // If the dialog is closed without confirming, reject the promise to trigger a cancel event.
        if (callReject) {
          reject();
        }
      });

      this.dialog!.addEventListener("primary", () => {
        callReject = false;

        void this.getCanvas()
          .then((canvas) => {
            this.resizer
              .saveFile(
                { exif: this.orientation ? undefined : this.exif, image: canvas },
                this.file.name,
                this.file.type,
              )
              .then((resizedFile) => {
                resolve(resizedFile);
              })
              .catch(() => {
                reject();
              });
          })
          .catch(() => {
            reject();
          });
      });
    });
  }

  protected getDialogExtra(): string | undefined {
    return undefined;
  }

  protected getCanvas(): Promise<HTMLCanvasElement> {
    // Calculate the size of the image in relation to the window size
    const selectionRatio = Math.min(
      this.cropperCanvasRect!.width / this.width,
      this.cropperCanvasRect!.height / this.height,
    );
    const width = this.cropperSelection!.width / selectionRatio;
    const height = width / this.configuration.aspectRatio;

    return this.cropperSelection!.$toCanvas({
      width: Math.max(Math.min(Math.round(width), this.maxSize.width), this.minSize.width),
      height: Math.max(Math.min(Math.round(height), this.maxSize.height), this.minSize.height),
    });
  }

  protected createCropper() {
    this.#cropper = new Cropper(this.image!, {
      template: this.getCropperTemplate(),
    });

    this.cropperCanvas = this.#cropper.getCropperCanvas();
    this.cropperImage = this.#cropper.getCropperImage();
    this.cropperSelection = this.#cropper.getCropperSelection();

    this.setCropperStyle();

    if (this.orientation) {
      this.cropperImage!.$rotate(`${this.orientation}deg`);
    }

    this.centerSelection();

    this.cropperSelection!.addEventListener("change", (event: CustomEvent) => {
      const selection = event.detail as Selection;
      this.cropperCanvasRect = this.cropperCanvas!.getBoundingClientRect();

      // Calculate the scaling ratio because the lower and upper boundary are
      // defined in absolute values but the cropper will implicitly scale to fit
      // onto the available screen space. The cropper will preserve the aspect
      // ratio thus we only need to check the width.
      const scalingRatio = this.cropperCanvasRect.width / this.width;

      // Enforce the upper and lower boundary of the dimensions. Any changes to
      // the position are also ignored at this point because we support
      // adjustments to the positions only as long as the dimensions are not
      // violated in either direction.
      const minWidth = this.minSize.width * scalingRatio;
      const maxWidth = Math.floor(Math.min(this.maxSize.width * scalingRatio, this.cropperCanvasRect.width));
      const minHeight = this.minSize.height * scalingRatio;
      const maxHeight = Math.floor(Math.min(this.maxSize.height * scalingRatio, this.cropperCanvasRect.height));
      if (
        selection.width < minWidth ||
        selection.width > maxWidth ||
        selection.height < minHeight ||
        selection.height > maxHeight
      ) {
        event.preventDefault();

        return;
      }

      // Check if the position of the selection violates any of the boundaries
      // and move it accordingly.
      // See https://fengyuanchen.github.io/cropperjs/v2/api/cropper-selection.html#limit-boundaries
      const maxSelection: Selection = {
        x: 0,
        y: 0,
        width: this.cropperCanvasRect.width,
        height: this.cropperCanvasRect.height,
      };
      if (!inSelection(selection, maxSelection)) {
        event.preventDefault();

        // Clamp the position to the boundaries of the canvas.
        void this.cropperSelection!.$nextTick(() => {
          this.cropperSelection!.$change(
            clampValue(selection.x, selection.width, maxSelection.width),
            clampValue(selection.y, selection.height, maxSelection.height),
          );
        });
      }
    });
  }

  protected setCropperStyle() {
    this.cropperSelection!.aspectRatio = this.configuration.aspectRatio;
  }

  protected centerSelection(): void {
    // Set to the maximum size
    this.cropperCanvas!.style.width = `${this.width}px`;
    this.cropperCanvas!.style.height = `${this.height}px`;

    const dimension = DomUtil.innerDimensions(this.cropperCanvas!.parentElement!);
    const ratio = Math.min(dimension.width / this.width, dimension.height / this.height);
    const imageRatio = this.width / this.height;

    const canvasHeight = Math.round(this.height * ratio);
    const canvasWidth = Math.round(canvasHeight * imageRatio);

    this.cropperCanvas!.style.height = `${canvasHeight}px`;
    this.cropperCanvas!.style.width = `${canvasWidth}px`;

    this.cropperImage!.$center("cover");
    this.cropperCanvasRect = this.cropperImage!.getBoundingClientRect();

    const selectionRatio = Math.min(
      this.cropperCanvasRect.width / this.maxSize.width,
      this.cropperCanvasRect.height / this.maxSize.height,
    );

    let selectionHeight = Math.min(this.cropperCanvasRect.height, Math.floor(this.maxSize.height * selectionRatio));
    let selectionWidth = Math.floor(selectionHeight * this.configuration.aspectRatio);

    if (selectionWidth > this.cropperCanvasRect.width) {
      selectionWidth = Math.floor(this.cropperCanvasRect.width);
      selectionHeight = Math.floor(selectionWidth / this.configuration.aspectRatio);
    }

    this.cropperSelection!.$change(0, 0, selectionWidth, selectionHeight, this.configuration.aspectRatio, true);

    this.cropperSelection!.$center();
    this.cropperSelection!.scrollIntoView({ block: "center", inline: "center" });
  }

  protected getCropperTemplate(): string {
    return `<cropper-canvas background scale-step="0.0">
  <cropper-image skewable scalable translatable rotatable></cropper-image>
  <cropper-shade hidden></cropper-shade>
  <cropper-handle action="scale" hidden disabled></cropper-handle>
  <cropper-selection movable resizable outlined>
    <cropper-grid role="grid" bordered covered></cropper-grid>
    <cropper-crosshair centered></cropper-crosshair>
    <cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>
    <cropper-handle action="n-resize"></cropper-handle>
    <cropper-handle action="e-resize"></cropper-handle>
    <cropper-handle action="s-resize"></cropper-handle>
    <cropper-handle action="w-resize"></cropper-handle>
    <cropper-handle action="ne-resize"></cropper-handle>
    <cropper-handle action="nw-resize"></cropper-handle>
    <cropper-handle action="se-resize"></cropper-handle>
    <cropper-handle action="sw-resize"></cropper-handle>
  </cropper-selection>
</cropper-canvas>`;
  }
}

class ExactImageCropper extends ImageCropper {
  get minSize() {
    return this.configuration.sizes[0];
  }

  get maxSize() {
    return this.configuration.sizes[this.configuration.sizes.length - 1];
  }

  public async showDialog(): Promise<File> {
    // The image already has the correct size, cropping is not necessary
    if (
      this.configuration.sizes.filter((size) => {
        return size.width == this.width && size.height == this.height;
      }).length > 0 &&
      this.image instanceof HTMLCanvasElement
    ) {
      return this.resizer.saveFile(
        { exif: this.orientation ? undefined : this.exif, image: this.image },
        this.file.name,
        this.file.type,
      );
    }

    return super.showDialog();
  }

  protected getCanvas(): Promise<HTMLCanvasElement> {
    // Calculate the size of the image in relation to the window size
    const selectionRatio = Math.min(
      this.cropperCanvasRect!.width / this.width,
      this.cropperCanvasRect!.height / this.height,
    );
    const width = this.cropperSelection!.width / selectionRatio;
    const height = width / this.configuration.aspectRatio;

    const sizes = this.configuration.sizes
      .filter((size) => {
        return width >= size.width && height >= size.height;
      })
      .reverse();

    const size = sizes.length > 0 ? sizes[0] : this.minSize;

    return this.cropperSelection!.$toCanvas({
      width: size.width,
      height: size.height,
    });
  }

  public async loadImage(): Promise<void> {
    await super.loadImage();

    const sizes = this.configuration.sizes
      .filter((size) => {
        return size.width <= this.width && size.height <= this.height;
      })
      .sort((a, b) => {
        if (this.configuration.aspectRatio >= 1) {
          return a.width - b.width;
        } else {
          return a.height - b.height;
        }
      });

    if (sizes.length === 0) {
      const smallestSize =
        this.configuration.sizes.length > 1 ? this.configuration.sizes[this.configuration.sizes.length - 1] : undefined;
      throw new Error(
        getPhrase("wcf.upload.error.image.tooSmall", {
          width: smallestSize?.width,
          height: smallestSize?.height,
        }),
      );
    }

    this.configuration.sizes = sizes;
  }
}

class MinMaxImageCropper extends ImageCropper {
  constructor(element: WoltlabCoreFileUploadElement, file: File, configuration: CropperConfiguration) {
    super(element, file, configuration);
    if (configuration.sizes.length !== 2) {
      throw new Error("MinMaxImageCropper requires exactly two sizes");
    }
  }

  get minSize() {
    return this.configuration.sizes[0];
  }

  get maxSize() {
    return this.configuration.sizes[1];
  }

  protected getDialogExtra(): string {
    return getPhrase("wcf.global.button.reset");
  }

  public async loadImage(): Promise<void> {
    await super.loadImage();

    if (this.width < this.minSize.width || this.height < this.minSize.height) {
      throw new Error(
        getPhrase("wcf.upload.error.image.tooSmall", {
          width: this.minSize.width,
          height: this.minSize.height,
        }),
      );
    }
  }

  protected createCropper() {
    super.createCropper();

    this.dialog!.addEventListener("extra", () => {
      this.centerSelection();
    });
  }
}

export async function cropImage(
  element: WoltlabCoreFileUploadElement,
  file: File,
  configuration: CropperConfiguration,
): Promise<File> {
  switch (file.type) {
    case "image/jpeg":
    case "image/png":
    case "image/webp":
      // Potential candidate for a resize operation.
      break;

    default:
      // Not an image or an unsupported file type.
      return file;
  }

  let imageCropper: ImageCropper;
  switch (configuration.type) {
    case "exact":
      imageCropper = new ExactImageCropper(element, file, configuration);
      break;
    case "minMax":
      imageCropper = new MinMaxImageCropper(element, file, configuration);
      break;
    default:
      throw new Error("Invalid configuration type");
  }

  await imageCropper.loadImage();
  return imageCropper.showDialog();
}
