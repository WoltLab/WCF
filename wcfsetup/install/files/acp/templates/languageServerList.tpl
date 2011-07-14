{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\language\\server\\LanguageServerAction', $('.languageServerRow'));
		new WCF.Action.Toggle('wcf\\data\\language\\server\\LanguageServerAction', $('.languageServerRow'));
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageServerL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.languageServer.view{/lang}</h2>
	</div>
</div>

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?form=LanguageServerAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.languageServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/languageServerAddM.png" alt="" /> <span>{lang}wcf.acp.languageServer.add{/lang}</span></a></li></ul>
	</div>
</div>

{if !$languageServers|count}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.languageServer.view.noneAvailable{/lang}</p>
		</div>
	</div>
{else}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.languageServer.list.available{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnLanguageServerID{if $sortField == 'languageServerID'} active{/if}" colspan="2"><div><a href="index.php?page=LanguageServerList&amp;pageNo={@$pageNo}&amp;sortField=languageServerID&amp;sortOrder={if $sortField == 'languageServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.languageServer.languageServerID{/lang}{if $sortField == 'languageServerID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnServer{if $sortField == 'server'} active{/if}"><div><a href="index.php?page=LanguageServerList&amp;pageNo={@$pageNo}&amp;sortField=server&amp;sortOrder={if $sortField == 'server' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.languageServer.server{/lang}{if $sortField == 'server'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$languageServers item=languageServer}
					<tr class="languageServerRow {cycle values="container-1,container-2"}">
						<td class="columnIcon">
							<img src="{@RELATIVE_WCF_DIR}icon/{if !$languageServer->disabled}enabled{else}disabled{/if}S.png" alt="" class="toggleButton" title="{lang}wcf.acp.languageServer.{if !$languageServer->disabled}disable{else}enable{/if}{/lang}" data-objectID="{@$languageServer->languageServerID}" data-disableMessage="{lang}wcf.acp.languageServer.disable{/lang}" data-enableMessage="{lang}wcf.acp.languageServer.enable{/lang}" />
							<a href="index.php?form=LanguageServerEdit&amp;languageServerID={@$languageServer->languageServerID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.languageServer.edit{/lang}" /></a>
							<img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.languageServer.delete{/lang}" class="deleteButton" data-objectID="{@$languageServer->languageServerID}" data-confirmMessage="{lang}wcf.acp.languageServer.delete.sure{/lang}" />
							
							{if $additionalButtons[$languageServer->languageServerID]|isset}{@$additionalButtons[$languageServer->languageServerID]}{/if}
						</td>
						<td class="columnID">{@$languageServer->languageServerID}</td>
						<td class="columnText">
							<a href="index.php?form=LanguageServerEdit&amp;languageServerID={@$languageServer->languageServerID}{@SID_ARG_2ND}">
								{@$languageServer->serverURL}
							</a>
						</td>
						{if $additionalColumns[$languageServer->languageServerID]|isset}{@$additionalColumns[$languageServer->languageServerID]}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?form=LanguageServerAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.languageServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/languageServerAddM.png" alt="" /> <span>{lang}wcf.acp.languageServer.add{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{include file='footer'}
