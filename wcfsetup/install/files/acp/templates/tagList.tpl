{include file='header' pageTitle='wcf.acp.tag.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.add('wcf.acp.tag.setAsSynonyms', '{lang}wcf.acp.tag.setAsSynonyms{/lang}');
		
		WCF.Clipboard.init('wcf\\acp\\page\\TagListPage', {@$hasMarkedItems}, {
			'com.woltlab.wcf.tag': {
				'delete': new WCF.Action.Delete('wcf\\data\\tag\\TagAction', '.jsTagRow')
			}
		});
		
		new WCF.ACP.Tag.SetAsSynonymsHandler();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.tag.list{/lang}</h1>
</header>

{include file='formError'}

{if $items}
	<form action="{link controller='TagList'}{/link}" method="post">
		<div class="container containerPadding marginTop">
			<fieldset>
				<legend>{lang}wcf.acp.tag.list.search{/lang}</legend>
				
				<dl>
					<dt><label for="tagSearch">{lang}wcf.acp.tag.list.search.query{/lang}</label></dt>
					<dd>
						<input type="text" id="tagSearch" name="search" value="{$search}" autofocus="autofocus" class="medium" />
					</dd>
				</dl>
			</fieldset>
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SID_INPUT_TAG}
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{/if}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="TagList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&search=$search"}
	
	<nav>
		<ul>
			<li><a href="{link controller='TagAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.tag.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.tag.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table data-type="com.woltlab.wcf.tag" class="table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll" /></label></th>
					<th class="columnID columnTagID{if $sortField == 'tagID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=tagID&sortOrder={if $sortField == 'tagID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.acp.tag.name{/lang}</a></th>
					<th class="columnDigits columnUsageCount{if $sortField == 'usageCount'} active {@$sortOrder}{/if}"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=usageCount&sortOrder={if $sortField == 'usageCount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.acp.tag.usageCount{/lang}</a></th>
					<th class="columnText columnLanguage{if $sortField == 'languageID'} active {@$sortOrder}{/if}"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=languageID&sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.acp.tag.languageID{/lang}</a></th>
					<th class="columnText columnSynonymFor">{lang}wcf.acp.tag.synonymFor{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=tag}
					<tr class="jsTagRow jsClipboardObject">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$tag->tagID}" /></td>
						<td class="columnIcon">
							<a href="{link controller='TagEdit' object=$tag}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$tag->tagID}" data-confirm-message="{lang}wcf.acp.tag.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{#$tag->tagID}</td>
						<td class="columnTitle columnName"><a href="{link controller='TagEdit' object=$tag}{/link}" class="badge tag">{$tag->name|tableWordwrap}</a></td>
						<td class="columnDigits columnUsageCount">{if $tag->synonymFor === null}{#$tag->usageCount}{/if}</td>
						<td class="columnText columnLanguage">{if $tag->languageName !== null}{$tag->languageName} ({$tag->languageCode}){/if}</td>
						<td class="columnText columnSynonymFor">{if $tag->synonymFor !== null}<a href="{link controller='TagList'}search={@$tag->synonymName|rawurlencode}{/link}" class="badge tag">{$tag->synonymName}</a>{/if}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='TagAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.tag.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
		
		<nav class="jsClipboardEditor" data-types="[ 'com.woltlab.wcf.tag' ]"></nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
