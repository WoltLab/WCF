{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		{* TODO: Fix icon path *}
		{* TODO: Please do not call these icons "plus" / "minus"! They may not look like that! Therefor i changed the names to "opened" / "closed". There should be a way to add a Suffix to the file name also, similar to the deprecated size-suffix (s, m, l). We now use the number suffix "1, 2, …". Strict names are not very useful here. *}
		WCF.Icon.addObject({
			'wcf.global.opened': '{@RELATIVE_WCF_DIR}icon/opened2.svg',
			'wcf.global.closed': '{@RELATIVE_WCF_DIR}icon/closed2.svg'
		});

		WCF.Collapsible.Simple.init();
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cache1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cache.list{/lang}</h1>
	</hgroup>
</header>

{if $cleared}
	<p class="success">{lang}wcf.acp.cache.clear.success{/lang}</p>	
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
	<dl>
		<dt>{lang}wcf.acp.cache.data.size{/lang}</dt>
		<dd>{@$cacheData.size|filesize}</dd>
	</dl>
	<dl>
		<dt>{lang}wcf.acp.cache.data.files{/lang}</dt>
		<dd>{#$cacheData.files}</dd>
	</dl>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			<li><a onclick="return confirm('{lang}wcf.acp.cache.clear.sure{/lang}')" href="index.php?action=CacheClear{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteM.png" alt="" /> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{foreach from=$caches key=cache item=files}
	{counter name=cacheIndex assign=cacheIndex print=false start=0}
	{if $files|count}
		<div class="border boxTitle">
			<a data-isOpen="1" data-collapsibleContainer="cache{@$cacheIndex}" class="collapsible"><img src="{@RELATIVE_WCF_DIR}icon/opened2.svg" alt="" title="ToDo: Collapsible" class="balloonTooltip" /></a>
			<hgroup>
				<h1>{$cache} <span class="badge" title="{lang}wcf.acp.cache.data.files.count{/lang}">{#$files|count}</span></h1>
			</hgroup>
			
			<table id="cache{@$cacheIndex}">
				<thead>
					<tr>
						<th><p class="emptyHead">{lang}wcf.acp.cache.list.name{/lang}</p></th>
						<th><p class="emptyHead">{lang}wcf.acp.cache.list.size{/lang}</p></th>
						<th><p class="emptyHead">{lang}wcf.acp.cache.list.mtime{/lang}</p></th>
						{if $files.0.perm|isset}
							<th><p class="emptyHead">{lang}wcf.acp.cache.list.perm{/lang}</p></th>
						{/if}
					</tr>
				</thead>
				
				<tbody>
				{foreach from=$files item=file}
					<tr>
						<td class="columnText"><p>{$file.filename}</td>
						<td class="columnNumbers"><p>{@$file.filesize|filesize}</td>
						<td class="columnDate">{if $file.mtime > 1}<p>{@$file.mtime|time}</p>{/if}</td>
						{if $file.perm|isset}
							<td class="columnNumbers"><p{if !$file.writable} style="color: #c00"{/if}>{@$file.perm}</p></td>
						{/if}
					</tr>
				{/foreach}
				</tbody>
			</table>
			
		</div>
	{/if}
{/foreach}

<div class="contentFooter">
	<nav class="largeButtons">
		<ul>
			<li><a onclick="return confirm('{lang}wcf.acp.cache.clear.sure{/lang}')" href="index.php?action=CacheClear{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteM.png" alt="" /> <span>{lang}wcf.acp.cache.button.clear{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{include file='footer'}
