{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.search{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Search.User('#searchUsername', null, false, [ ], false);
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{capture assign='sidebarRight'}
	{@$__boxSidebar}
{/capture}

{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.user.search{/lang}</h1>
</header>

{include file='userNotice'}

{if $errorField == 'search'}
	<p class="error">{lang}wcf.user.search.error.noMatches{/lang}</p>
{else}
	{include file='formError'}
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

<form method="post" action="{link controller='UserSearch'}{/link}">
	<div class="section">
		<dl>
			<dt><label for="searchUsername">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="searchUsername" name="username" value="{$username}" class="medium" />
			</dd>
		</dl>
		
		{event name='generalFields'}
	</div>
	
	{if !$optionTree|empty}
		{foreach from=$optionTree[0][categories] item=category}
			<section class="section">
				<header class="sectionHeader">
					<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$category[object]->categoryName}{/lang}</h2>
					{hascontent}<p class="sectionDescription">{content}{lang __optional=true}wcf.user.option.category.{@$category[object]->categoryName}.description{/lang}{/content}</p>{/hascontent}
				</header>
				{include file='userOptionFieldList' options=$category[options] langPrefix='wcf.user.option.' isSearchMode=true}
			</section>
		{/foreach}
	{/if}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

</body>
</html>
