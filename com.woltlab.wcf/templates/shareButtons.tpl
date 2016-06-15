<div class="messageShareButtons jsOnly">
	{assign var='__share_buttons_providers' value="\n"|explode:SHARE_BUTTONS_PROVIDERS}
	
	<ul class="inlineList">
		{if 'Facebook'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareFacebook" title="{lang}wcf.message.share.facebook{/lang}">
					<span class="icon icon24 fa-facebook-official"></span>
					<span>{lang}wcf.message.share.facebook{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Twitter'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareTwitter" title="{lang}wcf.message.share.twitter{/lang}">
					<span class="icon icon24 fa-twitter"></span>
					<span>{lang}wcf.message.share.twitter{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Google'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareGoogle" title="{lang}wcf.message.share.google{/lang}">
					<span class="icon icon24 fa-google-plus"></span>{*@todo: change to fa-google-plus-official (fa 4.6)*}
					<span>{lang}wcf.message.share.google{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Reddit'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareReddit" title="{lang}wcf.message.share.reddit{/lang}">
					<span class="icon icon24 fa-reddit"></span>
					<span>{lang}wcf.message.share.reddit{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'WhatsApp'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareWhatsApp" title="{lang}wcf.message.share.whatsApp{/lang}">
					<span class="icon icon24 fa-whatsapp jsTooltip"></span>
					<span>{lang}wcf.message.share.whatsApp{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'LinkedIn'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareLinkedIn" title="{lang}wcf.message.share.linkedIn{/lang}">
					<span class="icon icon24 fa-linkedin jsTooltip"></span>
					<span>{lang}wcf.message.share.linkedIn{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'Pinterest'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsSharePinterest" title="{lang}wcf.message.share.pinterest{/lang}">
					<span class="icon icon24 fa-pinterest-p jsTooltip"></span>
					<span>{lang}wcf.message.share.pinterest{/lang}</span>
				</a>
			</li>
		{/if}
		{if 'XING'|in_array:$__share_buttons_providers}
			<li>
				<a class="button jsShareXing" title="{lang}wcf.message.share.xing{/lang}">
					<span class="icon icon24 fa-xing jsTooltip"></span>
					<span>{lang}wcf.message.share.xing{/lang}</span>
				</a>
			</li>
		{/if}
		{event name='buttons'}
	</ul>
	
	<script data-relocate="true">
		require(['WoltLab/WCF/Ui/Message/Share'], function(UiMessageShare) {
			UiMessageShare.init();
		});
	</script>
</div>
