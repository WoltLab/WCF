(function(window) {
	var orgRequire = window.require;
	var queue = [];
	var counter = 0;
	
	window.require = function(dependencies, callback) {
		if (!Array.isArray(dependencies)) {
			return orgRequire.apply(window, arguments);
		}
		
		var i = counter++;
		queue.push(i);
		
		orgRequire(dependencies, function() {
			var args = arguments;
			
			queue[queue.indexOf(i)] = function() { callback.apply(window, args); };
			
			executeCallbacks();
		});
	};
	window.require.config = orgRequire.config;
	
	function executeCallbacks() {
		while (queue.length) {
			if (typeof queue[0] !== 'function') {
				break;
			}
			
			queue.shift()();
		}
	};
})(window);
