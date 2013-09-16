{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.registerActivation{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.registerActivation{/lang}</h1>
</header>

{include file='userNotice'}

{if $__wcf->user->userID && $__wcf->user->activationCode}<p class="info">{lang}wcf.user.registerActivation.info{/lang}</p>{/if}

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

<form method="post" action="{link controller='RegisterActivation'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend><label for="userID">{lang}wcf.user.registerActivation{/lang}</label></legend>
			
			<dl{if $errorField == 'username'} class="formError"{/if}>
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{@$username}" required="required" class="medium" />
					{if $errorField == 'username'}
						<small class="innerError">
							{if $errorType == 'notFound'}{lang}wcf.user.username.error.notFound{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'activationCode'} class="formError"{/if}>
				<dt><label for="activationCode">{lang}wcf.user.activationCode{/lang}</label></dt>
				<dd>
					<input type="text" id="activationCode" maxlength="9" name="activationCode" value="{@$activationCode}" required="required" class="medium" />
					{if $errorField == 'activationCode'}
						<small class="innerError">
							{if $errorType == 'notValid'}{lang}wcf.user.activationCode.error.notValid{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='fields'}
			
			<dl>
				<dt></dt>
				<dd>
					<ul class="buttonList smallButtons">
						<li><a class="button small" href="{link controller='RegisterNewActivationCode'}{/link}"><span>{lang}wcf.user.newActivationCode{/lang}</span></a></li>
						{event name='buttons'}
					</ul>
				</dd>
			</dl>
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}
</body>
</html>
