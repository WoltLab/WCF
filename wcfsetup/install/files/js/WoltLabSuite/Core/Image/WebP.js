/**
 * Provides helper functions to decode a WebP image.
 *
 * @author    Alexander Ebert
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.parseWebPFromBuffer = parseWebPFromBuffer;
    function decodeHeader(uint32BE) {
        switch (uint32BE) {
            case 0x414c5048:
                return "ALPH" /* ChunkHeader.ALPH */;
            case 0x414e494d:
                return "ANIM" /* ChunkHeader.ANIM */;
            case 0x414e4d46:
                return "ANMF" /* ChunkHeader.ANMF */;
            case 0x45584946:
                return "EXIF" /* ChunkHeader.EXIF */;
            case 0x49434350:
                return "ICCP" /* ChunkHeader.ICCP */;
            case 0x52494646:
                return "RIFF" /* ChunkHeader.RIFF */;
            case 0x56503820:
                return "VP8 " /* ChunkHeader.VP8 */;
            case 0x5650384c:
                return "VP8L" /* ChunkHeader.VP8L */;
            case 0x56503858:
                return "VP8X" /* ChunkHeader.VP8X */;
            case 0x57454250:
                return "WEBP" /* ChunkHeader.WEBP */;
            case 0x584d5020:
                return "XMP " /* ChunkHeader.XMP */;
            default:
                return uint32BE;
        }
    }
    class WebP {
        #buffer;
        #chunks;
        #height;
        #width;
        constructor(buffer, width, height, chunks) {
            this.#buffer = buffer;
            this.#chunks = chunks;
            this.#height = height;
            this.#width = width;
        }
        getExifData() {
            for (const [chunkHeader, offset, chunkSize] of this.#chunks) {
                if (chunkHeader === "EXIF" /* ChunkHeader.EXIF */) {
                    return new Uint8Array(this.#buffer.slice(offset, offset + chunkSize));
                }
            }
            return null;
        }
        get height() {
            return this.#height;
        }
        get width() {
            return this.#width;
        }
    }
    function parseVp8x(buffer, dataView) {
        if (dataView.byteLength <= 30) {
            throw new Error("A VP8X encoded WebP must be larger than 30 bytes.");
        }
        // If we reach this point, then we have already consumed the first 20 bytes of
        // the buffer. (offset = 20)
        // The next 8 bits contain the flags. (offset + 1 = 21)
        // The next 24 bits are reserved. (offset + 3 = 24)
        // The next 48 bits contain the width and height, represented as uint24LE, but
        // using the value - 1, thus we need to add 1 to each calculated value.
        const width = ((dataView.getUint8(26) << 16) | (dataView.getUint8(25) << 8) | dataView.getUint8(24)) + 1;
        const height = ((dataView.getUint8(29) << 16) | (dataView.getUint8(28) << 8) | dataView.getUint8(27)) + 1;
        const chunks = [];
        let offset = 30;
        while (offset < dataView.byteLength) {
            const chunkHeader = decodeHeader(dataView.getUint32(offset));
            const chunkSize = dataView.getUint32(offset + 4, true);
            offset += 8;
            chunks.push([chunkHeader, offset, chunkSize]);
            offset += chunkSize;
            if (chunkSize % 2 === 1) {
                // "If Chunk Size is odd, a single padding byte -- which MUST be 0 to
                // conform with RIFF -- is added."
                offset += 1;
            }
            if (offset > dataView.byteLength) {
                const header = typeof chunkHeader === "number" ? `0x${chunkHeader.toString(16)}` : chunkHeader;
                throw new Error(`Corrupted image detected, offset ${offset} > ${dataView.byteLength} for chunk ${header}.`);
            }
        }
        return new WebP(buffer, width, height, chunks);
    }
    function getDimensions(buffer) {
        // This is the lazy version that avoids having to implement an RFC 6386 parser
        // to extract the dimensions from the VP8/VP8L frames.
        const blob = new Blob([new Uint8Array(buffer)], { type: "image/webp" });
        const img = new Image();
        img.src = window.URL.createObjectURL(blob);
        return [img.naturalWidth, img.naturalHeight];
    }
    function parseWebPFromBuffer(buffer) {
        const dataView = new DataView(buffer, 0, buffer.byteLength);
        if (dataView.byteLength < 20) {
            // Anything below 20 bytes cannot be an WebP image. The first 12 bytes are
            // the RIFF header followed by at least 8 bytes for a chunk plus its size.
            return undefined;
        }
        if (decodeHeader(dataView.getUint32(0)) !== "RIFF" /* ChunkHeader.RIFF */) {
            return undefined;
        }
        // The next 4 bytes represent the total size of the file.
        if (decodeHeader(dataView.getUint32(8)) !== "WEBP" /* ChunkHeader.WEBP */) {
            return undefined;
        }
        const firstChunk = decodeHeader(dataView.getUint32(12));
        if (typeof firstChunk === "number") {
            // The first chunk must be a known value.
            throw new Error(`Unrecognized chunk 0x${firstChunk.toString(16)} at the first position`);
        }
        const chunkSize = dataView.getUint32(16, true);
        switch (firstChunk) {
            case "VP8 " /* ChunkHeader.VP8 */:
            case "VP8L" /* ChunkHeader.VP8L */: {
                const [width, height] = getDimensions(buffer);
                return new WebP(buffer, width, height, [[firstChunk, 20, chunkSize]]);
            }
            case "VP8X" /* ChunkHeader.VP8X */:
                return parseVp8x(buffer, dataView);
            default:
                throw new Error(`Unexpected chunk "${firstChunk}" at the first position`);
        }
    }
});
