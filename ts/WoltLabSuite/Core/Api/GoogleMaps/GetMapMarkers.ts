/**
 * Fetches the map markers located within the given boundaries from an RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { fromInfallibleApiRequest } from "../Result";

export type MarkerData = {
  dialog?: string;
  infoWindow: string;
  items: number;
  latitude: number;
  location: string;
  longitude: number;
  objectIDs?: number[];
  objectID?: number;
  title: string;
};

type Response = {
  markers: MarkerData[];
};

export type MapBoundaries = {
  northLatitude: number;
  southLatitude: number;
  eastLongitude: number;
  westLongitude: number;
};

export async function getMapMarkers(
  endpoint: string,
  boundaries: MapBoundaries,
  excludedObjectIDs: number[],
  additionalParameters: Record<string, unknown> = {},
): Promise<Response> {
  const url = new URL(`${window.WSC_RPC_API_URL}${endpoint}`);

  return fromInfallibleApiRequest(() => {
    return prepareRequest(url)
      .post({
        ...additionalParameters,
        northLatitude: boundaries.northLatitude,
        southLatitude: boundaries.southLatitude,
        eastLongitude: boundaries.eastLongitude,
        westLongitude: boundaries.westLongitude,
        excludedObjectIDs,
      })
      .disableLoadingIndicator()
      .fetchAsJson();
  });
}
