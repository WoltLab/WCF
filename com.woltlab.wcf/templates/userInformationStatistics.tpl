{event name='statistics'}

{if MODULE_LIKE && $user->likesReceived}
	<dt>{if $__wcf->getSession()->getPermission('user.profile.canViewUserProfile') && !$user->isProtected()}<a href="{link controller='User' object=$user}{/link}#likes" class="jsTooltip" title="{lang}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a>{else}{lang}wcf.like.likesReceived{/lang}{/if}</dt>
	<dd>{#$user->likesReceived}</dd>
{/if}

{if $user->activityPoints}
	<dt><a href="#" class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$user->userID}">{lang}wcf.user.activityPoint{/lang}</a></dt>
	<dd>{#$user->activityPoints}</dd>
{/if}

{if MODULE_TROPHY && $__wcf->session->getPermission('user.profile.trophy.canSeeTrophies') && $user->trophyPoints && ($user->isAccessible('canViewTrophies') || $user->userID == $__wcf->session->userID)}
	<dt><a href="#" class="trophyPoints jsTooltip userTrophyOverlayList" data-user-id="{$user->userID}" title="{lang}wcf.user.trophy.showTrophies{/lang}">{lang}wcf.user.trophy.trophyPoints{/lang}</a></dt>
	<dd>{#$user->trophyPoints}</dd>
{/if}
