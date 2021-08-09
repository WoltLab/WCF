{include file='userMenuSidebar'}

{capture assign='contentTitleBadge'}<span class="badge">{#$items}</span>{/capture}

{capture assign='contentInteractionPagination'}
	{pages print=true assign=pagesLinks controller='IgnoredUsers' link="pageNo=%d"}
{/capture}

{include file='header' __sidebarLeftHasMenu=true}

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList userList jsReloadPageWhenEmpty">
			{foreach from=$objects item=user}
				<li class="jsIgnoredUser" data-object-id="{@$user->getObjectID()}">
					<div class="box48">
						{user object=$user type='avatar48' ariaHidden='true' tabindex='-1'}
						
						<div class="details userInformation">
							{include file='userInformationHeadline'}
							
							<nav class="jsMobileNavigation buttonGroupNavigation">
								<ul class="buttonList iconList jsOnly">
									<li>
										<a class="pointer jsTooltip jsEditIgnoreButton" title="{lang}wcf.global.button.edit{/lang}">
											<span class="icon icon16 fa-pencil"></span>
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
	
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Ui/User/Ignore/List'], (Language, { UiUserIgnoreList }) => {
			Language.addObject({
				'wcf.user.button.ignore': '{jslang}wcf.user.button.ignore{/jslang}',
			});
			
			new UiUserIgnoreList();
		});
	</script>
{else}
	<p class="info" role="status">{lang}wcf.user.ignoredUsers.noUsers{/lang}</p>
{/if}

{include file='footer'}
