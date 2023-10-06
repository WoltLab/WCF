<div class="messageShareButtons jsMessageShareButtons jsOnly">
	{assign var='__share_buttons_providers' value="\n"|explode:SHARE_BUTTONS_PROVIDERS}
	
	<ul class="inlineList">
		{if 'Facebook'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" class="button messageShareProvider" title="{lang}wcf.message.share.facebook{/lang}" aria-label="{lang}wcf.message.share.facebook{/lang}" data-identifier="Facebook">
					{icon size=24 name='facebook' type='brand'}
				</button>
			</li>
		{/if}
		{if 'Twitter'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" class="button messageShareProvider" title="{lang}wcf.message.share.twitter{/lang}" aria-label="{lang}wcf.message.share.twitter{/lang}" data-identifier="Twitter">
					{icon size=24 name='x-twitter' type='brand'}
				</button>
			</li>
		{/if}
		{if 'Reddit'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" class="button messageShareProvider" title="{lang}wcf.message.share.reddit{/lang}" aria-label="{lang}wcf.message.share.reddit{/lang}" data-identifier="Reddit">
					{icon size=24 name='reddit' type='brand'}
				</button>
			</li>
		{/if}
		{if 'WhatsApp'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" class="button messageShareProvider" title="{lang}wcf.message.share.whatsApp{/lang}" aria-label="{lang}wcf.message.share.whatsApp{/lang}" data-identifier="WhatsApp">
					{icon size=24 name='whatsapp' type='brand'}
				</button>
			</li>
		{/if}
		{if 'LinkedIn'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" href="#" role="button" class="button messageShareProvider" title="{lang}wcf.message.share.linkedIn{/lang}" aria-label="{lang}wcf.message.share.linkedIn{/lang}" data-identifier="LinkedIn">
					{icon size=24 name='linkedin-in' type='brand'}
				</button>
			</li>
		{/if}
		{if 'Pinterest'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" class="button messageShareProvider" title="{lang}wcf.message.share.pinterest{/lang}" aria-label="{lang}wcf.message.share.pinterest{/lang}" data-identifier="Pinterest">
					{icon size=24 name='pinterest' type='brand'}
				</button>
			</li>
		{/if}
		{if 'XING'|in_array:$__share_buttons_providers}
			<li>
				<button type="button" class="button messageShareProvider" title="{lang}wcf.message.share.xing{/lang}" aria-label="{lang}wcf.message.share.xing{/lang}" data-identifier="XING">
					{icon size=24 name='xing' type='brand'}
				</button>
			</li>
		{/if}
		{event name='buttons'}
	</ul>
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Message/Share'], function(UiMessageShare) {
			UiMessageShare.init();
		});
	</script>
</div>
