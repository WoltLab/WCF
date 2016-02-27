{include file='documentHeader'}

<head>
	<title>{lang}wcf.moderation.deletedContent.objectType.{@$objectType}{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{capture assign='sidebarLeft'}
	<section class="box">
		<h2 class="boxTitle">{lang}wcf.moderation.deletedContent.objectTypes{/lang}</h2>
		
		<div class="boxContent">
			<nav>
				<ul>
					{foreach from=$availableObjectTypes item=availableObjectType}
						<li{if $objectType == $availableObjectType->objectType} class="active"{/if}><a href="{link controller='DeletedContentList'}objectType={@$availableObjectType->objectType}{/link}">{lang}wcf.moderation.deletedContent.objectType.{@$availableObjectType->objectType}{/lang}</a></li>
					{/foreach}
				</ul>
			</nav>
		</div>
	</section>
{/capture}

{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.moderation.deletedContent.{@$objectType}{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='DeletedContentList' link="objectType=$objectType&pageNo=%d"}
	
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
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
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
	
	<nav class="jsClipboardEditor" data-types="[ '{@$objectType}' ]"></nav>
</div>

{include file='footer'}

</body>
</html>