{foreach from=$pageNotices item=pageNotice}
	<woltlab-core-notice type="{$pageNotice->type}" class="pageNotice" id="pageNotice{$pageNotice->pageNoticeID}">
		<div class="pageNotice__message htmlContent">
			{unsafe:$pageNotice->getFormattedMessage()}
		</div>

		{if $pageNotice->canEdit()}
			<div class="pageNotice__footer">
				{if $pageNotice->isInternal}
					<span class="badge">
						{icon name='lock'}
						<span>{lang}wcf.pageNotice.internal{/lang}</span>
					</span>
				{/if}
				<span class="pageNotice__meta inlineItems dotSeparated">
					<span class="inlineItem">{if $pageNotice->userID}{user object=$pageNotice->getUserProfile()}{else}{$pageNotice->username}{/if}</span>
					<span class="inlineItem">{time time=$pageNotice->time}</span>
				</span>
				<div class="pageNotice__interactions">
					{unsafe:$pageNoticeContextMenus[$pageNotice->pageNoticeID]->render()}
				</div>
			</div>
		{/if}
	</woltlab-core-notice>
{/foreach}
