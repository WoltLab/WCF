/**
 * Implementation of sortable ajax tables.
 * 
 * @param	string		contentSelector
 * @param	string		targetSelector
 */
WCF.Sortable = Class.extend({
	/**
	 * Initializes 'delete'-Proxy.
	 * 
	 * @param	string	contentSelector	contains the selector for the sortable content. All the content within the element will be reloaded.
	 * @param	string	targetSelector	contains the selector for the element that will be sorted. This is mainly a table, but you can use the targetSelector if you have multiple tables within your contentSelector.
	 */
	init: function(contentSelector, targetSelector) {
		$(contentSelector+''+targetSelector+' thead th').each(function(index, elem) {
			var link = $('a', elem);
			
			// remove the href attribute to disable webpage load
			link.attr('data-url', link.attr('href'));
			link.removeAttr('href');
			
			// fetch click event to load the table via AJAX
			link.click(function() {
				// load content
				$.ajax($(this).attr('data-url'), {
					data: {
						ajax: true
					},
					success: function(data) {
						$(contentSelector).html(data);
						new WCF.Sortable(contentSelector);
					}
				});
			});
		});
	}
});