{include file='header' pageTitle=$objectType->getProcessor()->getLanguageItemPrefix()}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/TabMenu'], function(UiTabMenu) {
		UiTabMenu.setup();
		
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
{if $bulkProcessingID|isset}
	<script data-relocate="true">
		{jsphrase name='wcf.acp.worker.abort.confirmMessage'}
        require(["WoltLabSuite/Core/Acp/Ui/Worker"], (AcpUiWorker) => {
          new AcpUiWorker({
            dialogId: "bulkProcessing",
            dialogTitle: '{jslang}{$pageTitle}{/jslang}',
            className: "wcf\\system\\worker\\BulkProcessingWorker",
            parameters: {
              bulkProcessingID: {@$bulkProcessingID},
            },
          });
        });
	</script>
{/if}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}{/lang}</h1>
</header>

{include file='formError'}

<woltlab-core-notice type="warning">
	{hascontent}{content}{lang __optional=true}{$objectType->getProcessor()->getLanguageItemPrefix()}.warning{/lang}{/content}{hascontentelse}{lang}wcf.global.bulkProcessing.warning{/lang}{/hascontent}
</woltlab-core-notice>

{if $success|isset}
	<woltlab-core-notice type="success">{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.success{/lang}</woltlab-core-notice>
{/if}

<form id="formContainer" method="post" action="{link controller=$controller application=$controllerApplication}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.action{/lang}</h2>
		
		<dl>
			<dt></dt>
			<dd>
				{foreach from=$actions item=actionObjectType}
					<label><input type="radio" name="action" value="{$actionObjectType->action}"{if $actionObjectType->action == $action} checked{/if}> {lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.{@$actionObjectType->action}{/lang}</label>
				{/foreach}
				
				{if $errorField == 'action'}
					<small class="innerError">
						{lang}wcf.global.form.error.{@$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
	
	{foreach from=$actions item=actionObjectType}
		{if $actionObjectType->getProcessor()->getHTML()}
			<section class="section jsBulkProcessingActionSettings" data-action="{@$actionObjectType->action}" {if $actionObjectType->action != $action}style="display: none;"{/if}>
				<h2 class="sectionTitle">{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.{@$actionObjectType->action}{/lang}</h2>
				
				{@$actionObjectType->getProcessor()->getHTML()}
			</section>
		{/if}
	{/foreach}
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}{$objectType->getProcessor()->getLanguageItemPrefix()}.conditions{/lang}</h2>
			{hascontent}<p class="sectionDescription">{content}{lang __optional=true}{$objectType->getProcessor()->getLanguageItemPrefix()}.conditions.descriptions{/lang}{/content}</p>{/hascontent}
		</header>
		
		{@$objectType->getProcessor()->getConditionHTML()}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
