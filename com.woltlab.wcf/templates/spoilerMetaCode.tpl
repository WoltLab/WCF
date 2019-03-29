<div class="spoilerBox jsSpoilerBox">
	<div class="jsOnly spoilerBoxHeader">
		<a href="#" id="spoiler-button-{@$spoilerID}" role="button" aria-expanded="false" aria-controls="spoiler-content-{@$spoilerID}" class="button small jsSpoilerToggle"{if $buttonLabel} data-has-custom-label="true"{/if}>{if $buttonLabel}{$buttonLabel}{else}{lang}wcf.bbcode.spoiler.show{/lang}{/if}</a>
	</div>
	
	<div class="spoilerBoxContent" style="display: none" id="spoiler-content-{@$spoilerID}" aria-hidden="true" aria-labelledby="spoiler-button-{@$spoilerID}">
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
					var isActive = toggleButton.classList.contains('active');
					window[(isActive ? 'elShow' : 'elHide')](container);
					elAttr(toggleButton, 'aria-expanded', isActive);
					elAttr(container, 'aria-hidden', !isActive);
					
					if (!elDataBool(toggleButton, 'has-custom-label')) {
						toggleButton.textContent = (toggleButton.classList.contains('active')) ? '{lang}wcf.bbcode.spoiler.hide{/lang}' : '{lang}wcf.bbcode.spoiler.show{/lang}';
					}
				});
			});
		</script>
	{/if}
</div>
