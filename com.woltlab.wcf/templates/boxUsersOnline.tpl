<ul class="inlineList dotSeparated">
	<li>{lang}wcf.user.usersOnline.detail{/lang}</li>
	{if USERS_ONLINE_RECORD}<li>{lang}wcf.user.usersOnline.record{/lang}</li>{/if}
</ul>

{if $usersOnlineList|count}
	<ul class="inlineList commaSeparated">
		{foreach from=$usersOnlineList->getObjects() item=userOnline}
			<li><a href="{link controller='User' object=$userOnline->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{@$userOnline->getFormattedUsername()}</a></li>
		{/foreach}
	</ul>
{/if}

{if USERS_ONLINE_ENABLE_LEGEND && $usersOnlineList->getUsersOnlineMarkings()|count}
	<dl class="plain inlineDataList usersOnlineLegend">
		<dt>{lang}wcf.user.usersOnline.marking.legend{/lang}</dt>
		<dd>
			<ul class="inlineList commaSeparated">
				{foreach from=$usersOnlineList->getUsersOnlineMarkings() item=usersOnlineMarking}
					<li>{@$usersOnlineMarking}</li>
				{/foreach}
			</ul>
		</dd>
	
	</dl>
{/if}
