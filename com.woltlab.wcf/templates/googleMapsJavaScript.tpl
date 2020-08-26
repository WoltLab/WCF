{if !$__googleMapsInit|isset}
	{assign var=__googleMapsInit value=1}
	
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/markerClusterer{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
	<script data-relocate="true" src="//maps.google.com/maps/api/js?{if GOOGLE_MAPS_API_KEY}key={@GOOGLE_MAPS_API_KEY}&amp;{/if}language={@$__wcf->language->getFixedLanguageCode()}"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/oms.min.js?v={@LAST_UPDATE_TIME}"></script>
	<script data-relocate="true">
		$(function() {
			WCF.Language.addObject({
				'wcf.map.noLocationSuggestions': '{jslang}wcf.map.noLocationSuggestions{/jslang}',
				'wcf.map.route.error.not_found': '{jslang}wcf.map.route.error.not_found{/jslang}',
				'wcf.map.route.error.over_query_limit': '{jslang}wcf.map.route.error.over_query_limit{/jslang}',
				'wcf.map.route.error.request_denied': '{jslang}wcf.map.route.error.request_denied{/jslang}',
				'wcf.map.route.origin': '{jslang}wcf.map.route.origin{/jslang}',
				'wcf.map.route.planner': '{jslang}wcf.map.route.planner{/jslang}',
				'wcf.map.route.travelMode': '{jslang}wcf.map.route.travelMode{/jslang}',
				'wcf.map.route.travelMode.bicycling': '{jslang}wcf.map.route.travelMode.bicycling{/jslang}',
				'wcf.map.route.travelMode.driving': '{jslang}wcf.map.route.travelMode.driving{/jslang}',
				'wcf.map.route.travelMode.transit': '{jslang}wcf.map.route.travelMode.transit{/jslang}',
				'wcf.map.route.travelMode.walking': '{jslang}wcf.map.route.travelMode.walking{/jslang}',
				'wcf.map.route.viewOnGoogleMaps': '{jslang}wcf.map.route.viewOnGoogleMaps{/jslang}',
				'wcf.map.showLocationSuggestions': '{jslang}wcf.map.showLocationSuggestions{/jslang}',
				'wcf.map.useLocationSuggestion': '{jslang}wcf.map.useLocationSuggestion{/jslang}'
			});
			
			WCF.Location.GoogleMaps.Settings.set({
				disableDoubleClickZoom: {if GOOGLE_MAPS_ENABLE_DOUBLE_CLICK_ZOOM}0{else}1{/if},
				draggable: {@GOOGLE_MAPS_ENABLE_DRAGGING},
				mapType: '{@GOOGLE_MAPS_TYPE}',
				markerClustererImagePath: '{@$__wcf->getPath()}images/markerClusterer/',
				scaleControl: {@GOOGLE_MAPS_ENABLE_SCALE_CONTROL},
				scrollwheel: {@GOOGLE_MAPS_ENABLE_SCROLL_WHEEL_ZOOM},
				type: '{@GOOGLE_MAPS_TYPE}',
				zoom: {@GOOGLE_MAPS_ZOOM},
				defaultLatitude: {@GOOGLE_MAPS_DEFAULT_LATITUDE},
				defaultLongitude: {@GOOGLE_MAPS_DEFAULT_LONGITUDE},
				accessUserLocation: {@GOOGLE_MAPS_ACCESS_USER_LOCATION}
			});
			
			{event name='javascriptInit'}
		});
	</script>
{/if}
