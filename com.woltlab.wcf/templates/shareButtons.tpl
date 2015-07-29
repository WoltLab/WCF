<div class="messageShareButtons jsOnly">
	<ul class="inlineList">
		<li class="jsShareFacebook">
			<a>
				<span class="icon icon32 icon-facebook-sign jsTooltip" title="{lang}wcf.message.share.facebook{/lang}"></span>
				<span class="invisible">{lang}wcf.message.share.facebook{/lang}</span>
			</a>
			<span class="badge" style="display: none">0</span>
		</li>
		<li class="jsShareTwitter">
			<a>
				<span class="icon icon32 icon-twitter-sign jsTooltip" title="{lang}wcf.message.share.twitter{/lang}"></span>
				<span class="invisible">{lang}wcf.message.share.twitter{/lang}</span>
			</a>
			<span class="badge" style="display: none">0</span>
		</li>
		<li class="jsShareGoogle">
			<a>
				<span class="icon icon32 icon-google-plus-sign jsTooltip" title="{lang}wcf.message.share.google{/lang}"></span>
				<span class="invisible">{lang}wcf.message.share.google{/lang}</span>
			</a>
			<span class="badge" style="display: none">0</span>
		</li>
		<li class="jsShareReddit">
			<a>
				<span class="icon icon32 fa-reddit-square jsTooltip" title="{lang}wcf.message.share.reddit{/lang}"></span>
				<span class="invisible">{lang}wcf.message.share.reddit{/lang}</span>
			</a>
			<span class="badge" style="display: none">0</span>
		</li>
		
		{event name='buttons'}
	</ul>
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.message.share.facebook': '{lang}wcf.message.share.facebook{/lang}',
				'wcf.message.share.google': '{lang}wcf.message.share.google{/lang}',
				'wcf.message.share.reddit': '{lang}wcf.message.share.reddit{/lang}',
				'wcf.message.share.twitter': '{lang}wcf.message.share.twitter{/lang}',
				'wcf.message.share.privacy': '{lang}wcf.message.share.privacy{/lang}'
			});
			var $privacySettings = { {implode from=$__wcf->getUser()->getSocialNetworkPrivacySettings() key=provider item=value}'{$provider}': {if $value}true{else}false{/if}{/implode} };
			new WCF.Message.Share.Page({if SHARE_BUTTONS_SHOW_COUNT}true{else}false{/if}, $privacySettings);
		});
		//]]>
	</script>
</div>
