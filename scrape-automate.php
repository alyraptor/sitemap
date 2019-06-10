<?php

//========================
// Initialize
//========================

include 'utility.php';

function get_http_response_code($url) {
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}

function arr_length($array) {
	$largest = 0;

	foreach($array as $value) {
		if(is_array($value)) {
			$count = count($value);
			if($count > $largest) { $largest = $count; }
		} else {
			if(1 > $largest) { $largest = 1; }
		}
	}

	return $largest;
}

function validate_links($link, $url) {
	// Utility arrays
	global $default_links, $corporate_finance_nav, $default_wa_links;

	if(
		($link->href != null)
		&& !in_array($link->href, $default_links)
		&& !stripos($link, 'nav2')
		&& ($link->href != '')
		&& ($link->href != '#')
		&& !stripos($link->parent()->parent(), 'leftmenu')
		&& (!stripos($url, '/corporate-finance/') || !in_array($link->href, $corporate_finance_nav))
		&& (!stripos($url, '/wealth-advisors/') || !in_array($link->href, $default_wa_links))
	) {
		return true;
	} else {
		return false;
	}
}

function scrape_automate($url, $num) {

	$time_start = microtime(true);
	$line_array = [];

	// Utility arrays
	global $default_images, $default_wa_images, $default_links, $corporate_finance_nav, $url_categories, $letter, $personnel_pattern;

	// Fields
	global $page_type, $video_player, $testimonial, $casestudy;
	$page_type = null;
	$video_player = null;
	$testimonial = null;
	$casestudy = null;
	$images_array = [];
	$images = [];
	$images2 = [];
	$documents = [];
	$other_links = [];
	$related_articles = [];
	$contacts = [];
	$testimonials = [];

	//========================
	// Main Loop
	//========================

	if($url != null) {
		$http_response = get_http_response_code($url);
		if($http_response != '200') {
			if($http_response == '404') {
				$master_array = array('Page Type' => 'PAGE REMOVED');
				return $master_array;
			} else {
				echo '<br>Error! HTTP Header Code: ' . $http_response . '<br>';
				return;
			}
		}
	
		$web_content = file_get_html($url);

		//============ Page Type
		foreach($url_categories as $proper => $string) {
			$search_string = '.com/' . $string;
			if(strpos($url, $search_string) !== false) {
				$page_type[] = $proper;
			}
		}

		if($page_type[0] == null) {$page_type = Array('');}

		//============ Documents
		foreach($web_content->find('ul[id=demo-accordion]') as $ul) {
			if(stripos($ul, 'case studies') || stripos($ul, 'solution sheets') || stripos($ul, 'related links') || stripos($ul, 'white papers')) {
				foreach($ul->find('a') as $link) {
					$doc_string = $num . ' ' . $link->plaintext . ' (' . $link->href . ')';
					if(!in_array($doc_string, $documents)) {
						$documents[] = $doc_string;
					}
				}
			}
		}
		if($documents == null) { $documents = Array('None'); }

		//============ Images
		foreach($web_content->find('img') as $img) {
			if(
				!in_array($img->src, $default_images)
				&& !in_array($img->src, $default_wa_images)
				&& $img->src != null
				&& !preg_match($personnel_pattern, $img->src)
			) {
				$ia_string = $num . ' ' . $img->src; 
				if(!in_array($ia_string, $images_array)) {
					$images_array[] = $ia_string;
				}
			}
		}

		$images_len = count($images_array);
		$images = array_slice($images_array, 0, $images_len / 2);
		$images2 = array_slice($images_array, $images_len / 2);
		if(count($images) != count($images2)) { $images[] = $images2[0]; array_shift($images2); }
		if($images == null) { $images = Array('None'); $images2 = Array('None'); }

		//============ Video Players
		$video_player = null;
		$youtube_links = $web_content->find('div.ms-slide a');
		foreach($youtube_links as $link) {
			if(strpos($link->href, 'youtube')) {
				if($video_player == null) {
					$video_player[] = 'Youtube';
				} else {
					$video_player[0] .= ', Youtube';
				}
			}
		}
		$brightcove_links = $web_content->find('object.BrightcoveExperience');
		foreach($brightcove_links as $link) {
			if($video_player == null) {
				$video_player[] = 'Brightcove';
			} else {
				$video_player[0] .= ', Brightcove';
			}
		}
		if($video_player == null) { $video_player = Array('None'); }

		//============ Related Articles
		$ind_articles = $web_content->find('#ind-articles');
		if($ind_articles != null) {
			foreach($ind_articles as $div) {
				if(stripos($div->plaintext, 'Related Articles')) {
					foreach($div->find('a') as $link) {
						$ra_string = $num . ' ' . $link->href;
						if(validate_links($link, $url) && !in_array($ra_string, $related_articles)) {
							$related_articles[] = $ra_string;
						}
					}
				}
			}
		} else {
			$main = $web_content->getElementById('main-content');
			if($main != null) {
				if(strpos($main->plaintext, 'Related Articles')) {
					foreach($web_content->find('a') as $link) {
						if(strpos($link, 'articles') || stripos($link, 'bkdforensics.com') || stripos($link, 'bkdrisk.com') || stripos($link, 'dynamicsgpinsights.com') || stripos($link, 'sageerpinsights.com')) {
							$ra_string = $num . ' ' . $link->href;
							if(!in_array($ra_string, $related_articles)) {
								$related_articles[] = $ra_string;
							}
						}
					}
				}
			}
		}
		if($related_articles == null) { $related_articles = Array('None'); }

		//============ Other Links
		foreach($web_content->find('a') as $link) {
			if(validate_links($link, $url)) {
				$ol_string = $num . ' ' . $link->href;
				if(
					!in_array($ol_string, $other_links)
					&& !in_array($ol_string, $documents)
					&& !in_array($ol_string, $related_articles)
				) {
					$other_links[] = $ol_string;
				}
			}
		}
		if($other_links == null) { $other_links = 'None'; }

		//============ Contacts
		foreach($web_content->find('.pane h3') as $pane => $name) {
			$contacts[] = $name->plaintext . ' - Contact';
		}
		if($contacts == null) {
			foreach($web_content->find('.contact-box h3') as $pane => $name) {
				$contacts[] = $name->plaintext . ' - Contact';
			}
		}
		if($contacts == null) {
			foreach($web_content->find('.contact-box1 h3') as $name) { // Corporate Finance Contacts
				$contacts[] = $name->plaintext . ' - Contact';
			}
		}
		if($contacts == null) { $contacts = Array('None'); }

		//============ Testimonials
		$testy = false;
		foreach($web_content->find('div.quote-body') as $quote) { // Normal Page Testimonials
			$testy = true;
		}
		foreach($web_content->find('div.[data-role=z-tabs]') as $quote) { // Corporate Finance Testimonials
			if(stripos($quote->plaintext, 'testimonial')) {
				$testy = true;
			}
		}
		if(strpos($url, 'select-transactions') !== false) { // Select Transactions Testimonials
			foreach($web_content->find('h3') as $header) {
				if(stripos($header, 'testimonial')) {
					$testy = true;
				}
			}
		}

		if($testy == true) {
			$testimonial[] = 'TESTIMONIAL';
		} else {
			$testimonial = Array('None');
		}

		//============ Case Studies
		$casey = false;
		foreach($web_content->find('ul.tabs') as $tabs) {
			if(stripos($tabs, 'Case Studies')) {
				$casey[] = true;
			}
		}

		if($casey == true) {
			$casestudy[] = 'CASE STUDY';
		} else {
			$casestudy = Array('None');
		}
	}

	$master_array = Array(
		'Page Type' => $page_type,
		'Documents' => $documents,
		'Images' => $images,
		'Images 2' => $images2,
		'Video Player' => $video_player,
		'Contacts' => $contacts,
		'Other Links' => $other_links,
		'Related Articles' => $related_articles,
		'Case Studies' => $casestudy,
		'Testimonials' => $testimonial
	);

	return $master_array;
}