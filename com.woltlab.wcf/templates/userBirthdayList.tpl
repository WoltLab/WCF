{if $users|count}
	<div class="container marginTop">
		<ol class="containerList jsGroupedUserList">
			{foreach from=$users item=user}
				<li data-object-id="{@$user->userID}">
					<div class="box48">
						<a href="{link controller='User' object=$user}{/link}" title="{$user->username}" class="framed">{@$user->getAvatar()->getImageTag(48)}</a>
						
						<div class="details userInformation">
							<div class="containerHeadline">
								<h3><a href="{link controller='User' object=$user}{/link}">{$user->username}</a>{if MODULE_USER_RANK}
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
							
							{include file='userInformationStatistics'}
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
{else}
	<p class="marginTop">{lang}wcf.global.noItems{/lang}</p>
{/if}