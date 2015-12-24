{if !$usersOnlineShowRecord|isset}{assign var='usersOnlineShowRecord' value=true}{/if}
{if MODULE_USERS_ONLINE && $__wcf->session->getPermission('user.profile.canViewUsersOnlineList') && $usersOnlineList->stats[total]}
	<section class="box">
		<h2 class="boxTitle"><a href="{link controller='UsersOnlineList'}{/link}">{lang}wcf.user.usersOnline{/lang}</a> <span class="badge">{#$usersOnlineList->stats[total]}</span></h2>
		
		<div class="boxContent">
			<p>{lang}wcf.user.usersOnline.detail{/lang}{if $usersOnlineShowRecord && USERS_ONLINE_RECORD} - {lang}wcf.user.usersOnline.record{/lang}{/if}</p>
			
			{if $usersOnlineList|count}
				<ul class="dataList">
					{foreach from=$usersOnlineList->getObjects() item=userOnline}
						<li><a href="{link controller='User' object=$userOnline->getDecoratedObject()}{/link}" class="userLink" data-user-id="{@$userOnline->userID}">{@$userOnline->getFormattedUsername()}</a></li>
					{/foreach}
				</ul>
			{/if}
			
			{if USERS_ONLINE_ENABLE_LEGEND && $usersOnlineList->getUsersOnlineMarkings()|count}
				<div class="usersOnlineLegend">
					<p>{lang}wcf.user.usersOnline.marking.legend{/lang}:</p>
					<ul class="dataList">
						{foreach from=$usersOnlineList->getUsersOnlineMarkings() item=usersOnlineMarking}
							<li>{@$usersOnlineMarking}</li>
						{/foreach}
					</ul>
				</div>
			{/if}
		</div>
	</section>
{/if}
