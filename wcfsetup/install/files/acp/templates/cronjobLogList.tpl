{include file='header'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.log{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="CronjobLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
</div>

{hascontent}
	<form method="post" action="{link controller='CronjobLogDelete'}{/link}">
		<div class="tabularBox tabularBoxTitle marginTop shadow">
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
						
						{event name='headColumns'}
					</tr>
				</thead>
				
				<tbody>
					{content}
						{foreach from=$objects item=cronjobLog}
							<tr>
								<td class="columnID columnCronjobID"><p>{@$cronjobLog->cronjobID}</p></td>
								<td class="columnTitle columnClassName"><p>{$cronjobLog->className}</p></td>
								<td class="columnText columnDescription"><p>{$cronjobLog->description}</p></td>
								{if $cronjobLog->success}
									<td class="columnDate columnExecTime"><p>{@$cronjobLog->execTime|time} {lang}wcf.acp.cronjob.log.success{/lang}</p></td>
								{elseif $cronjobLog->error}
									<td class="columnDate columnExecTime">
										<p>{@$cronjobLog->execTime|time} {lang}wcf.acp.cronjob.log.error{/lang}<br />
										{@$cronjobLog->error}</p>
									</td>
								{else}
									<td class="columnDate columnExecTime"></td>
								{/if}
							
								{event name='columns'}
							</tr>
						{/foreach}
					{/content}
				</tbody>
			</table>
			
		</div>
		
		<div class="formSubmit">
			<input type="submit" onclick="return confirm('{lang}wcf.acp.cronjob.log.clear.confirm{/lang}')" value="{lang}wcf.acp.cronjob.log.clear{/lang}" accesskey="c" />
		</div>
	</form>
	
	<div class="contentNavigation">
		{@$pagesLinks}
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.acp.cronjob.log.noEntries{/lang}</p>
{/hascontent}

{include file='footer'}
