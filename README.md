Money.js-PHP-API
================

Currency coversion library based on money.js


USAGE
=====

	$fx = new Fx();
	echo $fx->convert(1000)->from('USD')->to('GDP');

	$fx->settings(array('from' => 'GDP', 'to' => 'AUD'));
	echo $fx->convert(1000)->done();