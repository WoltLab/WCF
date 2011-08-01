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
	
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.cache.data.source{/lang}</p>
		<p class="formField">{$cacheData.source}</p>
	</div>
	{if $cacheData.version}
		<div class="formElement">
			<p class="formFieldLabel">{lang}wcf.acp.cache.data.version{/lang}</p>
			<p class="formField">{$cacheData.version}</p>
		</div>
	{/if}
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.cache.data.size{/lang}</p>
		<p class="formField">{@$cacheData.size|filesize}</p>
	</div>
	<div class="formElement">
		<p class="formFieldLabel">{lang}wcf.acp.cache.data.files{/lang}</p>
		<p class="formField">{#$cacheData.files}</p>
	</div>
	
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
		<div class="border titleBarPanel">
			<div class="containerHead">
				<div class="containerIcon">
					<a onclick="openList('{$cache}')"><img src="{@RELATIVE_WCF_DIR}icon/minusS.png" id="{$cache}Image" alt="" /></a>
				</div>
				<div class="containerContent">
					<h3>{$cache} ({#$files|count})</h3>
				</div>
			</div>
		</div>
		<div id="{$cache}" class="border borderMarginRemove">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th><p><span class="emptyHead">{lang}wcf.acp.cache.list.name{/lang}</span></p></th>
						<th><p><span class="emptyHead">{lang}wcf.acp.cache.list.size{/lang}</span></p></th>
						<th><p><span class="emptyHead">{lang}wcf.acp.cache.list.mtime{/lang}</span></p></th>
						{if $files.0.perm|isset}
							<th><p><span class="emptyHead">{lang}wcf.acp.cache.list.perm{/lang}</span></p></th>
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
							<td class="columnNumbers"{if !$file.writable} style="color: #c00"{/if}><p>{@$file.perm}</p></td>
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

{include file='footer'}
