{capture assign='pageTitle'}{lang}wcf.user.register.disclaimer{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.user.register.disclaimer{/lang}{/capture}

{include file='header' __disableAds=true}

{include file='formError'}

<form method="post" action="{link controller='Disclaimer'}{/link}">
	<div class="section htmlContent">
		{lang}wcf.user.register.disclaimer.text{/lang}
		
		{event name='sections'}
	</div>
	
	{if !$__wcf->user->userID}
		<div class="formSubmit">
			<input type="submit" name="accept" value="{lang}wcf.user.register.disclaimer.accept{/lang}" accesskey="s" />
			<a class="button" href="{link}{/link}">{lang}wcf.user.register.disclaimer.decline{/lang}</a>
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	{/if}
</form>

{include file='footer'}
