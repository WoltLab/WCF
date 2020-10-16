/**
 * Provides reliable checks for common key presses, uses `Event.key` on supported browsers
 * or the deprecated `Event.which`.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  EventKey (alias)
 * @module  WoltLabSuite/Core/Event/Key
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Tab = exports.Space = exports.Home = exports.Escape = exports.Enter = exports.End = exports.Comma = exports.ArrowUp = exports.ArrowRight = exports.ArrowLeft = exports.ArrowDown = void 0;
    function _test(event, key, which) {
        if (!(event instanceof Event)) {
            throw new TypeError("Expected a valid event when testing for key '" + key + "'.");
        }
        return event.key === key || event.which === which;
    }
    /**
     * Returns true if the pressed key equals 'ArrowDown'.
     *
     * @deprecated 5.4 Use `event.key === "ArrowDown"` instead.
     */
    function ArrowDown(event) {
        return _test(event, 'ArrowDown', 40);
    }
    exports.ArrowDown = ArrowDown;
    /**
     * Returns true if the pressed key equals 'ArrowLeft'.
     *
     * @deprecated 5.4 Use `event.key === "ArrowLeft"` instead.
     */
    function ArrowLeft(event) {
        return _test(event, 'ArrowLeft', 37);
    }
    exports.ArrowLeft = ArrowLeft;
    /**
     * Returns true if the pressed key equals 'ArrowRight'.
     *
     * @deprecated 5.4 Use `event.key === "ArrowRight"` instead.
     */
    function ArrowRight(event) {
        return _test(event, 'ArrowRight', 39);
    }
    exports.ArrowRight = ArrowRight;
    /**
     * Returns true if the pressed key equals 'ArrowUp'.
     *
     * @deprecated 5.4 Use `event.key === "ArrowUp"` instead.
     */
    function ArrowUp(event) {
        return _test(event, 'ArrowUp', 38);
    }
    exports.ArrowUp = ArrowUp;
    /**
     * Returns true if the pressed key equals 'Comma'.
     *
     * @deprecated 5.4 Use `event.key === ","` instead.
     */
    function Comma(event) {
        return _test(event, ',', 44);
    }
    exports.Comma = Comma;
    /**
     * Returns true if the pressed key equals 'End'.
     *
     * @deprecated 5.4 Use `event.key === "End"` instead.
     */
    function End(event) {
        return _test(event, 'End', 35);
    }
    exports.End = End;
    /**
     * Returns true if the pressed key equals 'Enter'.
     *
     * @deprecated 5.4 Use `event.key === "Enter"` instead.
     */
    function Enter(event) {
        return _test(event, 'Enter', 13);
    }
    exports.Enter = Enter;
    /**
     * Returns true if the pressed key equals 'Escape'.
     *
     * @deprecated 5.4 Use `event.key === "Escape"` instead.
     */
    function Escape(event) {
        return _test(event, 'Escape', 27);
    }
    exports.Escape = Escape;
    /**
     * Returns true if the pressed key equals 'Home'.
     *
     * @deprecated 5.4 Use `event.key === "Home"` instead.
     */
    function Home(event) {
        return _test(event, 'Home', 36);
    }
    exports.Home = Home;
    /**
     * Returns true if the pressed key equals 'Space'.
     *
     * @deprecated 5.4 Use `event.key === "Space"` instead.
     */
    function Space(event) {
        return _test(event, 'Space', 32);
    }
    exports.Space = Space;
    /**
     * Returns true if the pressed key equals 'Tab'.
     *
     * @deprecated 5.4 Use `event.key === "Tab"` instead.
     */
    function Tab(event) {
        return _test(event, 'Tab', 9);
    }
    exports.Tab = Tab;
});
