/*! Copyright (C) Eunsoo Lee. All rights reserved. */
/*  Source of jQuery (https://api.jquery.com/jquery.getscript/) */

"use strict";

(function ($) {
	$.cachedScript = function (url, options) {
		options = $.extend(options || {}, {
			"dataType": "script",
			"cache": true,
			"url": url
		});

		return $.ajax(options);
	};
})(jQuery);

/* End of file CachedScript.js */
/* Location: ./modules/notification/libraries/extends/CachedScript/CachedScript.js */
