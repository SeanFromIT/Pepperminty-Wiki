<?php
register_module([
	"name" => "Raw page source",
	"version" => "0.8",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'raw' action that shows you the raw source of a page.",
	"id" => "action-raw",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=raw&page={pageName}[&typeheader={typeName}] Get the raw source code of a page
		 * @apiName RawSource
		 * @apiGroup Page
		 * @apiPermission Anonymous
		 * 
		 * @apiParam	{string}	page		The page to return the source of.
		 * @apiParam	{string}	typeheader	Optional; v0.22+. The content-type header to set on the response. If not set, defaults to text/markdown. Valid values: plaintext (returns text/plain). Does NOT change the content delivered. Useful for debugging if your browser doesn't display text returned with text/markdown.
		 */
		
		/*
		 * ██████   █████  ██     ██ 
		 * ██   ██ ██   ██ ██     ██ 
		 * ██████  ███████ ██  █  ██ 
		 * ██   ██ ██   ██ ██ ███ ██ 
		 * ██   ██ ██   ██  ███ ███  
		 */
		add_action("raw", function() {
			global $pageindex, $env;
			
			if(empty($pageindex->{$env->page})) {
				http_response_code(404);
				exit("Error: The page with the name $env->page could not be found.\n");
			}
			if(isset($_GET["typeheader"]) && $_GET["typeheader"] == "plaintext")
				header("content-type: text/plain");
			else
				header("content-type: text/markdown");
			header("content-disposition: inline");
			header("content-length: " . filesize($env->page_filename));
			exit(file_get_contents($env->page_filename));
		});
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);

?>
