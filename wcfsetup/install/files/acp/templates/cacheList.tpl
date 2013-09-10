{include file='header' pageTitle='wcf.acp.cache.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Collapsible.Simple.init();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.cache.list{/lang}</h1>
</header>

<div class="container containerPadding marginTop">
	<fieldset>
		<legend>{lang}wcf.acp.cache.data{/lang}</legend>
		
		<dl>
			<dt>{lang}wcf.acp.cache.data.source{/lang}</dt>
			<dd>
				{assign var='__source' value='\\'|explode:$cacheData.source}
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
	</fieldset>
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			{if $cacheData.files}
				<li><a onclick="WCF.System.Confirmation.show('{lang}wcf.acp.cache.clear.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;" href="{link controller='CacheClear'}{/link}" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>
			{/if}
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{foreach from=$caches key='cacheType' item='cacheTypeCaches'}
	{foreach from=$cacheTypeCaches key='cache' item='files'}
		{counter name=cacheIndex assign=cacheIndex print=false start=0}
		
		{if $files|count}
			<div class="tabularBox tabularBoxTitle marginTop">
				<header>
					<h2><a class="jsCollapsible jsTooltip" data-is-open="0" data-collapsible-container="cache{@$cacheIndex}" title="{lang}wcf.global.button.collapsible{/lang}" class="jsTooltip"><span class="icon icon16 icon-chevron-right"></span></a> {lang}wcf.acp.cache.type.{$cacheType}{/lang} <span class="badge badgeInverse">{#$files|count}</span></h2>
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
			</div>
		{/if}
	{/foreach}
{/foreach}

<div class="contentNavigation">
	<nav>
		<ul>
			{if $cacheData.files}
				<li><a onclick="WCF.System.Confirmation.show('{lang}wcf.acp.cache.clear.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;" href="{link controller='CacheClear'}{/link}" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>
			{/if}
			
			{event name='contentNavigationButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
