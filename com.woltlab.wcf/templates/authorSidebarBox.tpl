<div class="box">
	<div class="boxContent box96">
		{@$userProfile->getAvatar()->getImageTag(96)}
		
		<div>
			<div class="containerHeadline">
				<h3>{if $userProfile->userID}<a href="{link controller='User' object=$userProfile}{/link}" rel="author">{$userProfile->username}</a>{else}{$userProfile->username}{/if}</h3>
				{if MODULE_USER_RANK}
					{if $userProfile->getUserTitle()}
						<p><span class="badge userTitleBadge{if $userProfile->getRank() && $userProfile->getRank()->cssClassName} {@$userProfile->getRank()->cssClassName}{/if}">{$userProfile->getUserTitle()}</span></p>
					{/if}
					{if $userProfile->getRank() && $userProfile->getRank()->rankImage}
						<p><span class="userRankImage">{@$userProfile->getRank()->getImage()}</span></p>
					{/if}
				{/if}
			</div>
			
			{if $userProfile->userID}
				<dl class="plain dataList containerContent small">
					{include file='userInformationStatistics' user=$userProfile}
				</dl>
			{/if}
		</div>
	</div>
</div>