/**
 * Handles loading and initialization of Facebook's JavaScript SDK.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import "https://connect.facebook.net/en_US/sdk.js";

// see: https://developers.facebook.com/docs/javascript/reference/FB.init/v7.0
FB.init({
  version: "v7.0",
});

export = FB;
