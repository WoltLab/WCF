{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cacheL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cache.language.list{/lang}</h1>
	</hgroup>
</header>

{if $cleared}
	<p class="success">{lang}wcf.acp.cache.language.clear.success{/lang}</p>	
{/if}

<fieldset>
	<legend>{lang}wcf.acp.cache.language.data{/lang}</legend>
	
	<dl>
		<dt><label>{lang}wcf.acp.cache.language.data.size{/lang}</label></dt>
		<dd>{@$cacheData.size|filesize}</dd>
	</dl>
	<dl>
		<dt><label>{lang}wcf.acp.cache.language.data.files{/lang}</label></dt>
		<dd>{#$cacheData.files}</dd>
	</dl>
	
	{if $additionalFields|isset}{@$additionalFields}{/if}
</fieldset>

<div class="contentHeader">
	<nav class="largeButtons">
		<ul>
			<!-- TODO: add clear cache button -->
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{if $fileInfos|count}
	<div class="border boxTitle">
		<hgroup>
			<h1>{WCF_DIR}language <span class="badge" title="">{#$fileInfos|count}</span></h1>
		</hgroup>

		<table>
			<thead>
				<tr>
					<th><p class="emptyHead">{lang}wcf.acp.cache.language.list.name{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.cache.language.list.size{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.cache.language.list.mtime{/lang}</p></th>
					<th><p class="emptyHead">{lang}wcf.acp.cache.language.list.perm{/lang}</p></th>
				</tr>
			</thead>

			<tbody>
			{foreach from=$fileInfos item=fileInfo}
				<tr>
					<td class="columnText"><p>{$fileInfo->getFileName()}</td>
					<td class="columnNumbers"><p>{@$fileInfo->getSize()|filesize}</td>
					<td class="columnDate">{if $fileInfo->getMTime() > 1}<p>{@$fileInfo->getMTime()|time}</p>{/if}</td>
					{assign var=tempPermissions value=@'%o'|sprintf:$fileInfo->getPerms()}
					<td class="columnNumbers"><p{if !$fileInfo->isWritable()} style="color: #c00"{/if}>{@$tempPermissions|substr:-3}</p></td>
				</tr>
			{/foreach}
			</tbody>
		</table>		
	</div>
{/if}

<div class="contentFooter">
	<nav class="largeButtons">
		<ul>
			<!-- TODO: add clear cache button -->
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{include file='footer'}
