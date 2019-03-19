{if $unknownUser|isset}
	<p>{lang}wcf.user.unknownUser{/lang}</p>
{else}
	<div class="box128 userProfilePreview">
		<a href="{link controller='User' object=$user}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag(128)}</a>
		
		<div class="userInformation">
			{include file='userInformation' disableDialogLinks=true}
			
			{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
				<dl class="plain inlineDataList">
					<dt>{lang}wcf.user.usersOnline.lastActivity{/lang}</dt>
					<dd>{@$user->getLastActivityTime()|time}{if $user->getCurrentLocation()}, {@$user->getCurrentLocation()}{/if}</dd>
				</dl>
			{/if}
			
			{hascontent}
				<dl class="plain inlineDataList userFields">
					{content}
						{if $__wcf->getSession()->getPermission('user.profile.canViewUserProfile') && $user->isAccessible('canViewProfile')}
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
		
		{if $__wcf->getUser()->userID && $__wcf->getUser()->userID != $user->userID}
			<script data-relocate="true">
				$(function() {
					WCF.Language.addObject({
						'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
						'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
						'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
						'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
					});
					
					new WCF.User.Action.Follow($('.userInformation'));
					
					{if !$user->getPermission('user.profile.cannotBeIgnored')}
						new WCF.User.Action.Ignore($('.userInformation'));
					{/if}
				});
			</script>
		{/if}
	</div>
{/if}
