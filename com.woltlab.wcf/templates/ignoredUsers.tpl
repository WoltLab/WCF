{include file='userMenuSidebar'}

{capture assign='contentTitleBadge'}<span class="badge">{#$items}</span>{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination page="{$pageNo}" count="{$pages}" url="{link controller='IgnoredUsers'}{/link}"></woltlab-core-pagination>
	{/if}
{/capture}

{include file='header' __sidebarLeftHasMenu=true}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList userList jsReloadPageWhenEmpty">
			{foreach from=$objects item=user}
				<li class="jsIgnoredUser" data-object-id="{$user->getObjectID()}">
					<div class="box48">
						{user object=$user type='avatar48' ariaHidden='true' tabindex='-1'}
						
						<div class="details userInformation">
							{include file='userInformationHeadline'}
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li>
										<a class="pointer jsTooltip jsEditIgnoreButton" title="{lang}wcf.global.button.edit{/lang}">
											{icon name='pencil'}
											<span class="invisible">{lang}wcf.global.button.edit{/lang}</span>
										</a>
									</li>
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
				<woltlab-core-pagination page="{$pageNo}" count="{$pages}" url="{link controller='IgnoredUsers'}{/link}"></woltlab-core-pagination>
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
	
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Ui/User/Ignore/List'], (Language, { UiUserIgnoreList }) => {
			Language.addObject({
				'wcf.user.button.ignore': '{jslang}wcf.user.button.ignore{/jslang}',
			});
			
			new UiUserIgnoreList();
		});
	</script>
{else}
	<woltlab-core-notice type="info">{lang}wcf.user.ignoredUsers.noUsers{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
