<p>{lang}wcf.acp.tag.setAsSynonyms.description{/lang}</p>

<ul class="containerBoxList">
	{foreach from=$tags item=tag}
		<li>
			<label><input type="radio" name="tagID" value="{@$tag->tagID}"> <span class="badge tag">{$tag->name}</span></label>
		</li>
	{/foreach}
</ul>

<div class="formSubmit">
	<button data-type="submit">{lang}wcf.global.button.submit{/lang}</button>
</div>
