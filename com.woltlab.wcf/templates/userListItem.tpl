<li data-object-id="{@$user->userID}">
	<div class="box48">
		<a href="{link controller='User' object=$user}{/link}" title="{$user->username}" aria-hidden="true">{@$user->getAvatar()->getImageTag(48)}</a>
		
		<div class="details userInformation">
			{include file='userInformation'}
		</div>
	</div>
</li>