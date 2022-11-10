/**
 * Template provides a template scripting compiler
 * similar to the PHP one of WoltLab Suite Core. It supports a limited set of
 * useful commands and compiles templates down to a pure JavaScript Function.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

const Template = window.WoltLabTemplate;

interface Template {
  new (template: string): typeof window.WoltLabTemplate;
  fetch(v: object): string;
}

export = Template;
