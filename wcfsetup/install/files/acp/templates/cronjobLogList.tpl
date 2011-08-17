{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/time1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.cronjob.log{/lang}</h1>
		<h2>{lang}wcf.acp.cronjob.subtitle{/lang}</h2>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=CronjobLogList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

{hascontent}
	<form method="post" action="index.php?action=CronjobsLogDelete">
		<div class="border boxTitle">
			<hgroup>
				<h1>{lang}wcf.acp.cronjob.log.data{/lang}</h1>
			</hgroup>
			
			<table>
				<thead>
					<tr>
						<th class="columnCronjobID{if $sortField == 'cronjobID'} active{/if}"><p><a href="index.php?page=CronjobLogList&amp;pageNo={@$pageNo}&amp;sortField=cronjobID&amp;sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.cronjobID{/lang}{if $sortField == 'cronjobID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></p></th>
						<th class="columnClassName{if $sortField == 'className'} active{/if}"><p><a href="index.php?page=CronjobLogList&amp;pageNo={@$pageNo}&amp;sortField=className&amp;sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.className{/lang}{if $sortField == 'className'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></p></th>
						<th class="columnDescription{if $sortField == 'description'} active{/if}"><p><a href="index.php?page=CronjobLogList&amp;pageNo={@$pageNo}&amp;sortField=description&amp;sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.description{/lang}{if $sortField == 'description'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></p></th>
						<th class="columnExecTime{if $sortField == 'execTime'} active{/if}"><p><a href="index.php?page=CronjobLogList&amp;pageNo={@$pageNo}&amp;sortField=execTime&amp;sortOrder={if $sortField == 'execTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.cronjob.log.execTime{/lang}{if $sortField == 'execTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></p></th>
						
						{if $additionalColumns|isset}{@$additionalColumns}{/if}
					</tr>
				</thead>
				
				<tbody>
				{content}
					{foreach from=$cronjobLogs item=cronjobLog}
						<tr>
							<td class="columnCronjobID columnID"><p>{@$cronjobLog->cronjobID}</p></td>
							<td class="columnClassName columnText"><p>{$cronjobLog->className}</p></td>
							<td class="columnDescription columnText"><p>{$cronjobLog->description}</p></td>
							{if $cronjobLog->success}
								<td class="columnExecTime columnDate"><p>{@$cronjobLog->execTime|time} {lang}wcf.acp.cronjob.log.success{/lang}</p></td>
							{elseif $cronjobLog->error}
								<td class="columnExecTime columnText">
									<p>{@$cronjobLog->execTime|time} {lang}wcf.acp.cronjob.log.error{/lang}<br />
									{@$cronjobLog->error}</p>
								</td>
							{else}
								<td class="columnExecTime columnText"></td>
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
	<div class="border content">
		<p class="info">{lang}wcf.acp.cronjob.log.noEntries{/lang}</p>
	</div>
{/hascontent}

{include file='footer'}
