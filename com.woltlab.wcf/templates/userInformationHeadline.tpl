<div class="containerHeadline">
	<h3><a href="{link controller='User' object=$user}{/link}">{$user->username}</a>{if MODULE_USER_RANK && $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}</h3> 
</div>
<ul class="dataList userFacts">
	{if $user->isAccessible('canViewProfile')}
		{if $user->gender}<li>{lang}wcf.user.gender.{if $user->gender == 1}male{else}female{/if}{/lang}</li>{/if}
		{if $user->getAge()}<li>{@$user->getAge()}</li>{/if}
		{if $user->location}<li>{lang}wcf.user.membersList.location{/lang}</li>{/if}
	{/if}
	<li>{lang}wcf.user.membersList.registrationDate{/lang}</li>
	
	{event name='userData'}
</ul>