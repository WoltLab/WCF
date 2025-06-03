<ul class="acpDashboardBox__usersAwaitingApproval">
	{foreach from=$users item='user'}
		<li class="acpDashboardBox__usersAwaitingApproval__user">
			<div class="box24">
				<div class="acpDashboardBox__usersAwaitingApproval__avatar">
					{unsafe:$user->getAvatar()->getImageTag(24)}
				</div>

				<div>
					<a href="{link controller='UserEdit' id=$user->userID}{/link}"
						class="acpDashboardBox__usersAwaitingApproval__link" title="{lang}wcf.acp.user.edit{/lang}">
						{$user->username}
					</a>
					<div class="acpDashboardBox__usersAwaitingApproval__meta">
						{time time=$user->registrationDate}
					</div>
				</div>
			</div>
		</li>
	{/foreach}
</ul>

<div class="acpDashboardBox__cta">
	<a href="{link controller='UserQuickSearch' mode='pendingActivation'}{/link}" class="button small">
		{lang}wcf.global.button.showAll{/lang}
	</a>
</div>
