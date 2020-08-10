{hascontent}
	<nav class="jsMobileNavigation buttonGroupNavigation">
		<ul class="buttonList iconList">
			{content}
				{if $user->homepage && $user->homepage != 'http://'}
					<li><a class="jsTooltip" title="{lang}wcf.user.option.homepage{/lang}" {anchorAttributes url=$user->homepage appendClassname=false isUgc=true}><span class="icon icon16 fa-home"></span> <span class="invisible">{lang}wcf.user.option.homepage{/lang}</span></a></li>
				{/if}
				
				{if $user->userID != $__wcf->user->userID}
					{if $user->isAccessible('canViewEmailAddress')}
						<li><a class="jsTooltip" href="mailto:{@$user->getEncodedEmail()}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 fa-envelope-o"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>
					{/if}
				{/if}
				
				{if $__wcf->user->userID && $user->userID != $__wcf->user->userID}
					{if !$__wcf->getUserProfileHandler()->isIgnoredByUser($user->userID)}
						{if $__wcf->getUserProfileHandler()->isFollowing($user->userID)}
							<li class="jsOnly"><a href="#" data-following="1" data-object-id="{@$user->userID}" class="jsFollowButton jsTooltip" title="{lang}wcf.user.button.unfollow{/lang}"><span class="icon icon16 fa-minus"></span> <span class="invisible">{lang}wcf.user.button.unfollow{/lang}</span></a></li>
						{else}
							<li class="jsOnly"><a href="#" data-following="0" data-object-id="{@$user->userID}" class="jsFollowButton jsTooltip" title="{lang}wcf.user.button.follow{/lang}"><span class="icon icon16 fa-plus"></span> <span class="invisible">{lang}wcf.user.button.follow{/lang}</span></a></li>
						{/if}
					{/if}
					
					{if $__wcf->getUserProfileHandler()->isIgnoredUser($user->userID)}
						<li class="jsOnly"><a href="#" data-ignored="1" data-object-id="{@$user->userID}" class="jsIgnoreButton jsTooltip" title="{lang}wcf.user.button.unignore{/lang}"><span class="icon icon16 fa-circle-o"></span> <span class="invisible">{lang}wcf.user.button.unignore{/lang}</span></a></li>
					{else}
						<li class="jsOnly"><a href="#" data-ignored="0" data-object-id="{@$user->userID}" class="jsIgnoreButton jsTooltip" title="{lang}wcf.user.button.ignore{/lang}"><span class="icon icon16 fa-ban"></span> <span class="invisible">{lang}wcf.user.button.ignore{/lang}</span></a></li>
					{/if}
				{/if}
				
				{event name='buttons'}
			{/content}
		</ul>
	</nav>
{/hascontent}
