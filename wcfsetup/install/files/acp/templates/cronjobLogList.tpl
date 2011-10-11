{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/time1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.log{/lang}</h1>
		<h2>{lang}wcf.acp.cronjob.subtitle{/lang}</h2>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php/CronjobLogList/?pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

{hascontent}
	<form method="post" action="index.php/CronjobLogDelete/">
		<div class="border boxTitle">
			<hgroup>
				<h1>{lang}wcf.acp.cronjob.log{/lang} <span class="badge" title="{lang}wcf.acp.cronjob.log.count{/lang}">{#$items}</span></h1>
			</hgroup>
			
			<table>
				<thead>
					<tr>
						<th class="columnID columnCronjobID{if $sortField == 'cronjobID'} active{/if}"><a href="index.php/CronjobLogList/?pageNo={@$pageNo}&amp;sortField=cronjobID&amp;sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.global.objectID{/lang}{if $sortField == 'cronjobID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
						<th class="columnTitle columnClassName{if $sortField == 'className'} active{/if}"><a href="index.php/CronjobLogList/?pageNo={@$pageNo}&amp;sortField=className&amp;sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.className{/lang}{if $sortField == 'className'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
						<th class="columnText columnDescription{if $sortField == 'description'} active{/if}"><a href="index.php/CronjobLogList/?pageNo={@$pageNo}&amp;sortField=description&amp;sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.description{/lang}{if $sortField == 'description'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
						<th class="columnDate columnExecTime{if $sortField == 'execTime'} active{/if}"><a href="index.php/CronjobLogList/?pageNo={@$pageNo}&amp;sortField=execTime&amp;sortOrder={if $sortField == 'execTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.log.execTime{/lang}{if $sortField == 'execTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
						
						{if $additionalColumns|isset}{@$additionalColumns}{/if}
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
							
								{if $cronjobLog->additionalColumns|isset}{@$cronjobLog->additionalColumns}{/if}
							</tr>
						{/foreach}
					{/content}
				</tbody>
			</table>
			
		</div>
		
		<div class="formSubmit">
			{@SID_INPUT_TAG}
			<input type="submit" onclick="return confirm('{lang}wcf.acp.cronjob.log.clear.confirm{/lang}')" value="{lang}wcf.acp.cronjob.log.clear{/lang}" accesskey="c" />
		</div>
	</form>
	
	<div class="contentFooter">
		{@$pagesLinks}
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.acp.cronjob.log.noEntries{/lang}</p>
{/hascontent}

{include file='footer'}
