/**
 * This module allows resizing and conversion of HTMLImageElements to Blob and File objects
 *
 * @author  Tim Duesterhus, Maximilian Mader
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import * as FileUtil from "../FileUtil";
import * as ExifUtil from "./ExifUtil";
import Pica from "pica";

const pica = new Pica({ features: ["js", "wasm", "ww"] });

const DEFAULT_WIDTH = 800;
const DEFAULT_HEIGHT = 600;
const DEFAULT_QUALITY = 0.8;
const DEFAULT_FILETYPE = "image/jpeg";

class ImageResizer {
  maxWidth = DEFAULT_WIDTH;
  maxHeight = DEFAULT_HEIGHT;
  quality = DEFAULT_QUALITY;
  fileType = DEFAULT_FILETYPE;

  /**
   * Sets the default maximum width for this instance
   */
  setMaxWidth(value: number): ImageResizer {
    if (value == null) {
      value = DEFAULT_WIDTH;
    }

    this.maxWidth = value;
    return this;
  }

  /**
   * Sets the default maximum height for this instance
   */
  setMaxHeight(value: number): ImageResizer {
    if (value == null) {
      value = DEFAULT_HEIGHT;
    }

    this.maxHeight = value;
    return this;
  }

  /**
   * Sets the default quality for this instance
   */
  setQuality(value: number): ImageResizer {
    if (value == null) {
      value = DEFAULT_QUALITY;
    }

    this.quality = value;
    return this;
  }

  /**
   * Sets the default file type for this instance
   */
  setFileType(value: string): ImageResizer {
    if (value == null) {
      value = DEFAULT_FILETYPE;
    }

    this.fileType = value;
    return this;
  }

  /**
   * Converts the given object of exif data and image data into a File.
   */
  async saveFile(
    data: CanvasPlusExif,
    fileName: string,
    fileType: string = this.fileType,
    quality: number = this.quality,
  ): Promise<File> {
    const basename = /(.+)(\..+?)$/.exec(fileName);

    let blob = await pica.toBlob(data.image, fileType, quality);

    if (fileType === "image/jpeg" && typeof data.exif !== "undefined") {
      blob = await ExifUtil.setExifData(blob, data.exif);
    }

    return FileUtil.blobToFile(blob, basename![1]);
  }

  /**
   * Loads the given file into an image object and parses Exif information.
   */
  async loadFile(file: File): Promise<ImagePlusExif> {
    let exifBytes: Promise<ExifUtil.Exif | undefined> = Promise.resolve(undefined);

    let fileData: Blob | File = file;
    if (file.type === "image/jpeg") {
      // Extract EXIF data
      exifBytes = ExifUtil.getExifBytesFromJpeg(file);

      // Strip EXIF data
      fileData = await ExifUtil.removeExifData(fileData);
    }

    const imageLoader: Promise<HTMLImageElement> = new Promise((resolve, reject) => {
      const reader = new FileReader();
      const image = new Image();

      reader.addEventListener("load", () => {
        image.src = reader.result! as string;
      });

      reader.addEventListener("error", () => {
        reader.abort();
        if (reader.error) {
          reject(reader.error);
        } else {
          reject();
        }
      });

      image.addEventListener("error", reject);

      image.addEventListener("load", () => {
        resolve(image);
      });

      reader.readAsDataURL(fileData);
    });

    const [exif, image] = await Promise.all([exifBytes, imageLoader]);

    return { exif, image };
  }

  /**
   * Downscales an image given as File object.
   */
  async resize(
    image: HTMLImageElement,
    maxWidth: number = this.maxWidth,
    maxHeight: number = this.maxHeight,
    quality: number = this.quality,
    force = false,
    cancelPromise?: Promise<unknown>,
  ): Promise<HTMLCanvasElement | undefined> {
    const canvas = document.createElement("canvas");

    if (window.createImageBitmap as any) {
      const bitmap = await createImageBitmap(image);

      if (bitmap.height != image.height) {
        throw new Error("Chrome Bug #1069965");
      }
    }

    // Prevent upscaling
    const newWidth = Math.min(maxWidth, image.width);
    const newHeight = Math.min(maxHeight, image.height);

    if (image.width <= newWidth && image.height <= newHeight && !force) {
      return undefined;
    }

    // Keep image ratio
    const ratio = Math.min(newWidth / image.width, newHeight / image.height);

    canvas.width = Math.floor(image.width * ratio);
    canvas.height = Math.floor(image.height * ratio);

    // Map to Pica's quality
    let resizeQuality = 1;
    if (quality >= 0.8) {
      resizeQuality = 3;
    } else if (quality >= 0.4) {
      resizeQuality = 2;
    }

    const options = {
      quality: resizeQuality,
      cancelToken: cancelPromise,
      alpha: true,
    };

    return pica.resize(image, canvas, options);
  }
}

interface ImagePlusExif {
  image: HTMLImageElement;
  exif?: ExifUtil.Exif;
}

interface CanvasPlusExif {
  image: HTMLCanvasElement;
  exif?: ExifUtil.Exif;
}

export = ImageResizer;
