{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>
<style type="text/css">
#health ul {
	list-style: disc;
	padding-left: 16px;
}
#credits dd > ul > li {
	display: inline;
}
#credits dd > ul > li:after {
	content: ", ";
}
#credits dd > ul > li:last-child:after {
	content: "";
}
</style>
{if $didYouKnow !== ''}<p class="info">{lang}wcf.acp.index.didYouKnow{/lang}: {@$didYouKnow|language}</p>{/if}
<p class="{@$health}">{lang}wcf.acp.index.health.summary.{@$health}{/lang}</p>
{event name='boxes'}

<div class="tabMenuContainer" data-active="{if $health !== 'success'}health{else}news{/if}" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			{if $health !== 'success'}<li><a href="#health" title="Health">Health</a></li>{/if}
			<li><a href="#news" title="News">News</a></li>
			<li><a href="#credits" title="Credits">Credits</a></li>
			{event name='tabs'}
		</ul>
	</nav>
	{if $health !== 'success'}
		<div id="health" class="container containerPadding shadow hidden tabMenuContent">
			{foreach from=$healthDetails item='issues' key='healthType'}
				{hascontent}
				<fieldset><legend><img src="{$__wcf->getPath()}icon/system{$healthType|ucfirst}.svg" class="icon24" /> {lang}wcf.acp.index.health.detail.{@$healthType}{/lang}</legend>
					<ul>
					{content}
						{foreach from=$issues item='issue'}
							<li>{@$issue}</li>
						{/foreach}
					{/content}
					</ul>
				</fieldset>
				{/hascontent}
			{/foreach}
		</div>
	{/if}
	<div id="news" class="container containerPadding shadow hidden tabMenuContent">
		WoltLab Community Framework is twice as cool now, as the version number is twice as high.
	</div>
	<fieldset id="credits" class="container containerPadding shadow hidden tabMenuContent">
		<dl>
			<dt>{lang}wcf.acp.index.credits.developedBy{/lang}</dt>
			<dd><a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={"http://www.woltlab.com"|rawurlencode}" class="externalURL">WoltLab&reg; GmbH</a></dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.index.credits.productManager{/lang}</dt>
			<dd>
				<ul>
					<li>Marcel Werk</li>
				</ul>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.index.credits.developer{/lang}</dt>
			<dd>
				<ul>
					<li>Alexander Ebert</li>
					<li>Marcel Werk</li>
				</ul>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.index.credits.designer{/lang}</dt>
			<dd>
				<ul>
					<li>Harald Szekely</li>
					<li>Marcel Werk</li>
				</ul>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.index.credits.contributor{/lang}</dt>
			<dd>
				<ul>
					<li>Thorsten Buitkamp</li>
					<li>Tim D&uuml;sterhus</li>
					<li>Matthias Schmidt</li>
					<li>
						<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={"https://github.com/WoltLab/WCF/contributors"|rawurlencode}" class="externalURL">
							{lang}wcf.acp.index.credits.contributor.more{/lang}
						</a>
					</li>
				</ul>
			</dd>
		</dl>
		
		{*<dl>
			<dt>{lang}wcf.acp.index.credits.translators{/lang}</dt>
			<dd>
				<ul>
				</ul>
			</dd>
		</dl>*}
		
		<dl>
			<dt></dt>
			<dd>Copyright &copy; 2001-2012 WoltLab&reg; GmbH. All rights reserved.</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>{lang}wcf.acp.index.credits.trademarks{/lang}</dd>
		</dl>
	</fieldset>
	
	{event name='tabContent'}
</div>
{include file='footer'}