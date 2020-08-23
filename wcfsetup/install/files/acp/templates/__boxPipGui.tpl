<script data-relocate="true">
	require(['Language'], function(Language) {
		Language.addObject({
			'wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere': '{jslang}wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere{/jslang}',
			'wcf.acp.pip.box.visibilityExceptions.visibleEverywhere': '{jslang}wcf.acp.pip.box.visibilityExceptions.visibleEverywhere{/jslang}'
		});
		
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
