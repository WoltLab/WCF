<fieldset>
	<legend class="invisible">{lang}wcf.user.avatar{/lang}</legend>
	
	<div class="userAvatar">
		<span class="framed">{@$user->getAvatar()->getImageTag()}</span>
	</div>
</fieldset>
	
{event name='boxes'}