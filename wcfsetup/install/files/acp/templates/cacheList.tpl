{include file='header' pageTitle='wcf.acp.cache.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.cache.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $cacheData.files}
						<li>
							<button type="button" class="jsCacheClearButton button" data-endpoint="{$cacheClearEndPoint}">
								{icon name='xmark'} <span>{lang}wcf.acp.cache.button.clear{/lang}</span>
							</button>
						</li>
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
			{lang}wcf.acp.cache.source.type.{$__source|end}{/lang}
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
			<details class="section tabularBox">
				<summary class="sectionTitle">
					{lang}wcf.acp.cache.type.{$cacheType}{/lang}
					<span class="badge">{#$files|count}</span>
					<br><kbd>{$cache}</kbd>
				</summary>
				
				<table id="cache{@$cacheIndex}" class="table">
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
			</details>
		{/if}
	{/foreach}
{/foreach}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}
					{if $cacheData.files}
						<li>
							<button type="button" class="jsCacheClearButton button" data-endpoint="{$cacheClearEndPoint}">
								{icon name='xmark'} <span>{lang}wcf.acp.cache.button.clear{/lang}</span>
							</button>
						</li>
					{/if}
					
					{event name='contentFooterNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

<script data-relocate="true">
	{jsphrase name='wcf.acp.cache.clear.sure'}
	
	require(['WoltLabSuite/Core/Acp/Component/Cache/Clear'], ({ setup }) => {
		setup();
	});
</script>

{include file='footer'}
