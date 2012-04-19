{include file='header'}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>
<style type="text/css">
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
{if $didYouKnow !== ''}<p class="info">{lang}wcf.acp.index.didYouKnow{/lang}: {$didYouKnow|language}</p>{/if}

<div class="tabMenuContainer" data-active="credits" data-store="activeTabMenuItem">
	<nav class="tabMenu">
		<ul>
			<li><a href="#credits" title="Credits">Credits</a></li>
		</ul>
	</nav>
	
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
					<li>Alexander Ebert</li><li>Marcel Werk</li>
				</ul>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.index.credits.designer{/lang}</dt>
			<dd>
				<ul>
					<li>Harald Szekely</li><li>Marcel Werk</li>
				</ul>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.index.credits.contributor{/lang}</dt>
			<dd>
				<ul>
					<li>Thorsten Buitkamp</li><li>Tim D&uuml;sterhus</li><li>Matthias Schmidt</li>
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
</div>
{include file='footer'}