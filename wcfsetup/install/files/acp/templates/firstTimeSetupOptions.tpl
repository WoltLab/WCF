{include file='header' pageTitle='wcf.acp.firstTimeSetup.options'}

{event name='javascriptInclude'}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});
	
	{event name='javascriptInit'}
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.firstTimeSetup.options{/lang}</h1>
	</div>
</header>

{if $success|isset}
	<p class="success">{lang}wcf.global.success.edit{/lang}</p>
{/if}

<div class="info">{lang}wcf.acp.firstTimeSetup.options.description{/lang}</div>

{include file='formError'}

<form method="post" action="{link controller='FirstTimeSetupOptions'}{/link}" enctype="multipart/form-data">
	{include file='optionFieldList' langPrefix='wcf.acp.option.'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" name="__submit" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
