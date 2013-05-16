{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<style type="text/css">
	#news iframe {
		width: 100%;	
	}
</style>

<div class="tabMenuContainer" data-active="{if ENABLE_WOLTLAB_NEWS}news{else}credits{/if}" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			{if ENABLE_WOLTLAB_NEWS}<li><a href="{@$__wcf->getAnchor('news')}">{lang}wcf.acp.index.news{/lang}</a></li>{/if}
			<li><a href="{@$__wcf->getAnchor('credits')}">{lang}wcf.acp.index.credits{/lang}</a></li>
			
			{event name='tabMenuTabs'}
		</ul>
	</nav>
	
	{if ENABLE_WOLTLAB_NEWS}
		<div id="news" class="container containerPadding hidden tabMenuContent">
			<a class="twitter-timeline" href="https://twitter.com/woltlab" data-chrome="nofooter transparent" data-widget-id="335166618281865217">Tweets by @woltlab</a>
			{literal}
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			{/literal}
		</div>
	{/if}
	
	<div id="credits" class="container containerPadding hidden tabMenuContent">
		<fieldset>
			<legend>{lang}wcf.acp.index.credits{/lang}</legend>
		
			<dl>
				<dt>{lang}wcf.acp.index.credits.developedBy{/lang}</dt>
				<dd><a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"http://www.woltlab.com"|rawurlencode}" class="externalURL">WoltLab&reg; GmbH</a></dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.productManager{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.developer{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Tim D&uuml;sterhus</li>
						<li>Alexander Ebert</li>
						<li>Matthias Schmidt</li>
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.designer{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Harald Szekely</li>
						<li>Marcel Werk</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dt>{lang}wcf.acp.index.credits.contributor{/lang}</dt>
				<dd>
					<ul class="dataList">
						<li>Thorsten Buitkamp</li>
						<li>
							<a href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://github.com/WoltLab/WCF/contributors"|rawurlencode}" class="externalURL">{lang}wcf.acp.index.credits.contributor.more{/lang}</a>
						</li>
					</ul>
				</dd>
			</dl>
			
			<dl>
				<dd>Copyright &copy; 2001-2013 WoltLab&reg; GmbH. All rights reserved.</dd>
			</dl>
			
			<dl>
				<dd>{lang}wcf.acp.index.credits.trademarks{/lang}</dd>
			</dl>
		</fieldset>
	</div>
	
	{event name='tabMenuContents'}
</div>
{include file='footer'}