{hascontent}
	<nav class="jsMobileNavigation buttonGroupNavigation">
		<ul class="buttonList iconList">
			{content}
				{if $user->homepage && $user->homepage != 'http://'}
					<li><a class="jsTooltip" href="{$user->homepage}" title="{lang}wcf.user.option.homepage{/lang}"{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}><span class="icon icon16 icon-home"></span> <span class="invisible">{lang}wcf.user.option.homepage{/lang}</span></a></li>
				{/if}
				
				{if $user->userID != $__wcf->user->userID}
					{if $user->isAccessible('canViewEmailAddress')}
						<li><a class="jsTooltip" href="mailto:{@$user->getEncodedEmail()}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 icon-envelope-alt"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>
					{elseif $user->isAccessible('canMail') && $__wcf->session->getPermission('user.profile.canMail')}
						<li><a class="jsTooltip" href="{link controller='Mail' object=$user}{/link}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 icon-envelope-alt"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>
					{/if}
				{/if}
				
				{if $__wcf->user->userID && $user->userID != $__wcf->user->userID}
					{if $__wcf->getUserProfileHandler()->isFollowing($user->userID)}
						<li class="jsOnly"><a data-following="1" data-object-id="{@$user->userID}" class="jsFollowButton jsTooltip" title="{lang}wcf.user.button.unfollow{/lang}"><span class="icon icon16 icon-minus"></span> <span class="invisible">{lang}wcf.user.button.unfollow{/lang}</span></a></li>
					{else}
						<li class="jsOnly"><a data-following="0" data-object-id="{@$user->userID}" class="jsFollowButton jsTooltip" title="{lang}wcf.user.button.follow{/lang}"><span class="icon icon16 icon-plus"></span> <span class="invisible">{lang}wcf.user.button.follow{/lang}</span></a></li>
					{/if}
					
					{*if !$user->getPermission('user.profile.cannotBeIgnored')*}{*disabled for performance reasons*}
						{if $__wcf->getUserProfileHandler()->isIgnoredUser($user->userID)}
							<li class="jsOnly"><a data-ignored="1" data-object-id="{@$user->userID}" class="jsIgnoreButton jsTooltip" title="{lang}wcf.user.button.unignore{/lang}"><span class="icon icon16 icon-circle-blank"></span> <span class="invisible">{lang}wcf.user.button.unignore{/lang}</span></a></li>
						{else}
							<li class="jsOnly"><a data-ignored="0" data-object-id="{@$user->userID}" class="jsIgnoreButton jsTooltip" title="{lang}wcf.user.button.ignore{/lang}"><span class="icon icon16 icon-ban-circle"></span> <span class="invisible">{lang}wcf.user.button.ignore{/lang}</span></a></li>
						{/if}
					{*/if*}
				{/if}
				
				{event name='buttons'}
			{/content}
		</ul>
	</nav>
{/hascontent}