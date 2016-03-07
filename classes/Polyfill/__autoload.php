<?php
/*! Copyright (C) Eunsoo Lee. All rights reserved. */

$classesMap = array(
	'msgpack/msgpack.php' => array('msgpack_pack', 'msgpack_unpack')
);

foreach ($classesMap as $class => $methods)
{
	if (!in_array(TRUE, array_map('function_exists', $methods)))
	{
		require_once __DIR__ . '/' . $class;
	}
}

/* End of file __autoload.php */
/* Location: ./modules/notification/classes/Polyfill/__autoload.php */
