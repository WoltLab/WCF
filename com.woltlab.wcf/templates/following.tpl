{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.following{/lang} - {lang}wcf.user.usercp{/lang} - {PAGE_TITLE|language}</title>
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\user\\follow\\UserFollowAction', '.jsFollowing');
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='userMenuSidebar'}

{include file='header'}

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.user.following{/lang} <span class="badge">{#$items}</span></h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='Following' link="pageNo=%d"}
	
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

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList userList">
			{foreach from=$objects item=user}
				<li class="jsFollowing">
					<div class="box48">
						<a href="{link controller='User' object=$user}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag(48)}</a>
							
						<div class="details userInformation">
							{include file='userInformationHeadline'}
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li><a class="pointer jsTooltip jsDeleteButton" title="{lang}wcf.user.button.unfollow{/lang}" data-object-id="{@$user->followID}"><span class="icon icon16 fa-times"></span> <span class="invisible">{lang}wcf.user.button.unfollow{/lang}</span></a></li>
									{event name='userButtons'}
								</ul>
							</nav>
							
							{include file='userInformationStatistics'}
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
	
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
{else}
	<p class="info">{lang}wcf.user.following.noUsers{/lang}</p>
{/if}

{include file='footer'}

</body>
</html>
