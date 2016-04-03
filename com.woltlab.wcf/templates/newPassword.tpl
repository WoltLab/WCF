{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.newPassword{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.user.newPassword{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='userNotice'}

{include file='formError'}

<form method="post" action="{link controller='NewPassword'}{/link}">
	<div class="section">
		<dl{if $errorField == 'userID'} class="formError"{/if}>
			<dt>
				<label for="userID">{lang}wcf.user.userID{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="userID" name="u" value="{@$userID}" required="required" class="medium" />
				{if $errorField == 'userID'}
					<small class="innerError">
						{lang}wcf.user.userID.error.{$errorType}{/lang}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'lostPasswordKey'} class="formError"{/if}>
			<dt>
				<label for="lostPasswordKey">{lang}wcf.user.lostPasswordKey{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="lostPasswordKey" name="k" value="{$lostPasswordKey}" required="required" class="medium" />
				{if $errorField == 'lostPasswordKey'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.lostPasswordKey.error.{$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
	
	{event name='sections'}
		
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

</body>
</html>
