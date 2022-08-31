{
  let initCalled = false;
  const callbackPromise = new Promise<void>((resolve) => {
    window.__initGoogleMaps = resolve;
  });

  const loadGoogleMaps = (apiKey: string): Promise<void> => {
    if (!initCalled) {
      const script = document.createElement("script");
      script.src =
        "https://maps.googleapis.com/maps/api/js?" + (apiKey ? `key=${apiKey}&` : "") + "callback=__initGoogleMaps";
      document.head.appendChild(script);
      initCalled = true;
    }
    return callbackPromise;
  };

  class GoogleMaps extends HTMLElement {
    private _map?: google.maps.Map = undefined;
    private mapLoaded: Promise<void>;
    private mapLoadedResolve?: () => void = undefined;

    constructor() {
      super();

      this.mapLoaded = new Promise<void>((resolve) => {
        this.mapLoadedResolve = resolve;
      });
    }

    connectedCallback() {
      this.validate();

      void loadGoogleMaps(this.apiKey).then(() => {
        this._map = new google.maps.Map(this, {
          zoom: 13,
          center: {
            lat: 0,
            lng: 0,
          },
        });

        if (this.mapLoadedResolve) {
          this.mapLoadedResolve();
          this.mapLoadedResolve = undefined;
        }
      });
    }

    async addMarker(latitude: number, longitude: number, title: string, focus?: boolean): Promise<void> {
      await this.mapLoaded;

      const marker = new google.maps.Marker({
        map: this.map,
        position: new google.maps.LatLng(latitude, longitude),
        title: title,
      });

      if (focus) {
        this.map!.setCenter(marker.getPosition()!);
      }
    }

    private validate(): void {
      if (!this.apiKey) {
        //throw new TypeError("Must provide an api key.");
      }
    }

    get apiKey(): string {
      return this.getAttribute("api-key") || "";
    }

    get map(): google.maps.Map | undefined {
      return this._map;
    }
  }

  window.customElements.define("google-maps", GoogleMaps);
}
