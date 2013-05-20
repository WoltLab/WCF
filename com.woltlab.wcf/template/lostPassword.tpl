{include file="documentHeader"}

<head>
	<title>{lang}wcf.user.lostPassword{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script type="text/javascript" src="{@$__wcf->getPath('wcf')}js/WCF.User{if !ENABLE_DEBUG_MODE}.min{/if}.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			new WCF.User.Registration.LostPassword();
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.lostPassword{/lang}</h1>
</header>

{include file='userNotice'}

<p class="info">{lang}wcf.user.lostPassword.description{/lang}</p>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='LostPassword'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.user.lostPassword{/lang}</legend>
			
			<dl id="usernameDiv"{if $errorField == 'username'} class="formError"{/if}>
				<dt>
					<label for="usernameInput">{lang}wcf.user.username{/lang}</label>
				</dt>
				<dd>
					<input type="text" id="usernameInput" name="username" value="{$username}" class="medium" />
					{if $errorField == 'username'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
							{if $errorType == '3rdParty'}{lang}wcf.user.error.username.3rdParty{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl id="emailDiv"{if $errorField == 'email'} class="formError"{/if}>
				<dt>
					<label for="emailInput">{lang}wcf.user.email{/lang}</label>
				</dt>
				<dd>
					<input type="email" id="emailInput" name="email" value="{$email}" class="medium" />
					{if $errorField == 'email'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'notFound'}{lang}wcf.user.lostPassword.error.email.notFound{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='fields'}
		</fieldset>
		
		{event name='fieldsets'}
		
		{if $useCaptcha}{include file='recaptcha'}{/if}
	</div>
		
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}

</body>
</html>
