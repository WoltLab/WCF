<script data-relocate="true">
	require(['Language'], function(Language) {
		{jsphrase name='wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere'}
		{jsphrase name='wcf.acp.pip.box.visibilityExceptions.visibleEverywhere'}
		
		var visibleEverywhere = elById('visibleEverywhere');
		var visibilityExceptionsLabel = elBySel('#visibilityExceptionsContainer > dt > label');
		
		function updateVisibilityExceptions() {
			if (visibleEverywhere.checked) {
				visibilityExceptionsLabel.innerHTML = Language.get('wcf.acp.pip.box.visibilityExceptions.visibleEverywhere');
			}
			else {
				visibilityExceptionsLabel.innerHTML = Language.get('wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere');
			}
		}
		
		visibleEverywhere.addEventListener('change', updateVisibilityExceptions);
		elById('visibleEverywhere_no').addEventListener('change', updateVisibilityExceptions);
		
		updateVisibilityExceptions();
	});
</script>
