{include file='header' pageTitle='wcf.acp.box.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\box\\BoxAction', '.jsBoxRow');
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.box.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='BoxAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.add{/lang}</span></a></li>
			<li><a href="{link controller='BoxAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.addMultilingual{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<form method="post" action="{link controller='BoxList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="name" name="name" value="{$name}" placeholder="{lang}wcf.global.name{/lang}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="boxTitle" name="title" value="{$title}" placeholder="{lang}wcf.acp.box.title{/lang}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="boxContent" name="content" value="{$content}" placeholder="{lang}wcf.acp.box.content{/lang}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<label class="selectDropdown">
						<select name="position" id="boxPosition">
							<option value="0">{lang}wcf.acp.box.position{/lang}</option>
							{foreach from=$availablePositions item=availablePosition}
								<option value="{@$availablePosition}"{if $availablePosition == $position} selected="selected"{/if}>{@$availablePosition}</option>
							{/foreach}
						</select>
					</label>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<label class="selectDropdown">
						<select name="boxType" id="boxType">
							<option value="">{lang}wcf.acp.box.boxType{/lang}</option>
							<option value="static"{if $boxType == 'static'} selected="selected"{/if}>{lang}wcf.acp.box.boxType.static{/lang}</option>
							<option value="system"{if $boxType == 'system'} selected="selected"{/if}>{lang}wcf.acp.box.boxType.system{/lang}</option>
						</select>
					</label>
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</section>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
		{assign var='linkParameters' value=''}
		{if $name}{capture append=linkParameters}&name={@$name|rawurlencode}{/capture}{/if}
		{if $title}{capture append=linkParameters}&title={@$title|rawurlencode}{/capture}{/if}
		{if $content}{capture append=linkParameters}&content={@$content|rawurlencode}{/capture}{/if}
		{if $position}{capture append=linkParameters}&position={@$position}{/capture}{/if}
		{if $boxType}{capture append=linkParameters}&boxType={@$boxType|rawurlencode}{/capture}{/if}
		
		{pages print=true assign=pagesLinks controller="BoxList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnPageID{if $sortField == 'boxID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=boxID&sortOrder={if $sortField == 'boxID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnName{if $sortField == 'name'} active {@$sortOrder}{/if}"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=name&sortOrder={if $sortField == 'name' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnBoxType{if $sortField == 'boxType'} active {@$sortOrder}{/if}"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=boxType&sortOrder={if $sortField == 'boxType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.box.boxType{/lang}</a></th>
					<th class="columnText columnPosition{if $sortField == 'position'} active {@$sortOrder}{/if}"><a href="{link controller='BoxList'}pageNo={@$pageNo}&sortField=position&sortOrder={if $sortField == 'position' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.box.position{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=box}
					<tr class="jsBoxRow">
						<td class="columnIcon">
							<a href="{link controller='BoxEdit' id=$box->boxID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{if $box->canDelete()}
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$box->boxID}" data-confirm-message="{lang}wcf.acp.box.delete.confirmMessage{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnBoxID">{@$box->boxID}</td>
						<td class="columnTitle columnName"><a href="{link controller='BoxEdit' id=$box->boxID}{/link}">{$box->name}</a></td>
						<td class="columnText columnBoxType">{$box->boxType}</td>
						<td class="columnText columnPosition">{$box->position}</td>
						
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
				<li><a href="{link controller='BoxAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.add{/lang}</span></a></li>
				<li><a href="{link controller='BoxAdd'}isMultilingual=1{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.box.addMultilingual{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
