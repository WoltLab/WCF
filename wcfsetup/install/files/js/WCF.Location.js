/**
 * Location-related classes for WCF
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Location = { };

/**
 * Provides location-related utility functions.
 */
WCF.Location.Util = {
	/**
	 * Passes the user's current latitude and longitude to the given function
	 * as parameters. If the user's current position cannot be determined,
	 * undefined will be passed as both parameters.
	 * 
	 * @param	function	callback
	 * @param	integer		timeout
	 */
	getLocation: function(callback, timeout) {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function(position) {
				callback(position.coords.latitude, position.coords.longitude);
			}, function() {
				callback(undefined, undefined);
			}, {
				timeout: timeout || 5000
			});
		}
		else {
			callback(undefined, undefined);
		}
	}
};

/**
 * Namespace for Google Maps-related classes.
 */
WCF.Location.GoogleMaps = { };

/**
 * Handles the global Google Maps settings.
 */
WCF.Location.GoogleMaps.Settings = {
	/**
	 * Google Maps settings
	 * @var	object
	 */
	_settings: { },
	
	/**
	 * Returns the value of a certain setting or null if it doesn't exist.
	 * 
	 * If no parameter is given, all settings are returned.
	 * 
	 * @param	string		setting
	 * @return	mixed
	 */
	get: function(setting) {
		if (setting === undefined) {
			return this._settings;
		}
		
		if (this._settings[setting] !== undefined) {
			return this._settings[setting];
		}
		
		return null;
	},
	
	/**
	 * Sets the value of a certain setting.
	 * 
	 * @param	mixed		setting
	 * @param	mixed		value
	 */
	set: function(setting, value) {
		if ($.isPlainObject(setting)) {
			for (var index in setting) {
				this._settings[index] = setting[index];
			}
		}
		else {
			this._settings[setting] = value;
		}
	}
};

/**
 * Handles a Google Maps map.
 */
WCF.Location.GoogleMaps.Map = Class.extend({
	/**
	 * map object for the displayed map
	 * @var	google.maps.Map
	 */
	_map: null,
	
	/**
	 * list of markers on the map
	 * @var	array<google.maps.Marker>
	 */
	_markers: [ ],
	
	/**
	 * Initalizes a new WCF.Location.Map object.
	 * 
	 * @param	string		mapContainerID
	 * @param	object		mapOptions
	 */
	init: function(mapContainerID, mapOptions) {
		this._mapContainer = $('#' + mapContainerID);
		this._mapOptions = $.extend(true, this._getDefaultMapOptions(), mapOptions);
		
		this._map = new google.maps.Map(this._mapContainer[0], this._mapOptions);
		this._markers = [ ];
		
		this.refresh();
	},
	
	/**
	 * Returns the default map options.
	 * 
	 * @return	object
	 */
	_getDefaultMapOptions: function() {
		var $defaultMapOptions = { };
		
		// dummy center value
		$defaultMapOptions.center = new google.maps.LatLng(52.517, 13.4); // Berlin
		
		// double click to zoom
		$defaultMapOptions.disableDoubleClickZoom = WCF.Location.GoogleMaps.Settings.get('disableDoubleClickZoom');
		
		// draggable
		$defaultMapOptions.draggable = WCF.Location.GoogleMaps.Settings.get('draggable');
		
		// map type
		switch (WCF.Location.GoogleMaps.Settings.get('mapType')) {
			case 'map':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
			break;
			
			case 'satellite':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
			break;
			
			case 'physical':
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
			break;
			
			case 'hybrid':
			default:
				$defaultMapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
			break;
		}
		
		/// map type controls
		$defaultMapOptions.mapTypeControl = WCF.Location.GoogleMaps.Settings.get('mapTypeControl') != 'off';
		if ($defaultMapOptions.mapTypeControl) {
			switch (WCF.Location.GoogleMaps.Settings.get('mapTypeControl')) {
				case 'dropdown':
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
					};
				break;
				
				case 'horizontalBar':
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
					};
				break;
				
				default:
					$defaultMapOptions.mapTypeControlOptions = {
						style: google.maps.MapTypeControlStyle.DEFAULT
					};
				break;
			}
		}
		
		// scale control
		$defaultMapOptions.scaleControl = WCF.Location.GoogleMaps.Settings.get('scaleControl');
		$defaultMapOptions.scrollwheel = WCF.Location.GoogleMaps.Settings.get('scrollwheel');
		
		// zoom
		$defaultMapOptions.zoom = WCF.Location.GoogleMaps.Settings.get('zoom');
		
		return $defaultMapOptions;
	},
	
	/**
	 * Adds a draggable marker at the given position to the map and returns
	 * the created marker object.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 * @return	google.maps.Marker
	 */
	addDraggableMarker: function(latitude, longitude) {
		var $marker = new google.maps.Marker({
			clickable: false,
			draggable: true,
			map: this._map,
			position: new google.maps.LatLng(latitude, longitude)
		});
		
		this._markers.push($marker);
		
		return $marker;
	},
	
	/**
	 * Adds a marker with the given data to the map and returns the created
	 * marker object.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 * @param	string		title
	 * @param	mixed		icon
	 * @param	string		information
	 * @return	google.maps.Marker
	 */
	addMarker: function(latitude, longitude, title, icon, information) {
		var $marker = new google.maps.Marker({
			map: this._map,
			position: new google.maps.LatLng(latitude, longitude),
			title: title
		});
		
		// add icon
		if (icon) {
			$marker.setIcon(icon);
		}
		
		// add info window for marker information
		if (information) {
			var $infoWindow = new google.maps.InfoWindow({
				content: information
			});
			google.maps.event.addEventListemer($marker, 'click', $.proxy(function() {
				$infoWindow.open(this._map, $marker);
			}, this));
		}
		
		this._markers.push($marker);
		
		return $marker;
	},
	
	/**
	 * Returns all markers on the map.
	 * 
	 * @return	array<google.maps.Marker>
	 */
	getMarkers: function() {
		return this._markers;
	},
	
	/**
	 * Returns the Google Maps map object.
	 * 
	 * @return	google.maps.Map
	 */
	getMap: function() {
		return this._map;
	},
	
	/**
	 * Refreshes the map.
	 */
	refresh: function() {
		google.maps.event.trigger(this._map, 'resize');
	},
	
	/**
	 * Refreshes the boundaries of the map to show all markers.
	 */
	refreshBounds: function() {
		var $minLatitude = null;
		var $maxLatitude = null;
		var $minLongitude = null;
		var $maxLongitude = null;
		
		for (var $index in this._markers) {
			var $marker = this._markers[$index];
			var $latitude = $marker.getPosition().lat();
			var $longitude = $marker.getPosition().lng();
			
			if ($minLatitude === null) {
				$minLatitude = $maxLatitude = $latitude;
				$minLongitude = $maxLongitude = $longitude;
			}
			else {
				if ($minLatitude > $latitude) {
					$minLatitude = $latitude;
				}
				else if ($maxLatitude < $latitude) {
					$maxLatitude = $latitude;
				}
				
				if ($minLongitude > $latitude) {
					$minLongitude = $latitude;
				}
				else if ($maxLongitude < $longitude) {
					$maxLongitude = $longitude;
				}
			}
		}
		
		this._map.fitBounds(new google.maps.LatLngBounds(
			new google.maps.LatLng($minLatitude, $minLongitude),
			new google.maps.LatLng($maxLatitude, $maxLongitude)
		));
	},
	
	/**
	 * Removes all markers from the map.
	 */
	removeMarkers: function() {
		for (var $index in this._markers) {
			this._markers[$index].setMap(null);
		}
		
		this._markers = [ ];
	},
	
	/**
	 * Sets the center of the map to the given position.
	 * 
	 * @param	float		latitude
	 * @param	float		longitude
	 */
	setCenter: function(latitude, longitude) {
		this._map.setCenter(new google.maps.LatLng(latitude, longitude));
	}
});

/**
 * Provides location searches based on google.maps.Geocoder.
 */
WCF.Location.GoogleMaps.LocationSearch = WCF.Search.Base.extend({
	/**
	 * Google Maps geocoder object
	 * @var	google.maps.Geocoder
	 */
	_geocoder: null,
	
	/**
	 * @see	WCF.Search.Base.init()
	 */
	init: function(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay) {
		this._super(searchInput, callback, excludedSearchValues, commaSeperated, showLoadingOverlay);
		
		this._geocoder = new google.maps.Geocoder();
	},
	
	/**
	 * @see	WCF.Search.Base._createListItem()
	 */
	_createListItem: function(geocoderResult) {
		var $listItem = $('<li><span>' + WCF.String.escapeHTML(geocoderResult.formatted_address) + '</span></li>').appendTo(this._list);
		$listItem.data('location', geocoderResult.geometry.location).data('label', geocoderResult.formatted_address).click($.proxy(this._executeCallback, this));
		
		this._itemCount++;
		
		return $listItem;
	},
	
	/**
	 * @see	WCF.Search.Base._keyUp()
	 */
	_keyUp: function(event) {
		// handle arrow keys and return key
		switch (event.which) {
			case 37: // arrow-left
			case 39: // arrow-right
				return;
			break;
			
			case 38: // arrow up
				this._selectPreviousItem();
				return;
			break;
			
			case 40: // arrow down
				this._selectNextItem();
				return;
			break;
			
			case 13: // return key
				return this._selectElement(event);
			break;
		}
		
		var $content = this._getSearchString(event);
		if ($content === '') {
			this._clearList(true);
		}
		else if ($content.length >= this._triggerLength) {
			this._clearList(false);
			
			this._geocoder.geocode({
				address: $content
			}, $.proxy(this._success, this));
		}
		else {
			// input below trigger length
			this._clearList(false);
		}
	},
	
	/**
	 * Handles a successfull geocoder request.
	 * 
	 * @param	array		results
	 * @param	integer		status
	 */
	_success: function(results, status) {
		if (status != google.maps.GeocoderStatus.OK) {
			return;
		}
		
		if ($.getLength(results)) {
			var $count = 0;
			for (var $index in results) {
				this._createListItem(results[$index]);
				
				if (++$count == 10) {
					break;
				}
			}
		}
		else if (!this._handleEmptyResult()) {
			return;
		}
		
		WCF.CloseOverlayHandler.addCallback('WCF.Search.Base', $.proxy(function() { this._clearList(); }, this));
		
		var $containerID = this._searchInput.parents('.dropdown').wcfIdentify();
		if (!WCF.Dropdown.getDropdownMenu($containerID).hasClass('dropdownOpen')) {
			WCF.Dropdown.toggleDropdown($containerID);
		}
		
		// pre-select first item
		this._itemIndex = -1;
		if (!WCF.Dropdown.getDropdown($containerID).data('disableAutoFocus')) {
			this._selectNextItem();
		}
	}
});

/**
 * Handles setting a single location on a Google Map.
 */
WCF.Location.GoogleMaps.LocationInput = Class.extend({
	/**
	 * location search object
	 * @var	WCF.Location.GoogleMaps.LocationSearch
	 */
	_locationSearch: null,
	
	/**
	 * related map object
	 * @var	WCF.Location.GoogleMaps.Map
	 */
	_map: null,
	
	/**
	 * draggable marker to set the location
	 * @var	google.maps.Marker
	 */
	_marker: null,
	
	/**
	 * Initializes a new WCF.Location.GoogleMaps.LocationInput object.
	 * 
	 * @param	string		mapContainerID
	 * @param	object		mapOptions
	 * @param	string		searchInput
	 * @param	function	callback
	 */
	init: function(mapContainerID, mapOptions, searchInput, latitude, longitude) {
		this._searchInput = searchInput;
		this._map = new WCF.Location.GoogleMaps.Map(mapContainerID, mapOptions);
		this._locationSearch = new WCF.Location.GoogleMaps.LocationSearch(searchInput, $.proxy(this._setMarkerByLocation, this));
		
		if (latitude && longitude) {
			this._marker = this._map.addDraggableMarker(latitude, longitude);
		}
		else {
			this._marker = this._map.addDraggableMarker(0, 0);
			
			WCF.Location.Util.getLocation($.proxy(function(latitude, longitude) {
				WCF.Location.GoogleMaps.Util.moveMarker(this._marker, latitude, longitude);
			}, this));
		}
	},
	
	/**
	 * Returns the related map.
	 * 
	 * @return	WCF.Location.GoogleMaps.Map
	 */
	getMap: function() {
		return this._map;
	},
	
	/**
	 * Returns the draggable marker used to set the location.
	 * 
	 * @return	google.maps.Marker
	 */
	getMarker: function() {
		return this._marker;
	},
	
	/**
	 * Sets the marker based on an entered location.
	 * 
	 * @param	object		data
	 */
	_setMarkerByLocation: function(data) {
		this._marker.setPosition(data.location);
		WCF.Location.GoogleMaps.Util.focusMarker(this._marker);
		
		$(this._searchInput).val('');
	}
});

/**
 * Provides utility functions for Google Maps maps.
 */
WCF.Location.GoogleMaps.Util = {
	/**
	 * Focuses the given marker's map on the marker.
	 * 
	 * @param	google.maps.Marker	marker
	 */
	focusMarker: function(marker) {
		marker.getMap().setCenter(marker.getPosition());
	},
	
	/**
	 * Returns the latitude and longitude of the given marker.
	 * 
	 * @return	object
	 */
	getMarkerPosition: function(marker) {
		return {
			latitude: marker.getPosition().lat(),
			longitude: marker.getPosition().lng()
		};
	},
	
	/**
	 * Moves the given marker to the given position.
	 * 
	 * @param	google.maps.Marker		marker
	 * @param	float				latitude
	 * @param	float				longitude
	 */
	moveMarker: function(marker, latitude, longitude) {
		marker.setPosition(new google.maps.LatLng(latitude, longitude));
	},
};
