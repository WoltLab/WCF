{include file='header' pageTitle='wcf.acp.firstTimeSetup.optionsEmail'}

{event name='javascriptInclude'}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});
	
	{event name='javascriptInit'}
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.firstTimeSetup.optionsEmail{/lang}</h1>
	</div>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

{include file='formError'}

<form method="post" action="{link controller='FirstTimeSetupOptionsEmail'}{/link}" enctype="multipart/form-data">
	{include file='optionFieldList' langPrefix='wcf.acp.option.'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" name="__submit" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
