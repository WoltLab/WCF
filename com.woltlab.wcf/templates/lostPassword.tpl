{include file="documentHeader"}

<head>
	<title>{lang}wcf.user.lostPassword{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.User.Registration.LostPassword();
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.user.lostPassword{/lang}</h1>
</header>

{include file='userNotice'}

<p class="info">{lang}wcf.user.lostPassword.description{/lang}</p>

{include file='formError'}

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
	<div class="section">
		<dl id="usernameDiv"{if $errorField == 'username'} class="formError"{/if}>
			<dt>
				<label for="usernameInput">{lang}wcf.user.username{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="usernameInput" name="username" value="{$username}" class="medium" />
				{if $errorField == 'username'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						{if $errorType == 'notFound'}{lang}wcf.user.username.error.notFound{/lang}{/if}
						{if $errorType == '3rdParty'}{lang}wcf.user.username.error.3rdParty{/lang}{/if}
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
						{if $errorType == 'notFound'}{lang}wcf.user.lostPassword.email.error.notFound{/lang}{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
	
	{event name='sections'}
	
	{include file='captcha'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

</body>
</html>
