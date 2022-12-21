import WoltlabCoreGoogleMapsElement from "./woltlab-core-google-maps";
import { MarkerClusterer } from "@googlemaps/markerclusterer";

import "./woltlab-core-google-maps";

const markerClusterers = new WeakMap<WoltlabCoreGoogleMapsElement, MarkerClusterer>();

export async function addMarker(
  googleMaps: WoltlabCoreGoogleMapsElement,
  latitude: number,
  longitude: number,
  title: string,
  focus?: boolean,
): Promise<void> {
  const map = await googleMaps.getMap();

  const marker = new google.maps.Marker({
    map,
    position: new google.maps.LatLng(latitude, longitude),
    title,
  });

  if (focus) {
    map.setCenter(marker.getPosition()!);
  }

  let clusterer = markerClusterers.get(googleMaps);
  if (clusterer === undefined) {
    clusterer = new MarkerClusterer({
      map,
    });
    markerClusterers.set(googleMaps, clusterer);
  }

  clusterer.addMarker(marker);
}

export async function addDraggableMarker(
  googleMaps: WoltlabCoreGoogleMapsElement,
  latitude?: number,
  longitude?: number,
): Promise<google.maps.Marker> {
  const map = await googleMaps.getMap();

  if (latitude === undefined) {
    latitude = googleMaps.lat;
  }
  if (longitude === undefined) {
    longitude = googleMaps.lng;
  }

  const marker = new google.maps.Marker({
    map,
    position: new google.maps.LatLng(latitude, longitude),
    draggable: true,
    clickable: false,
  });

  map.setCenter(marker.getPosition()!);

  return marker;
}
