{if $users|count}
	<div class="section sectionContainerList">
		<ol class="containerList jsGroupedUserList">
			{foreach from=$users item=user}
				<li data-object-id="{@$user->userID}">
					<div class="box48">
						{user object=$user type='avatar48' title=$user->username ariaHidden='true'}
						
						<div class="details userInformation">
							<div class="containerHeadline">
								<h3>{user object=$user}{if MODULE_USER_RANK}
									{if $user->getUserTitle()}
										<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
									{/if}
									{if $user->getRank() && $user->getRank()->rankImage}
										<span class="userRankImage">{@$user->getRank()->getImage()}</span>
									{/if}
								{/if}</h3>
							</div>
							<ul class="dataList userFacts">
								<li>{$user->getBirthday($year)}</li>
							</ul>
							
							{include file='userInformationButtons'}
							
							<dl class="plain inlineDataList">
								{include file='userInformationStatistics'}
							</dl>
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
{else}
	<p class="info" role="status">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.user.button.follow': '{jslang}wcf.user.button.follow{/jslang}',
			'wcf.user.button.ignore': '{jslang}wcf.user.button.ignore{/jslang}',
			'wcf.user.button.unfollow': '{jslang}wcf.user.button.unfollow{/jslang}',
			'wcf.user.button.unignore': '{jslang}wcf.user.button.unignore{/jslang}'
		});
		
		new WCF.User.Action.Follow($('.jsGroupedUserList > li'));
		new WCF.User.Action.Ignore($('.jsGroupedUserList > li'));
	});
</script>
