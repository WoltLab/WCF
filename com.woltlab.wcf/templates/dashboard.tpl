{include file='documentHeader'}

<head>
	<title>{if $__wcf->getPageMenu()->getLandingPage()->menuItem != 'wcf.user.dashboard'}{lang}wcf.user.dashboard{/lang} - {/if}{PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='Dashboard'}{/link}" />
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{if $__boxSidebar|isset && $__boxSidebar}
	{capture assign='sidebar'}
		{@$__boxSidebar}
	{/capture}
{/if}

{include file='header' sidebarOrientation='right'}

{if $__wcf->getPageMenu()->getLandingPage()->menuItem == 'wcf.user.dashboard'}
	<header class="boxHeadline">
		<h1>{PAGE_TITLE|language}</h1>
		{hascontent}<p>{content}{PAGE_DESCRIPTION|language}{/content}</p>{/hascontent}
	</header>
{else}
	<header class="boxHeadline">
		<h1>{lang}wcf.user.dashboard{/lang}</h1>
	</header>
{/if}

{*TODO: remove dummy media manager dialog demonstration code later on*}
<p class="button" id="mediaManagerButton">click</p>

<script data-relocate="true">
	$(function() {
		require(['WoltLab/WCF/Media/Manager', 'Language', 'Permission'], function(MediaManager, Language, Permission) {
			Language.addObject({
				'wcf.global.button.insert': '{lang}wcf.global.button.insert{/lang}',
				
				'wcf.media.insert': '{lang}wcf.media.insert{/lang}',
				'wcf.media.insert.imageSize': '{lang}wcf.media.insert.imageSize{/lang}',
				'wcf.media.insert.imageSize.small': '{lang __literal=true}wcf.media.insert.imageSize.small{/lang}',
				'wcf.media.insert.imageSize.medium': '{lang __literal=true}wcf.media.insert.imageSize.medium{/lang}',
				'wcf.media.insert.imageSize.large': '{lang __literal=true}wcf.media.insert.imageSize.large{/lang}',
				'wcf.media.insert.imageSize.original': '{lang __literal=true}wcf.media.insert.imageSize.original{/lang}',
				'wcf.media.manager': '{lang}wcf.media.manager{/lang}',
				'wcf.media.edit': '{lang}wcf.media.edit{/lang}',
				'wcf.media.imageDimensions.value': '{lang __literal=true}wcf.media.imageDimensions.value{/lang}',
				'wcf.media.button.insert': '{lang}wcf.media.button.insert{/lang}',
				'wcf.media.search.filetype': '{lang}wcf.media.search.filetype{/lang}',
				'wcf.media.search.noResults': '{lang}wcf.media.search.noResults{/lang}'
			});
			
			Permission.add('admin.content.cms.canManageMedia', {if $__wcf->session->getPermission('admin.content.cms.canManageMedia')}true{else}false{/if});
			
			new MediaManager();
		});
	});
</script>
{* /end *}

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

<section id="dashboard">
	{if $__boxContent|isset}{@$__boxContent}{/if}
</section>

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
