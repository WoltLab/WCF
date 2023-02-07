<section class="section">
	<h2 class="sectionTitle">{lang}wcf.acp.box.settings{/lang}</h2>
	
	<dl>
		<dt></dt>
		<dd>
			<label>
				<input type="checkbox" name="showChildCategories" value="1"{if $showChildCategories} checked{/if}>
				{lang}wcf.acp.box.showChildCategories{/lang}
			</label>
		</dd>
	</dl>
	
	{event name='fields'}
</section>
