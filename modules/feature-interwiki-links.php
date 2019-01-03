<?php
register_module([
	"name" => "Interwiki links",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds interwiki link support. Point the se",
	"id" => "feature-interwiki-links",
	"code" => function() {
		global $settings;
		if(!empty($settings->interwiki_index_location)) {
			// Generate the interwiki index cache file if it doesn't exist already
			// NOTE: If you want to update the cache file, just delete it & it'll get regenerated automagically :-)
			if(!file_exists($paths->interwiki_index))
				interwiki_index_update();
			else
				$env->interwiki_index = json_decode(file_get_contents($paths->interwiki_index));
		}
	}
]);

/**
 * Updates the interwiki index cache file.
 */
function interwiki_index_update() {
	$env->interwiki_index = new stdClass();
	$interwiki_csv_handle = fopen($settings->interwiki_index_location, "r");
	if($interwiki_csv_handle === false)
		throw new Exception("Error: Failed to read interwiki index from '{$settings->interwiki_index_location}'.");
	
	fgetcsv($interwiki_csv_handle); // Discard the header line
	while(($interwiki_data = fgetcsv($interwiki_csv_handle))) {
		$interwiki_def = new stdClass();
		$interwiki_def->name = $interwiki_data[0];
		$interwiki_def->prefix = $interwiki_data[1];
		$interwiki_def->root_url = $interwiki_data[2];
		
		$env->interwiki_index->$prefix = $interwiki_def;
	}
	
	file_put_contents($paths->interwiki_index, json_encode($env->interwiki_index, JSON_PRETTY_PRINT));
}

/**
 * Parses an interwiki pagename into it's component parts.
 * @param  string	$interwiki_pagename	The interwiki pagename to parse.
 * @return string[]	An array containing the parsed components of the interwiki pagename, in the form ["prefix", "page_name"].
 */
function interwiki_pagename_parse($interwiki_pagename) {
	if(strpos($interwiki_pagename, ":") === false)
		return null;
	$result = explode(":", $interwiki_pagename, 2);
	return array_map("trim", $result);
}

/**
 * Resolves an interwiki pagename to the associated
 * interwiki definition object.
 * @param	string		$interwiki_pagename	An interwiki pagename. Should be in the form "prefix:page name".
 * @return	stdClass	The interwiki definition object.
 */
function interwiki_pagename_resolve($interwiki_pagename) {
	global $env;
	// If it's not an interwiki link, then don't bother confusing ourselves
	if(strpos($interwiki_pagename, ":") === false)
		return null;
	
	[$prefix, $pagename] = interwiki_pagename_parse($interwiki_pagename); // Shorthand destructuring - introduced in PHP 7.1
	
	if(empty($env->interwiki_index->$prefix))
		return null;
	
	return $env->interwiki_index->$prefix;
}
/**
 * Converts an interwiki pagename into a url.
 * @param	string	$interwiki_pagename		The interwiki pagename (in the form "prefix:page name")
 * @return	string	A url that points to the specified interwiki page.
 */
function interwiki_get_pagename_url($interwiki_pagename) {
	$interwiki_def = interwiki_pagename_resolve($interwiki_pagename);
	if($interwiki_def == null)
		return null;
	
	[$prefix, $pagename] = interwiki_pagename_parse($interwiki_pagename);
	
	return str_replace(
		"{{page_name}}", rawurlencode($pagename),
		$interwiki_def->root_url
	);
}

?>