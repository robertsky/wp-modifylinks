<?php
/*
Plugin Name: Modify Links
Plugin URI: http://robertsky.com
Description: This plugin modifies links in posts upon saves, adding title automatically.
Version: 0.0.1
Author: Robert Sim
Author URI: http://robertsky.com
Text Domain: wp-modifylinks
*/

function rsky_modify_link_posts($data) {

	//Get content
	$dom = new DOMDocument;
	$dom->loadHTML($data);
	foreach ($dom->getElementsByTagName('a') as $node) {
		if($node->hasAttribute('href')){
			if(!($node->hasAttribute('title'))) {
				$url = $node->getAttribute('href')->textContent;
				$url = esc_url_raw(trim($url,'"'));
				$args = array(
					'sslverify' => false,
					);
				$webpage = wp_remote_get($url);
				if(!is_wp_error($webpage)) {
					if(wp_remote_retrieve_response_code($webpage) === 200 && $body = wp_remote_retrieve_body($webpage)) {
						$remote_dom = new DOMDocument;
						$remote_dom->loadHTML($body);
						$title = $dom->getElementsByTagName('title');
						if($title->length > 0) {
							$node->setAttribute('title', $title->item(0)->textContent);
						}
					}
				}
			}
		}
	}
	$data = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));
	return $data;
}

add_filter('content_save_pre','rsky_modify_link_posts');