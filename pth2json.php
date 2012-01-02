<?php
/**
 * PTH to JSON converter
 * Converts a pth file to a vaguely equivilent json format, at a given 
 * 1/$resolution.
 * By default it's only a 1/6 resolution. This is because we end up with a 
 * massive set of point, and quite frankly I believe 1/6th of the points is 
 * enough for most needs
 *
 * Requires Dygear's PTH and Node classes
 *  - http://www.lfsforum.net/showthread.php?t=71343&highlight=pth
 *
 * Assumes the pth files are in ./pth
 */

require('PTH.php');

foreach (glob('./pth/*.pth') as $src)
{
	$dst = preg_replace('/.pth$/', '.json', $src);
	if (!pth2json($src, $dst))
		throw new Exception('Failed to write file '.$dst);

	echo 'Converted ',$src,' to ',$dst,"\n";
}

function pth2json($src, $dst, $resolution = 6)
{
	$pth = new PTH($src);

	$data = array(
		'track' => str_replace('.pst', '', basename($src)),
		'resolution' => round(1/$resolution, 2),
		'version' => $pth->Version, 
		'revision' => $pth->Revision, 
		'nodes' => array(), 
	);

	$i = 0;

	foreach ($pth->Node as $n)
	{
		if ($i == $pth->FinishLine)
			$data['startfinish'] = array('x' => $n->Center->X, 'y' => $n->Center->Y, 'z' => $n->Center->Z);

		if ($i % $resolution)
			$data['nodes'][] = array('x' => $n->Center->X, 'y' => $n->Center->Y, 'z' => $n->Center->Z);
		$i++;
	}

	$json = json_encode($data);

	if (file_put_contents($dst, $json) === false)
		return false;

	return true;
}

