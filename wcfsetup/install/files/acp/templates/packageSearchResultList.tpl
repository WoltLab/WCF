<table class="table">
	<thead>
		<tr>
			<th colspan="2" class="columnTitle">{lang}wcf.acp.package.name{/lang}</th>
			<th class="columnText">{lang}wcf.acp.package.author{/lang}</a></th>
			<th class="columnText">{lang}wcf.acp.package.license{/lang}</th>
			<th class="columnDate">{lang}wcf.acp.package.packageDate{/lang}</a></th>
			
			{event name='headColumns'}
		</tr>
	</thead>
	
	<tbody>
		{if $officialPackages|count}
			<tr>
				<td colspan="5"><small>{lang count=$officialPackages|count}wcf.acp.package.search.result.official{/lang}</small></td>
			</tr>
			{include file='packageSearchResultListItems' packages=$officialPackages}
		{/if}
		{if $trustedSources|count}
			<tr>
				<td colspan="5"><small>{lang count=$trustedSources|count}wcf.acp.package.search.result.trusted{/lang}</small></td>
			</tr>
			{include file='packageSearchResultListItems' packages=$trustedSources}
		{/if}
		{if $thirdPartySources|count}
			<tr>
				<td colspan="5"><small>{lang count=$thirdPartySources|count}wcf.acp.package.search.result.trusted{/lang}</small></td>
			</tr>
			{include file='packageSearchResultListItems' packages=$thirdPartySources}
		{/if}
	</tbody>
</table>
