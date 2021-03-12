{include file='header' pageTitle='wcf.acp.tag.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/Clipboard', 'Language'], function(ControllerClipboard, Language) {
		Language.add('wcf.acp.tag.setAsSynonyms', '{jslang}wcf.acp.tag.setAsSynonyms{/jslang}');
		
		var deleteAction = new WCF.Action.Delete('wcf\\data\\tag\\TagAction', '.jsTagRow');
		deleteAction.setCallback(ControllerClipboard.reload.bind(ControllerClipboard));
		
		WCF.Clipboard.init('wcf\\acp\\page\\TagListPage', {@$hasMarkedItems}, {
			'com.woltlab.wcf.tag': {
				'delete': deleteAction
			}
		});
		
		new WCF.ACP.Tag.SetAsSynonymsHandler();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.tag.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TagAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.tag.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $items}
	<form action="{link controller='TagList'}{/link}" method="post">
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
			
			<dl>
				<dt></dt>
				<dd>
					<input type="text" id="tagSearch" name="search" value="{$search}" placeholder="{lang}wcf.acp.tag.list.search.query{/lang}" autofocus class="medium">
				</dd>
			</dl>
		</section>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</form>
{/if}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="TagList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&search=$search"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table data-type="com.woltlab.wcf.tag" class="table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
					<th class="columnID columnTagID{if $sortField == 'tagID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=tagID&sortOrder={if $sortField == 'tagID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.acp.tag.name{/lang}</a></th>
					<th class="columnDigits columnUsageCount{if $sortField == 'usageCount'} active {@$sortOrder}{/if}"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=usageCount&sortOrder={if $sortField == 'usageCount' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.acp.tag.usageCount{/lang}</a></th>
					<th class="columnText columnLanguage{if $sortField == 'languageID'} active {@$sortOrder}{/if}"><a href="{link controller='TagList'}pageNo={@$pageNo}&sortField=languageID&sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&search={@$search|rawurlencode}{/link}">{lang}wcf.acp.tag.languageID{/lang}</a></th>
					<th class="columnText columnSynonymFor">{lang}wcf.acp.tag.synonymFor{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=tag}
					<tr class="jsTagRow jsClipboardObject">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$tag->tagID}"></td>
						<td class="columnIcon">
							<a href="{link controller='TagEdit' object=$tag}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$tag->tagID}" data-confirm-message-html="{lang __encode=true}wcf.acp.tag.delete.sure{/lang}"></span>
							
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
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='TagAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.tag.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
