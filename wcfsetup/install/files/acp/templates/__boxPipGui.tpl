<script data-relocate="true">
	require(['Language'], function(Language) {
		{jsphrase name='wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere'}
		{jsphrase name='wcf.acp.pip.box.visibilityExceptions.visibleEverywhere'}
		
		var visibleEverywhere = elById('visibleEverywhere');
		var visibilityExceptionsLabel = elById('visibilityExceptionsLabel');
		
		function updateVisibilityExceptions() {
			if (visibleEverywhere.checked) {
				visibilityExceptionsLabel.innerHTML = Language.get('wcf.acp.pip.box.visibilityExceptions.visibleEverywhere');
			}
			else {
				visibilityExceptionsLabel.innerHTML = Language.get('wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere');
			}
		}
		
		visibleEverywhere.addEventListener('change', updateVisibilityExceptions);
		
		updateVisibilityExceptions();
	});
</script>
