<div class="spoilerBox jsSpoilerBox">
	<header class="jsOnly">
		<a class="button small jsSpoilerToggle"{if $buttonLabel} data-has-custom-label="true"{/if}>{if $buttonLabel}{$buttonLabel}{else}{lang}wcf.bbcode.spoiler.show{/lang}{/if}</a>
	</header>
	
	<div style="display: none">
		<!-- META_CODE_INNER_CONTENT -->
	</div>
	
	{if !$__wcfSpoilerBBCodeJavaScript|isset}
		{assign var='__wcfSpoilerBBCodeJavaScript' value=true}
		<script data-relocate="true">
			elBySelAll('.jsSpoilerBox', null, function(spoilerBox) {
				spoilerBox.classList.remove('jsSpoilerBox');
				
				var toggleButton = elBySel('.jsSpoilerToggle', spoilerBox);
				var container = toggleButton.parentNode.nextElementSibling;
				
				toggleButton.addEventListener(WCF_CLICK_EVENT, function(event) {
					event.preventDefault();
					
					toggleButton.classList.toggle('active');
					window[(toggleButton.classList.contains('active') ? 'elShow' : 'elHide')](container);
					
					if (!elDataBool(toggleButton, 'has-custom-label')) {
						toggleButton.textContent = (toggleButton.classList.contains('active')) ? '{lang}wcf.bbcode.spoiler.hide{/lang}' : '{lang}wcf.bbcode.spoiler.show{/lang}';
					}
				});
			});
		</script>
	{/if}
</div>
