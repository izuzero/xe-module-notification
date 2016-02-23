<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

$classesMap = array(
	'lcfirst/lcfirst.php' => array('lcfirst'),
	'msgpack/msgpack.php' => array('msgpack_pack', 'msgpack_unpack')
);

foreach ($classesMap as $class => $methods)
{
	if (!in_array(TRUE, array_map('function_exists', $methods)))
	{
		require_once $class;
	}
}

/* End of file autoload.php */
/* Location: ./modules/notification/classes/polyfill/autoload.php */
