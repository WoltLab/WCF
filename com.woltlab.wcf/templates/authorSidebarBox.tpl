<fieldset>
	<legend class="invisible">{lang}wcf.user.author{/lang}</legend>
	
	<div class="box96 framed">
		{@$userProfile->getAvatar()->getImageTag(96)}
		
		<div>
			<div class="containerHeadline">
				<h3>{if $userProfile->userID}<a href="{link controller='User' object=$userProfile}{/link}" rel="author">{$userProfile->username}</a>{else}{$userProfile->username}{/if}</h3>
				{if MODULE_USER_RANK}
					<p>
						{if $userProfile->getUserTitle()}
							<span class="badge userTitleBadge{if $userProfile->getRank() && $userProfile->getRank()->cssClassName} {@$userProfile->getRank()->cssClassName}{/if}">{$userProfile->getUserTitle()}</span>
						{/if}
						{if $userProfile->getRank() && $userProfile->getRank()->rankImage}
							<span class="userRankImage">{@$userProfile->getRank()->getImage()}</span>
						{/if}
					</p>
				{/if}
			</div>
			
			{if $userProfile->userID}
				{include file='userInformationStatistics' user=$userProfile __userStatsClassname='dataList'}
			{/if}
		</div>
	</div>
</fieldset>