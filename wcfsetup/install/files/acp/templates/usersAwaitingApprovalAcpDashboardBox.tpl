<ul>
	{foreach from=$users item='user'}
		<li>
			<div>
				{@$user->getAvatar()->getImageTag(24)}
			</div>
			<div>
				<a title="{lang}wcf.acp.user.edit{/lang}" href="{link controller='UserEdit' id=$user->userID}{/link}">{$user->username}</a>
			</div>
		</li>
	{/foreach}
</ul>

<a href="{link controller='UserQuickSearch'}mode=pendingActivation{/link}" class="button small">More</a>
