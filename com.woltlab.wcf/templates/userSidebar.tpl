<fieldset>
	<legend class="invisible">{lang}wcf.user.avatar{/lang}</legend>
	
	<div class="userAvatar">
		{if $user->userID == $__wcf->user->userID}
			<a href="{link controller='AvatarEdit'}{/link}" class="framed jsTooltip" title="{lang}wcf.user.avatar.edit{/lang}">{@$user->getAvatar()->getImageTag()}</a>
		{else}
			<span class="framed">{@$user->getAvatar()->getImageTag()}</span>
		{/if}
	</div>
</fieldset>

<fieldset>
	<legend class="invisible">{lang}wcf.user.stats{/lang}</legend>
	
	<dl class="plain statsDataList">
		{event name='statistics'}
		
		<dt>{if $user->activityPoints}<a class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showDetails{/lang}" data-user-id="{@$user->userID}">{lang}wcf.user.activityPoint{/lang}</a>{else}{lang}wcf.user.activityPoint{/lang}{/if}</dt>
		<dd>{#$user->activityPoints}</dd>
		
		{if MODULE_LIKE}
			<dt>{lang}wcf.like.likesReceived{/lang}</dt>
			<dd>{#$user->likesReceived}</dd>
		{/if}
		
		<dt>{lang}wcf.user.profileHits{/lang}</dt>
		<dd{if $user->getProfileAge() > 1} title="{lang}wcf.user.profileHits.hitsPerDay{/lang}"{/if}>{#$user->profileHits}</dd>
	</dl>
</fieldset>

{if $followingCount}
	<fieldset>
		<legend>{lang}wcf.user.profile.following{/lang} <span class="badge">{#$followingCount}</span></legend>
		
		<div>
			<ul class="framedIconList">
				{foreach from=$following item=followingUser}
					<li><a href="{link controller='User' object=$followingUser}{/link}" title="{$followingUser->username}" class="framed jsTooltip">{@$followingUser->getAvatar()->getImageTag(48)}</a></li>
				{/foreach}
			</ul>
			
			{if $followingCount > 10}
				<a id="followingAll" class="button small more jsOnly">{lang}wcf.user.profile.userList.showAll{/lang}</a>
			{/if}
		</div>
	</fieldset>
{/if}

{if $followerCount}
	<fieldset>
		<legend>{lang}wcf.user.profile.followers{/lang} <span class="badge">{#$followerCount}</span></legend>
		
		<div>
			<ul class="framedIconList">
				{foreach from=$followers item=follower}
					<li><a href="{link controller='User' object=$follower}{/link}" title="{$follower->username}" class="framed jsTooltip">{@$follower->getAvatar()->getImageTag(48)}</a></li>
				{/foreach}
			</ul>
				
			{if $followerCount > 10}
				<a id="followerAll" class="button small more jsOnly">{lang}wcf.user.profile.userList.showAll{/lang}</a>
			{/if}
		</div>
	</fieldset>
{/if}

{if $visitorCount}
	<fieldset>
		<legend>{lang}wcf.user.profile.visitors{/lang} <span class="badge">{#$visitorCount}</span></legend>
		
		<div>
			<ul class="framedIconList">
				{foreach from=$visitors item=visitor}
					<li><a href="{link controller='User' object=$visitor}{/link}" title="{$visitor->username} ({@$visitor->time|plainTime})" class="framed jsTooltip">{@$visitor->getAvatar()->getImageTag(48)}</a></li>
				{/foreach}
			</ul>
				
			{if $visitorCount > 10}
				<a id="visitorAll" class="button small more jsOnly">{lang}wcf.user.profile.userList.showAll{/lang}</a>
			{/if}
		</div>
	</fieldset>
{/if}

{event name='boxes'}
