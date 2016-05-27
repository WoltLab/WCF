{include file='header' pageTitle='wcf.acp.cache.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Collapsible.Simple.init();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cache.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $cacheData.files}
						<li><a onclick="WCF.System.Confirmation.show('{lang}wcf.acp.cache.clear.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;" href="{link controller='CacheClear'}{/link}" class="button"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.cache.data{/lang}</h2>
	
	<dl>
		<dt>{lang}wcf.acp.cache.data.source{/lang}</dt>
		<dd>
			{assign var='__source' value="\\"|explode:$cacheData.source}
			{lang}wcf.acp.cache.source.type.{$__source|array_pop}{/lang}
			<small>{$cacheData.source}</small>
		</dd>
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
</section>

{foreach from=$caches key='cacheType' item='cacheTypeCaches'}
	{foreach from=$cacheTypeCaches key='cache' item='files'}
		{counter name=cacheIndex assign=cacheIndex print=false start=0}
		
		{if $files|count}
			<section class="section tabularBox tabularBoxTitle">
				<header>
					<h2>
						<a class="jsCollapsible jsTooltip" data-is-open="0" data-collapsible-container="cache{@$cacheIndex}" title="{lang}wcf.global.button.collapsible{/lang}" class="jsTooltip"><span class="icon icon16 fa-chevron-right"></span></a>
						{lang}wcf.acp.cache.type.{$cacheType}{/lang}
						<span class="badge">{#$files|count}</span>
					</h2>
					<small>{$cache}</small>
				</header>
				
				<table id="cache{@$cacheIndex}" style="display: none;" class="table">
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
								<td class="columnTitle">{$file.filename}</td>
								<td class="columnDigits">{@$file.filesize|filesize}</td>
								<td class="columnDate">{if $file.mtime > 1}{@$file.mtime|time}{/if}</td>
								{if $file.perm|isset}
									<td class="columnDigits"><span{if !$file.writable} class="hot"{/if}>{@$file.perm}</span></td>
								{/if}
							</tr>
						{/foreach}
					</tbody>
				</table>
			</section>
		{/if}
	{/foreach}
{/foreach}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{if $cacheData.files}
						<li><a onclick="WCF.System.Confirmation.show('{lang}wcf.acp.cache.clear.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;" href="{link controller='CacheClear'}{/link}" class="button"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>
					{/if}
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
