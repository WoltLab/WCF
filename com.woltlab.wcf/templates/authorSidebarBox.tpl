<fieldset>
	<legend class="invisible">{lang}wcf.user.author{/lang}</legend>
	
	<div class="box96 framed">
		{@$userProfile->getAvatar()->getImageTag(96)}
		
		<div>
			<div class="containerHeadline">
				<h3><a href="{link controller='User' object=$userProfile}{/link}" rel="author">{$userProfile->username}</a></h3>
				{if MODULE_USER_RANK && $userProfile->getUserTitle()}<p><span class="badge userTitleBadge{if $userProfile->getRank() && $userProfile->getRank()->cssClassName} {@$userProfile->getRank()->cssClassName}{/if}">{$userProfile->getUserTitle()}</span></p>{/if}
			</div>
			
			{include file='userInformationStatistics' user=$userProfile}
		</div>
	</div>
</fieldset>