{if !$usersOnlineShowRecord|isset}{assign var='usersOnlineShowRecord' value=true}{/if}
{if MODULE_USERS_ONLINE && $__wcf->session->getPermission('user.profile.canViewUsersOnlineList') && $usersOnlineList->stats[total]}
	<section class="box" data-static-box-identifier="com.woltlab.wcf.UsersOnlineInfo">
		<h2 class="boxTitle"><a href="{link controller='UsersOnlineList'}{/link}">{lang}wcf.user.usersOnline{/lang}</a> <span class="badge">{#$usersOnlineList->stats[total]}</span></h2>
		
		<div class="boxContent">
			<ul class="inlineList dotSeparated">
				<li>{lang}wcf.user.usersOnline.detail{/lang}</li>
				{if $usersOnlineShowRecord && USERS_ONLINE_RECORD}<li>{lang}wcf.user.usersOnline.record{/lang}</li>{/if}
			</ul>
			
			{if $usersOnlineList|count}
				<ul class="inlineList commaSeparated">
					{foreach from=$usersOnlineList->getObjects() item=userOnline}
						<li><a href="{link controller='User' object=$userOnline->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{@$userOnline->getFormattedUsername()}</a></li>
					{/foreach}
				</ul>
			{/if}
		</div>
		
		{if USERS_ONLINE_ENABLE_LEGEND && $usersOnlineList->getUsersOnlineMarkings()|count}
			<div class="boxContent">
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
			</div>
		{/if}
	</section>
{/if}
