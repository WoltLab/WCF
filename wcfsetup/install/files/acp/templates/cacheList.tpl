{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		WCF.Icon.addObject({
			'wcf.global.opened': '{@RELATIVE_WCF_DIR}icon/opened2.svg',
			'wcf.global.closed': '{@RELATIVE_WCF_DIR}icon/closed2.svg'
		});
		WCF.Collapsible.Simple.init();
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cache1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cache.list{/lang}</h1>
	</hgroup>
</header>

{if $cleared}
	<p class="wcf-success">{lang}wcf.acp.cache.clear.success{/lang}</p>	
{/if}

<fieldset>
	<legend>{lang}wcf.acp.cache.data{/lang}</legend>
	
	<dl>
		<dt>{lang}wcf.acp.cache.data.source{/lang}</dt>
		<dd>{$cacheData.source}</dd>
	</dl>
	{if $cacheData.version}
		<dl>
			<dt>{lang}wcf.acp.cache.data.version{/lang}</dt>
			<dd>{$cacheData.version}</dd>
		</dl>
	{/if}
	{if $cacheData.size}<dl>
		<dt>{lang}wcf.acp.cache.data.size{/lang}</dt>
		<dd>{@$cacheData.size|filesize}</dd>
	</dl>{/if}
	{if $cacheData.files}<dl>
		<dt>{lang}wcf.acp.cache.data.files{/lang}</dt>
		<dd>{#$cacheData.files}</dd>
	</dl>{/if}
	
	{event name='dataFields'}
</fieldset>

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			{if $cacheData.files}<li><a onclick="return confirm('{lang}wcf.acp.cache.clear.sure{/lang}')" href="{link controller='CacheClear'}{/link}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" /> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>{/if}
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

{foreach from=$caches key=cache item=files}
	{counter name=cacheIndex assign=cacheIndex print=false start=0}
	{if $files|count}
		<div class="wcf-border wcf-boxTitle">
			<a class="collapsible" data-is-open="1" data-collapsible-container="cache{@$cacheIndex}"><img src="{@RELATIVE_WCF_DIR}icon/opened2.svg" alt="" title="{lang}wcf.global.button.close{/lang}" class="balloonTooltip" /></a>
			<hgroup>
				<h1>{$cache} <span class="badge" title="{lang}wcf.acp.cache.data.files.count{/lang}">{#$files|count}</span></h1>
			</hgroup>
			
			<table id="cache{@$cacheIndex}" class="wcf-table">
				<thead>
					<tr>
						<th class="columnTitle">{lang}wcf.acp.cache.list.name{/lang}</th>
						<th class="columnDigits">{lang}wcf.acp.cache.list.size{/lang}</th>
						<th class="columnDate">{lang}wcf.acp.cache.list.mtime{/lang}</th>
						{if $files.0.perm|isset}
							<th class="columnDigits">{lang}wcf.acp.cache.list.perm{/lang}</th>
						{/if}
					</tr>
				</thead>
				
				<tbody>
				{foreach from=$files item=file}
					<tr>
						<td class="columnTitle"><p>{$file.filename}</td>
						<td class="columnDigits"><p>{@$file.filesize|filesize}</td>
						<td class="columnDate">{if $file.mtime > 1}<p>{@$file.mtime|time}</p>{/if}</td>
						{if $file.perm|isset}
							<td class="columnDigits"><p{if !$file.writable} style="color: #c00"{/if}>{@$file.perm}</p></td>
						{/if}
					</tr>
				{/foreach}
				</tbody>
			</table>
			
		</div>
	{/if}
{/foreach}

<div class="wcf-contentFooter">
	<nav class="wcf-largeButtons">
		<ul>
			{if $cacheData.files}<li><a onclick="return confirm('{lang}wcf.acp.cache.clear.sure{/lang}')" href="{link controller='CacheClear'}{/link}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" /> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>{/if}
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

{include file='footer'}
