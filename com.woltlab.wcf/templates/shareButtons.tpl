<div class="messageShareButtons jsMessageShareButtons jsOnly">
	{assign var='__share_buttons_providers' value="\n"|explode:SHARE_BUTTONS_PROVIDERS}
	
	<ul class="inlineList">
		{if 'Facebook'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareFacebook" title="{lang}wcf.message.share.facebook{/lang}" aria-label="{lang}wcf.message.share.facebook{/lang}">
					<span class="icon icon24 fa-facebook-official"></span>
					<span>{lang}wcf.message.share.facebook{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Twitter'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareTwitter" title="{lang}wcf.message.share.twitter{/lang}" aria-label="{lang}wcf.message.share.twitter{/lang}">
					<span class="icon icon24 fa-twitter"></span>
					<span>{lang}wcf.message.share.twitter{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Google'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareGoogle" title="{lang}wcf.message.share.google{/lang}" aria-label="{lang}wcf.message.share.google{/lang}">
					<span class="icon icon24 fa-google-plus-official"></span>
					<span>{lang}wcf.message.share.google{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Reddit'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareReddit" title="{lang}wcf.message.share.reddit{/lang}" aria-label="{lang}wcf.message.share.reddit{/lang}">
					<span class="icon icon24 fa-reddit"></span>
					<span>{lang}wcf.message.share.reddit{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'WhatsApp'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareWhatsApp" title="{lang}wcf.message.share.whatsApp{/lang}" aria-label="{lang}wcf.message.share.whatsApp{/lang}">
					<span class="icon icon24 fa-whatsapp jsTooltip"></span>
					<span>{lang}wcf.message.share.whatsApp{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'LinkedIn'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareLinkedIn" title="{lang}wcf.message.share.linkedIn{/lang}" aria-label="{lang}wcf.message.share.linkedIn{/lang}">
					<span class="icon icon24 fa-linkedin jsTooltip"></span>
					<span>{lang}wcf.message.share.linkedIn{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Pinterest'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsSharePinterest" title="{lang}wcf.message.share.pinterest{/lang}" aria-label="{lang}wcf.message.share.pinterest{/lang}">
					<span class="icon icon24 fa-pinterest-p jsTooltip"></span>
					<span>{lang}wcf.message.share.pinterest{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'XING'|in_array:$__share_buttons_providers}
			<li>
				<a href="#" role="button" class="button jsShareXing" title="{lang}wcf.message.share.xing{/lang}" aria-label="{lang}wcf.message.share.xing{/lang}">
					<span class="icon icon24 fa-xing jsTooltip"></span>
					<span>{lang}wcf.message.share.xing{/lang}</span>
				</a>
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
