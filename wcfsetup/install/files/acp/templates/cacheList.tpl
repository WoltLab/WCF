{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cacheL.png" alt="" />
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
		<dt><label>{lang}wcf.acp.cache.data.source{/lang}</label></dt>
		<dd>{$cacheData.source}</dd>
	</dl>
	{if $cacheData.version}
		<dl>
			<dt><label>{lang}wcf.acp.cache.data.version{/lang}</label></dt>
			<dd>{$cacheData.version}</dd>
		</dl>
	{/if}
	<dl>
		<dt><label>{lang}wcf.acp.cache.data.size{/lang}</label></dt>
		<dd>{@$cacheData.size|filesize}</dd>
	</dl>
	<dl>
		<dt><label>{lang}wcf.acp.cache.data.files{/lang}</label></dt>
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
	{if $files|count}
		<div class="border boxTitle">
			<a onclick="openList('{$cache}')" class="collapsible"><img src="{@RELATIVE_WCF_DIR}icon/minusS.png" id="{$cache}Image" alt="" title="ToDo: Collapsible" class="balloonTooltip" /></a>
			<hgroup>
				<h1>{$cache} <span class="badge" title="{$cache}">{#$files|count}</span></h1>
			</hgroup>
			
			<table id="{$cache}">
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
		<script type="text/javascript">
			//<![CDATA[
			initList('{$cache}', 0);
			//]]>
		</script>
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
