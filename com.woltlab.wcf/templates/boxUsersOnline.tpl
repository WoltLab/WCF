<ul class="inlineList dotSeparated">
	<li>{lang}wcf.user.usersOnline.detail{/lang}</li>
	{if USERS_ONLINE_RECORD && $__showRecord}<li>{lang}wcf.user.usersOnline.record{/lang}</li>{/if}
</ul>

{if $usersOnlineList|count}
	<ul class="inlineList commaSeparated">
		{foreach from=$usersOnlineList->getObjects() item=userOnline}
			<li>{user object=$userOnline}</li>
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
