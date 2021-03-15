{include file='userMenuSidebar'}

{capture assign='contentTitleBadge'}<span class="badge">{#$items}</span>{/capture}

{include file='header' __sidebarLeftHasMenu=true}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='IgnoredUsers' link="pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList userList jsReloadPageWhenEmpty">
			{foreach from=$objects item=user}
				<li class="jsIgnoredUser">
					<div class="box48">
						{user object=$user type='avatar48' ariaHidden='true' tabindex='-1'}
						
						<div class="details userInformation">
							{include file='userInformationHeadline'}
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li><a class="pointer jsTooltip jsDeleteButton" title="{lang}wcf.user.button.unignore{/lang}" data-object-id="{@$user->ignoreID}"><span class="icon icon16 fa-times"></span> <span class="invisible">{lang}wcf.user.button.unignore{/lang}</span></a></li>
									{event name='userButtons'}
								</ul>
							</nav>
							
							<dl class="plain inlineDataList small">
								{include file='userInformationStatistics'}
							</dl>
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info" role="status">{lang}wcf.user.ignoredUsers.noUsers{/lang}</p>
{/if}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\user\\ignore\\UserIgnoreAction', '.jsIgnoredUser');
	});
</script>

{include file='footer'}
