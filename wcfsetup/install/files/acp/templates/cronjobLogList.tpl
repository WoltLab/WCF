{include file='header' pageTitle='wcf.acp.cronjob.log'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		$('.jsCronjobLogDelete').click(function() {
			WCF.System.Confirmation.show('{lang}wcf.acp.cronjob.log.clear.confirm{/lang}', function(action) {
				if (action == 'confirm') {
					new WCF.Action.Proxy({
						autoSend: true,
						data: {
							actionName: 'clearAll',
							className: 'wcf\\data\\cronjob\\log\\CronjobLogAction'
						},
						success: function() {
							window.location.reload();
						}
					});
				}
			});
		});
		
		$('.jsCronjobError').click(function(event) {
			$(event.currentTarget).next().wcfDialog({
				title: '{lang}wcf.acp.cronjob.log.error.details{/lang}'
			});
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.log{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="CronjobLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{if $objects|count}
					<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete"><img src="{@$__wcf->getPath()}icon/delete.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
				{/if}
				
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.cronjob.log{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.cronjob.log.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnCronjobID{if $sortField == 'cronjobID'} active{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=cronjobID&sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'cronjobID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnClassName{if $sortField == 'className'} active{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.className{/lang}{if $sortField == 'className'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnDescription{if $sortField == 'description'} active{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=description&sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.description{/lang}{if $sortField == 'description'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnExecTime{if $sortField == 'execTime'} active{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=execTime&sortOrder={if $sortField == 'execTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.log.execTime{/lang}{if $sortField == 'execTime'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnSuccess{if $sortField == 'success'} active{/if}"><a href="{link controller='CronjobLogList'}pageNo={@$pageNo}&sortField=success&sortOrder={if $sortField == 'success' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.cronjob.log.status{/lang}{if $sortField == 'success'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=cronjobLog}
						<tr>
							<td class="columnID columnCronjobID"><p>{@$cronjobLog->cronjobID}</p></td>
							<td class="columnTitle columnClassName"><p>{$cronjobLog->className}</p></td>
							<td class="columnText columnDescription"><p>{$cronjobLog->description|language}</p></td>
							<td class="columnDate columnExecTime"><p>{if $cronjobLog->execTime}{@$cronjobLog->execTime|time}{/if}</p></td>
							
							<td class="columnText columnSuccess"><p>
							{if $cronjobLog->success}
								<span class="badge green">{lang}wcf.acp.cronjob.log.success{/lang}</span>
							{elseif $cronjobLog->error}	
								<a class="badge red jsTooltip jsCronjobError" title="{lang}wcf.acp.cronjob.log.error.showDetails{/lang}">{lang}wcf.acp.cronjob.log.error{/lang}</a>
								<span style="display: none">{@$cronjobLog->error}</span>
							{/if}
							</p></td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a title="{lang}wcf.acp.cronjob.log.clear{/lang}" class="button jsCronjobLogDelete"><img src="{@$__wcf->getPath()}icon/delete.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.cronjob.log.clear{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.acp.cronjob.log.noEntries{/lang}</p>
{/hascontent}

{include file='footer'}
