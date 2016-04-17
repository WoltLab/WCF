{if $unknownUser|isset}
	<p>{lang}wcf.user.unknownUser{/lang}</p>
{else}
	<div class="box128 userProfilePreview">
		<a href="{link controller='User' object=$user}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag(128)}</a>
		
		<div class="userInformation">
			{include file='userInformation' disableUserInformationButtons=true}
			
			{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
				<dl class="plain inlineDataList userStats">
					<dt>{lang}wcf.user.usersOnline.lastActivity{/lang}</dt>
					<dd>{@$user->getLastActivityTime()|time}{if $user->getCurrentLocation()}, {@$user->getCurrentLocation()}{/if}</dd>
				</dl>
			{/if}
			
			{hascontent}
				<dl class="plain inlineDataList userFields">
					{content}
						{if $user->isAccessible('canViewProfile')}
							{if $user->occupation}
								<dt>{lang}wcf.user.option.occupation{/lang}</dt>
								<dd>{$user->occupation}</dd>
							{/if}
							{if $user->hobbies}
								<dt>{lang}wcf.user.option.hobbies{/lang}</dt>
								<dd>{$user->hobbies}</dd>
							{/if}
						{/if}
						{event name='userFields'}
					{/content}
				</dl>
			{/hascontent}
		</div>
	</div>
{/if}
