import { wheneverFirstSeen } from "../../Helper/Selector";
import { setup as setupSuggestion } from "./Geocoding/Suggestion";

export function setup(): void {
  wheneverFirstSeen("[data-google-maps-geocoding]", async (element: HTMLInputElement) => {
    const map = document.getElementById(element.dataset.googleMapsGeocoding!) as any;

    const marker = await map.addDraggableMarker() as google.maps.Marker;
    const geocoder = await map.getGeocoder() as google.maps.Geocoder;
    marker.addListener("dragend", () => {
      void geocoder.geocode({ location: marker.getPosition() }, (results, status) => {
        if (status === google.maps.GeocoderStatus.OK) {
          element.value = results![0].formatted_address;
        }
      });
    });

    setupSuggestion(element, geocoder);
  });
}
