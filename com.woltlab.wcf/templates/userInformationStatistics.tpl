{if !$disableDialogLinks|isset}{assign var=disableDialogLinks value=false}{/if}

{event name='statistics'}

{if MODULE_LIKE && $user->likesReceived}
	<dt>{if $__wcf->getSession()->getPermission('user.profile.canViewUserProfile') && !$user->isProtected()}<a href="{link controller='User' object=$user}{/link}#likes" class="jsTooltip" title="{lang}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a>{else}{lang}wcf.like.likesReceived{/lang}{/if}</dt>
	<dd>{#$user->likesReceived}</dd>
{/if}

{if $user->activityPoints}
	<dt>{if $disableDialogLinks}<span>{lang}wcf.user.activityPoint{/lang}</span>{else}<a href="#" class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$user->userID}">{lang}wcf.user.activityPoint{/lang}</a>{/if}</dt>
	<dd>{#$user->activityPoints}</dd>
{/if}
