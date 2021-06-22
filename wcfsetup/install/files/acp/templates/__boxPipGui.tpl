<script data-relocate="true">
	(() => {
		const visibleEverywhere = document.getElementById('visibleEverywhere');
		const visibilityExceptionsLabel = document.querySelector('#visibilityExceptionsContainer > dt > label');
		
		function updateVisibilityExceptions() {
			if (visibleEverywhere.checked) {
				visibilityExceptionsLabel.innerHTML = '{jslang}wcf.acp.pip.box.visibilityExceptions.visibleEverywhere{/jslang}';
			} else {
				visibilityExceptionsLabel.innerHTML = '{jslang}wcf.acp.pip.box.visibilityExceptions.hiddenEverywhere{/jslang}';
			}
		}
		
		visibleEverywhere.addEventListener('change', updateVisibilityExceptions);
		document.getElementById('visibleEverywhere_no').addEventListener('change', updateVisibilityExceptions);
		
		updateVisibilityExceptions();
	})();
</script>
