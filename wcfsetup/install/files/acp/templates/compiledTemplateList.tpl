{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/cacheL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cache.template.list{/lang}</h1>
	</hgroup>
</header>

{if $deleted}
	<p class="success">{lang}wcf.acp.cache.template.delete.success{/lang}</p>	
{/if}

<fieldset>
	<legend>{lang}wcf.acp.cache.template.data{/lang}</legend>
	
	<dl>
		<dt><label>{lang}wcf.acp.cache.template.data.size{/lang}</label></dt>
		<dd>{@$cacheData.size|filesize}</dd>
	</dl>
	<dl>
		<dt><label>{lang}wcf.acp.cache.template.data.files{/lang}</label></dt>
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

{foreach from=$fileInfos key=cache item=dirFileInfos}
	{if $dirFileInfos|count}
		<div class="border boxTitle">
			<hgroup>
				<h1>{$cache} <span class="badge" title="">{#$dirFileInfos|count}</span></h1>
			</hgroup>

			<table id="{$cache}">
				<thead>
					<tr>
						<th><p class="emptyHead">{lang}wcf.acp.cache.template.list.name{/lang}</p></th>
						<th><p class="emptyHead">{lang}wcf.acp.cache.template.list.size{/lang}</p></th>
						<th><p class="emptyHead">{lang}wcf.acp.cache.template.list.mtime{/lang}</p></th>
						<th><p class="emptyHead">{lang}wcf.acp.cache.template.list.perm{/lang}</p></th>
					</tr>
				</thead>

				<tbody>
				{foreach from=$dirFileInfos item=fileInfo}
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
{/foreach}

<div class="contentFooter">
	<nav class="largeButtons">
		<ul>
			<!-- TODO: add clear cache button -->
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

{include file='footer'}
