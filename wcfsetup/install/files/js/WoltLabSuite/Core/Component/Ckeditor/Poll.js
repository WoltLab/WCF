/**
 * Embeds standalone polls into the editor through the poll manager.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle tiny
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || (function () {
    var ownKeys = function(o) {
        ownKeys = Object.getOwnPropertyNames || function (o) {
            var ar = [];
            for (var k in o) if (Object.prototype.hasOwnProperty.call(o, k)) ar[ar.length] = k;
            return ar;
        };
        return ownKeys(o);
    };
    return function (mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k = ownKeys(mod), i = 0; i < k.length; i++) if (k[i] !== "default") __createBinding(result, mod, k[i]);
        __setModuleDefault(result, mod);
        return result;
    };
})();
define(["require", "exports", "./Event", "../../Helper/PromiseMutex"], function (require, exports, Event_1, PromiseMutex_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function insertPoll(ckeditor) {
        const { openPollManager } = await new Promise((resolve_1, reject_1) => { require(["../Poll/Manager"], resolve_1, reject_1); }).then(__importStar);
        const poll = await openPollManager();
        if (poll === undefined) {
            return;
        }
        ckeditor.insertText(`[poll='${poll.pollID}'][/poll]`);
    }
    function setup(element) {
        (0, Event_1.listenToCkeditor)(element).ready(({ ckeditor }) => {
            if (!ckeditor.features.poll) {
                return;
            }
            // The poll manager is loaded asynchronously, repeated clicks must not open it twice.
            const openManager = (0, PromiseMutex_1.promiseMutex)(() => insertPoll(ckeditor));
            (0, Event_1.listenToCkeditor)(ckeditor.sourceElement).bbcode(({ bbcode }) => {
                if (bbcode !== "poll") {
                    return false;
                }
                openManager();
                return true;
            });
        });
    }
});
