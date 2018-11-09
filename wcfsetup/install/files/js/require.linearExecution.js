(function(window) {
	var orgRequire = window.require;
	var queue = [];
	var counter = 0;

	window.orgRequire = orgRequire
	
	window.require = function(dependencies, callback, errBack) {
		if (!Array.isArray(dependencies)) {
			return orgRequire.apply(window, arguments);
		}
		
		var promise = new Promise(function (resolve, reject) {
			var i = counter++;
			queue.push(i);
			
			orgRequire(dependencies, function () {
				var args = arguments;
				
				queue[queue.indexOf(i)] = function() { resolve(args); };
				
				executeCallbacks();
			}, function (err) {
				queue[queue.indexOf(i)] = function() { reject(err); };
				
				executeCallbacks();
			});
		});
		
		if (callback) {
			promise = promise.then(function (objects) {
				return callback.apply(window, objects);
			});
		}
		if (errBack) {
			promise.catch(errBack);
		}
		
		return promise;
	};
	window.require.config = orgRequire.config;
	
	function executeCallbacks() {
		while (queue.length) {
			if (typeof queue[0] !== 'function') {
				break;
			}
			
			queue.shift()();
		}
	}
})(window);
