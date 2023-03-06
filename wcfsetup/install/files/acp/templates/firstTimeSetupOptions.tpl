{include file='header' pageTitle='wcf.acp.option.firstTimeSetup'}

{event name='javascriptInclude'}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});
	
	{event name='javascriptInit'}
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.option.firstTimeSetup{/lang}</h1>
		<p class="contentHeaderDescription">{lang}wcf.acp.option.firstTimeSetup.description{/lang}</p>
	</div>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

{include file='formError'}

<form method="post" action="{link controller='FirstTimeSetupOptions'}{/link}" enctype="multipart/form-data">
	{include file='optionFieldList' langPrefix='wcf.acp.option.'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" name="__submit" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
