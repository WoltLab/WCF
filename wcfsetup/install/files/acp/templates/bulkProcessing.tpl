{include file='header' pageTitle=$objectType->getProcessor()->getLanguageItemPrefix()}

<script data-relocate="true">
	require(['WoltLab/WCF/UI/TabMenu'], function(UITabMenu) {
		UITabMenu.setup();
		
		function toggleActionOptions(event) {
			var actionName = event.currentTarget.getAttribute('value');
			var actionSettings = document.getElementsByClassName('jsBulkProcessingActionSettings');
			for (var i = 0, length = actionSettings.length; i < length; i++) {
				var settings = actionSettings[i];
				
				if (settings.getAttribute('data-action') === actionName) {
					settings.style.removeProperty('display');
				}
				else {
					settings.style.setProperty('display', 'none');
				}
			}
		};
		
		var actions = document.querySelectorAll('input[name=action]');
		for (var i = 0, length = actions.length; i < length; i++) {
			actions[i].addEventListener('change', toggleActionOptions);
		}
	});
</script>

<header class="boxHeadline">
	<h1>{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}{/lang}</h1>
</header>

{include file='formError'}

<p class="warning">{hascontent}{content}{lang __optional=true}{$objectType->getProcessor()->getLanguageItemPrefix()}.warning{/lang}{/content}{hascontentelse}{lang}wcf.global.bulkProcessing.warning{/lang}{/hascontent}</p>

{if $success|isset}
	<p class="success">{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.success{/lang}</p>
{/if}

<form method="post" action="{link controller=$controller}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.action{/lang}</legend>
			
			<dl>
				<dt></dt>
				<dd>
					{foreach from=$actions item=actionObjectType}
						<label><input type="radio" name="action" value="{@$actionObjectType->action}" {if $actionObjectType->action == $action}checked="checked" {/if}/> {lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.{@$actionObjectType->action}{/lang}</label>
					{/foreach}
					
					{if $errorField == 'action'}
						<small class="innerError">
							{lang}wcf.global.form.error.{@$errorType}{/lang}
						</small>
					{/if}
				</dd>
			</dl>
		</fieldset>
		
		{foreach from=$actions item=actionObjectType}
			{if $actionObjectType->getProcessor()->getHTML()}
				<fieldset class="jsBulkProcessingActionSettings" data-action="{@$actionObjectType->action}" {if $actionObjectType->action != $action}style="display: none;"{/if}>
					<legend>{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.{@$actionObjectType->action}{/lang}</legend>
					
					{@$actionObjectType->getProcessor()->getHTML()}
				</fieldset>
			{/if}
		{/foreach}
	</div>
	
	<header class="boxHeadline boxSubHeadline">
		<h2>{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.conditions{/lang}</h2>
		{hascontent}<small>{content}{lang __optional=true}{$objectType->getProcessor()->getLanguageItemPrefix()}.conditions.descriptions{/lang}{/content}</small>{/hascontent}
	</header>
	
	{@$objectType->getProcessor()->getConditionHTML()}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
