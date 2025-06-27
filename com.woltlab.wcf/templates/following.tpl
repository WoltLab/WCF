{include file='userMenuSidebar'}

{capture assign='contentTitleBadge'}<span class="badge">{#$items}</span>{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination
			page="{$pageNo}"
			count="{$pages}"
			url="{link controller='Following'}{/link}"
		></woltlab-core-pagination>
	{/if}
{/capture}

{include file='header' __sidebarLeftHasMenu=true}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList userList jsReloadPageWhenEmpty jsObjectActionContainer" data-object-action-class-name="wcf\data\user\follow\UserFollowAction">
			{foreach from=$objects item=user}
				<li class="jsFollowing jsObjectActionObject" data-object-id="{$user->getObjectID()}">
					<div class="box48">
						{user object=$user type='avatar48' ariaHidden='true' tabindex='-1'}
						
						<div class="details userInformation">
							{include file='userInformationHeadline'}
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li><a class="pointer jsTooltip jsObjectAction" data-object-action="delete" title="{lang}wcf.user.button.unfollow{/lang}" data-object-id="{$user->followID}">{icon name='xmark'} <span class="invisible">{lang}wcf.user.button.unfollow{/lang}</span></a></li>
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
		{if $pages > 1}
			<div class="paginationBottom">
				<woltlab-core-pagination
					page="{$pageNo}"
					count="{$pages}"
					url="{link controller='Following'}{/link}"
				></woltlab-core-pagination>
			</div>
		{/if}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.user.following.noUsers{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
