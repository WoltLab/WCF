{include file='documentHeader'}

<head>
	<title>{lang}wcf.page.privacyPolicy{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl_{$templateNameApplication}_{$templateName}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header'}

<header class="boxHeadline">
	<h1>{lang}wcf.page.privacyPolicy{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<div class="container containerPadding marginTop htmlContent">
	{lang}wcf.page.privacyPolicy.text{/lang}
	
	{event name='fieldsets'}
</div>
	
<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}

</body>
</html>
