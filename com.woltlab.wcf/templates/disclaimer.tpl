{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.register.disclaimer{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header' __disableAds=true}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.user.register.disclaimer{/lang}</h1>
</header>

{include file='userNotice'}

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

</body>
</html>
