{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}acp/js/UserListEdit.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	// data array
	var userData = new Hash();
	var url = '{@$url|encodeJS}';
	
	// language
	var language = new Object();
	language['wcf.global.button.mark']		= '{lang}wcf.global.button.mark{/lang}';
	language['wcf.global.button.unmark']		= '{lang}wcf.global.button.unmark{/lang}';
	language['wcf.global.button.delete']		= '{lang}wcf.global.button.delete{/lang}';
	language['wcf.acp.user.button.sendMail']	= '{lang}wcf.acp.user.button.sendMail{/lang}';
	language['wcf.acp.user.button.exportMail']	= '{lang}wcf.acp.user.button.exportMail{/lang}';
	language['wcf.acp.user.button.assignGroup']	= '{lang}wcf.acp.user.button.assignGroup{/lang}';
	language['wcf.acp.user.deleteMarked.sure']	= '{lang}wcf.acp.user.deleteMarked.sure{/lang}';
	language['wcf.acp.user.delete.sure']		= '{lang}wcf.acp.user.delete.sure{/lang}';
	language['wcf.acp.user.markedUsers']		= '{lang}wcf.acp.user.markedUsers{/lang}';
	
	// additional options
	var additionalOptions = new Array();
	var additionalUserOptions = new Array();
	{if $additionalMarkedOptions|isset}{@$additionalMarkedOptions}{/if}
	
	// permissions
	var permissions = new Object();
	permissions['canEditUser'] = {if $__wcf->session->getPermission('admin.user.canEditUser')}1{else}0{/if};
	permissions['canDeleteUser'] = {if $__wcf->session->getPermission('admin.user.canDeleteUser')}1{else}0{/if};
	permissions['canMailUser'] = {if $__wcf->session->getPermission('admin.user.canMailUser')}1{else}0{/if};
	permissions['canEditMailAddress'] = {if $__wcf->session->getPermission('admin.user.canEditMailAddress')}1{else}0{/if};
	permissions['canEditPassword'] = {if $__wcf->session->getPermission('admin.user.canEditPassword')}1{else}0{/if};
	
	onloadEvents.push(function() { userListEdit = new UserListEdit(userData, {@$markedUsers}, additionalUserOptions, additionalOptions); });
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{if $searchID}userSearch{else}users{/if}L.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.user.{if $searchID}search{else}list{/if}{/lang}</h1>
		<h2>{if $searchID}{lang}wcf.acp.user.search.matches{/lang}{else}{lang}wcf.acp.user.list.count{/lang}{/if}</h2>
	</hgroup>
</header>

{assign var=encodedURL value=$url|rawurlencode}
{assign var=encodedAction value=$action|rawurlencode}
<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserList&pageNo=%d&searchID=$searchID&action=$encodedAction&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
	
	<nav class="largeButtons">
		<ul>
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="index.php?form=UserAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.user.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userAddM.png" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="index.php?form=UserSearch{@SID_ARG_2ND}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/searchM.png" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</nav>
</div>

<div class="border boxTitle">
	<nav class="menu">
		<ul>
			<li{if $action == ''} class="active"{/if}><a href="index.php?page=UserList{@SID_ARG_2ND}"><span>{lang}wcf.acp.user.list.all{/lang}</span> <span class="badge" title="{lang}wcf.acp.user.list.count{/lang}">{#$items}</span></a></li>
			{if $additionalUserListOptions|isset}{@$additionalUserListOptions}{/if}
		</ul>
	</nav>
	
	{if $users|count}
		<table>
			<thead>
				<tr class="tableHead">
					<th class="columnMark"><p class="emptyHead"><label><input type="checkbox" name="userMarkAll" /></label></p></th>
					<th class="columnUserID{if $sortField == 'userID'} active{/if}" colspan="2"><p><a href="index.php?page=UserList&amp;searchID={@$searchID}&amp;action={@$encodedAction}&amp;pageNo={@$pageNo}&amp;sortField=userID&amp;sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.user.userID{/lang}{if $sortField == 'userID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnUsername{if $sortField == 'username'} active{/if}"><p><a href="index.php?page=UserList&amp;searchID={@$searchID}&amp;action={@$encodedAction}&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></dipv></th>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<th class="column{$column|ucfirst}{if $sortField == $column} active{/if}"><p><a href="index.php?page=UserList&amp;searchID={@$searchID}&amp;action={@$encodedAction}&amp;pageNo={@$pageNo}&amp;sortField={$column}&amp;sortOrder={if $sortField == $column && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}{$columnLanguageVariable}{/lang}{if $sortField == $column} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					{/foreach}
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$users item=user}
				<tr id="userRow{@$user->userID}">
					<td class="columnMark"><input type="checkbox" id="userMark{@$user->userID}" name="userMark" value="{@$user->userID}" /></td>
					<td class="columnIcon">
						<script type="text/javascript">
							//<![CDATA[
							userData.set({@$user->userID}, {
								'isMarked': {@$user->isMarked},
								'className': '{cycle values="container-1,container-2"}'
							});
							//]]>
						</script>
						
						{if $user->editable}
							<a href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.user.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.user.edit{/lang}" />
						{/if}
						{if $user->deletable}
							<a onclick="return confirm('{lang}wcf.acp.user.delete.sure{/lang}')" href="index.php?action=UserDelete&amp;userID={@$user->userID}&amp;url={@$encodedURL}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.user.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.user.delete{/lang}" />
						{/if}
						
						{if $additionalButtons[$user->userID]|isset}{@$additionalButtons[$user->userID]}{/if}
					</td>
					<td class="columnUserID columnID"><p>{@$user->userID}</p></td>
					<td class="columnUsername columnText"><p>{if $user->editable}<a title="{lang}wcf.acp.user.edit{/lang}" href="index.php?form=UserEdit&amp;userID={@$user->userID}{@SID_ARG_2ND}">{$user->username}</a>{else}{$user->username}{/if}</p></td>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<td class="column{$column|ucfirst}"><p>{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</p></td>
					{/foreach}
					
					{if $additionalColumns[$user->userID]|isset}{@$additionalColumns[$user->userID]}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks} <div id="userEditMarked" class="optionButtons"></div>
		
		<nav class="largeButtons">
			<ul>
				{if $__wcf->session->getPermission('admin.user.canAddUser')}
					<li><a href="index.php?form=UserAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.user.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/userAddM.png" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
				{/if}
				<li><a href="index.php?form=UserSearch{@SID_ARG_2ND}" title="{lang}wcf.acp.user.search{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/searchM.png" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
				{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
			</ul>
		</nav>
	</div>
{else}
	<div class="border content">
		<p class="info">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
	</div>
{/if}

{include file='footer'}
