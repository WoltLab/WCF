/**
 * Map route planner based on Google Maps.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Controller/Map/Route/Planner
 */
define([
	'Dom/Traverse',
	'Dom/Util',
	'Language',
	'Ui/Dialog',
	'WoltLabSuite/Core/Ajax/Status'
], function(
	DomTraverse,
	DomUtil,
	Language,
	UiDialog,
	AjaxStatus
) {
	/**
	 * @constructor
	 */
	function Planner(buttonId, destination) {
		this._button = elById(buttonId);
		if (this._button === null) {
			throw new Error("Unknown button with id '" + buttonId + "'");
		}
		
		this._button.addEventListener('click', this._openDialog.bind(this));
		
		this._destination = destination;
	}
	Planner.prototype = {
		/**
		 * Sets up the route planner dialog.
		 */
		_dialogSetup: function() {
			return {
				id: this._button.id + 'Dialog',
				options: {
					onShow: this._initDialog.bind(this),
					title: Language.get('wcf.map.route.planner')
				},
				source: '<div class="googleMapsDirectionsContainer" style="display: none;">' +
						'<div class="googleMap"></div>' +
						'<div class="googleMapsDirections"></div>' +
					'</div>' +
					'<small class="googleMapsDirectionsGoogleLinkContainer"><a href="' + this._getGoogleMapsLink() + '" class="googleMapsDirectionsGoogleLink" target="_blank" style="display: none;">' + Language.get('wcf.map.route.viewOnGoogleMaps') + '</a></small>' +
					'<dl>' +
						'<dt>' + Language.get('wcf.map.route.origin') + '</dt>' +
						'<dd><input type="text" name="origin" class="long" autofocus /></dd>' +
					'</dl>' +
					'<dl style="display: none;">' +
						'<dt>' + Language.get('wcf.map.route.travelMode') + '</dt>' +
						'<dd>' +
							'<select name="travelMode">' +
								'<option value="driving">' + Language.get('wcf.map.route.travelMode.driving') + '</option>' + 
								'<option value="walking">' + Language.get('wcf.map.route.travelMode.walking') + '</option>' + 
								'<option value="bicycling">' + Language.get('wcf.map.route.travelMode.bicycling') + '</option>' +
								'<option value="transit">' + Language.get('wcf.map.route.travelMode.transit') + '</option>' +
							'</select>' +
						'</dd>' +
					'</dl>'
			}
		},
		
		/**
		 * Calculates the route based on the given result of a location search.
		 * 
		 * @param	{object}	data
		 */
		_calculateRoute: function(data) {
			var dialog = UiDialog.getDialog(this).dialog;
			
			if (data.label) {
				this._originInput.value = data.label;
			}
			
			if (this._map === undefined) {
				this._map = new google.maps.Map(elByClass('googleMap', dialog)[0], {
					disableDoubleClickZoom: WCF.Location.GoogleMaps.Settings.get('disableDoubleClickZoom'),
					draggable: WCF.Location.GoogleMaps.Settings.get('draggable'),
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					scaleControl: WCF.Location.GoogleMaps.Settings.get('scaleControl'),
					scrollwheel: WCF.Location.GoogleMaps.Settings.get('scrollwheel')
				});
				
				this._directionsService = new google.maps.DirectionsService();
				this._directionsRenderer = new google.maps.DirectionsRenderer();
				
				this._directionsRenderer.setMap(this._map);
				this._directionsRenderer.setPanel(elByClass('googleMapsDirections', dialog)[0]);
				
				this._googleLink = elByClass('googleMapsDirectionsGoogleLink', dialog)[0];
			}
			
			var request = {
				destination: this._destination,
				origin: data.location,
				provideRouteAlternatives: true,
				travelMode: google.maps.TravelMode[this._travelMode.value.toUpperCase()]
			};
			
			AjaxStatus.show();
			this._directionsService.route(request, this._setRoute.bind(this));
			
			elAttr(this._googleLink, 'href', this._getGoogleMapsLink(data.location, this._travelMode.value));
			
			this._lastOrigin = data.location;
		},
		
		/**
		 * Returns the Google Maps link based on the given optional directions origin
		 * and optional travel mode.
		 * 
		 * @param	{google.maps.LatLng}	origin
		 * @param	{string}		travelMode
		 * @return	{string}
		 */
		_getGoogleMapsLink: function(origin, travelMode) {
			if (origin) {
				var link = 'https://www.google.com/maps/dir/?api=1' +
						'&origin=' + origin.lat() + ',' + origin.lng() + '' +
						'&destination=' + this._destination.lat() + ',' + this._destination.lng();
				
				if (travelMode) {
					link += '&travelmode=' + travelMode;
				}
				
				return link;
			}
			
			return 'https://www.google.com/maps/search/?api=1&query=' + this._destination.lat() + ',' + this._destination.lng();
		},
		
		/**
		 * Initializes the route planning dialog.
		 */
		_initDialog: function() {
			if (!this._didInitDialog) {
				var dialog = UiDialog.getDialog(this).dialog;
				
				// make input element a location search
				this._originInput = elBySel('input[name="origin"]', dialog);
				new WCF.Location.GoogleMaps.LocationSearch(this._originInput, this._calculateRoute.bind(this));
				
				this._travelMode = elBySel('select[name="travelMode"]', dialog);
				this._travelMode.addEventListener('change', this._updateRoute.bind(this));
				
				this._didInitDialog = true;
			}
		},
		
		/**
		 * Opens the route planning dialog.
		 */
		_openDialog: function() {
			UiDialog.open(this);
		},
		
		/**
		 * Handles the response of the direction service.
		 * 
		 * @param	{object}	result
		 * @param	{string}	status
		 */
		_setRoute: function(result, status) {
			AjaxStatus.hide();
			
			if (status === 'OK') {
				elShow(this._map.getDiv().parentNode);
				
				google.maps.event.trigger(this._map, 'resize');
				
				this._directionsRenderer.setDirections(result);
				
				elShow(DomTraverse.parentByTag(this._travelMode, 'DL'));
				elShow(this._googleLink);
				
				elInnerError(this._originInput, false);
			}
			else {
				// map irrelevant errors to not found error
				if (status !== 'OVER_QUERY_LIMIT' && status !== 'REQUEST_DENIED') {
					status = 'NOT_FOUND';
				}
				
				elInnerError(this._originInput, Language.get('wcf.map.route.error.' + status.toLowerCase()));
			}
		},
		
		/**
		 * Updates the route after the travel mode has been changed.
		 */
		_updateRoute: function() {
			this._calculateRoute({
				location: this._lastOrigin
			});
		}
	};
	
	return Planner;
});
