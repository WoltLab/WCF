{include file='documentHeader'}

<head>
	<title>{lang}wcf.tagging.taggedObjects.{@$objectType}{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='Tagged' object=$tag}objectType={@$objectType}&pageNo={@$pageNo+1}{/link}" />
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='Tagged' object=$tag}objectType={@$objectType}{if $pageNo > 2}&pageNo={@$pageNo-1}{/if}{/link}" />
	{/if}
	<link rel="canonical" href="{link controller='Tagged' object=$tag}objectType={@$objectType}{if $pageNo > 1}&pageNo={@$pageNo}{/if}{/link}" />
</head>

<body id="tpl{$templateName|ucfirst}">

{capture assign='sidebar'}
	<fieldset>
		<legend>{lang}wcf.tagging.objectTypes{/lang}</legend>
		
		<nav>
			<ul>
				{foreach from=$availableObjectTypes item=availableObjectType}
					<li{if $objectType == $availableObjectType->objectType} class="active"{/if}><a href="{link controller='Tagged' object=$tag}objectType={@$availableObjectType->objectType}{/link}">{lang}wcf.tagging.objectType.{@$availableObjectType->objectType}{/lang}</a></li>
				{/foreach}
			</ul>
		</nav>
	</fieldset>
	
	<fieldset>
		<legend>{lang}wcf.tagging.tags{/lang}</legend>
		
		{include file='tagCloudBox' taggableObjectType=$objectType}
	</fieldset>
{/capture}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.tagging.taggedObjects.{@$objectType}{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='Tagged' object=$tag link="objectType=$objectType&pageNo=%d"}
	
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

{if $items}
	{include file=$resultListTemplateName application=$resultListApplication}
{else}
	<p class="info">{lang}wcf.tagging.taggedObjects.noResults{/lang}</p>
{/if}

<div class="contentNavigation">
	{@$pagesLinks}
	
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