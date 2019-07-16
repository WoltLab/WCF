{if !$disableDialogLinks|isset}{assign var=disableDialogLinks value=false}{/if}

{event name='statistics'}

{if MODULE_LIKE && $user->cumulativeLikes}
	<dt>{if $__wcf->getSession()->getPermission('user.profile.canViewUserProfile') && !$user->isProtected()}<a href="{link controller='User' object=$user}{/link}#likes" class="jsTooltip" title="{lang}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.reactionsReceived{/lang}</a>{else}{lang}wcf.like.reactionsReceived{/lang}{/if}</dt>
	<dd>{#$user->cumulativeLikes}</dd>
{/if}

{if $user->activityPoints}
	<dt>{if $disableDialogLinks}<span>{lang}wcf.user.activityPoint{/lang}</span>{else}<a href="#" class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$user->userID}">{lang}wcf.user.activityPoint{/lang}</a>{/if}</dt>
	<dd>{#$user->activityPoints}</dd>
{/if}

{if MODULE_TROPHY && $__wcf->session->getPermission('user.profile.trophy.canSeeTrophies') && $user->trophyPoints && ($user->isAccessible('canViewTrophies') || $user->userID == $__wcf->session->userID)}
	<dt>{if $disableDialogLinks}<span>{lang}wcf.user.trophy.trophyPoints{/lang}</span>{else}<a href="#" class="trophyPoints jsTooltip userTrophyOverlayList" data-user-id="{$user->userID}" title="{lang}wcf.user.trophy.showTrophies{/lang}">{lang}wcf.user.trophy.trophyPoints{/lang}</a>{/if}</dt>
	<dd>{#$user->trophyPoints}</dd>
{/if}
