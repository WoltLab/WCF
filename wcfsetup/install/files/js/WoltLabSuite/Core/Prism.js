/**
 * Loads Prism while disabling automated highlighting.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2021 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Prism
 */
window.Prism = window.Prism || {};
window.Prism.manual = true;
define(['prism/prism'], function () {
    window.Prism.hooks.add('wrap', (env) => {
        env.classes = env.classes.map((c) => `prism-${c}`);
    });
    return Prism;
});
