<div class="messageShareButtons jsOnly">
	<ul>
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
				<img class="jsTooltip" src="{$__wcf->getPath()}icon/reddit.png" alt="{lang}wcf.message.share.reddit{/lang}" title="{lang}wcf.message.share.reddit{/lang}" />
				<span class="invisible">{lang}wcf.message.share.reddit{/lang}</span>
			</a>
			<span class="badge" style="display: none">0</span>
		</li>
		
		{event name='buttons'}
	</ul>
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.message.share.facebook': '{lang}wcf.message.share.facebook{/lang}',
				'wcf.message.share.google': '{lang}wcf.message.share.google{/lang}',
				'wcf.message.share.reddit': '{lang}wcf.message.share.reddit{/lang}',
				'wcf.message.share.twitter': '{lang}wcf.message.share.twitter{/lang}'
			});
			
			new WCF.Message.Share.Page({if SHARE_BUTTONS_SHOW_COUNT}true{else}false{/if});
		});
		//]]>
	</script>
</div>
