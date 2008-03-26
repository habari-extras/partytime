<?php
class partyTime extends Plugin {
	private $theme= null;
	
	function info() {
		return array(
			'name' => 'partyTime',
			'version' => '1.0',
			'url' => 'http://myfla.ws/projects/partytime/',
			'author' => 'Arthus Erea',
			'authorurl' => 'http://myfla.ws',
			'license' => 'Creative Commons Attribution-Share Alike 3.0',
			'description' => 'partyTime can be used to share events'
		);
	}
	
	public function action_init() {
		Post::add_new_type('event');
	}
	
	public function filter_publish_controls ($controls, $post) {
		$vars = Controller::get_handler_vars();
				
		if($vars['content_type'] == Post::type('event')) {
			$output = '';
			
			$output .= '<div class="text container"><p class="column span-5"><label for="event_start">Starts:</label></p><p class="column span-14 last"><input type="text" id="event_start" name="event_start" value="';
			if(strlen($post->info->event_start) > 0) {
				$output .= date('Y-m-d H:i:s', $post->info->event_start);
			}
			$output .= '" /></p></div>';
			$output .= '<div class="text container"><p class="column span-5"><label for="event_end">Ends: <small>Optional</small></label></p><p class="column span-14 last"><input type="text" id="event_end" name="event_end" value="';
			if(strlen($post->info->event_end) > 0) {
				$output .= date('Y-m-d H:i:s', $post->info->event_end);
			}
			$output .= '" /></p></div>';
			$output .= '<div class="text container"><p class="column span-5"><label for="event_location">Location:</label></p><p class="column span-14 last"><input type="text" id="event_location" name="event_location" value="' . $post->info->event_location . '" /></p></div>';
			
			$controls['Event'] = $output;
		}
		
		return $controls;
	}
	
	public function action_post_update_status( $post, $new_status ) {
		$vars = Controller::get_handler_vars();
		
		if($vars['content_type'] == Post::type('event')) {
			partyTime::set($post);
		}
	}
	
	function set($post) {
		$vars = Controller::get_handler_vars();
		
		if((strlen($vars['event_start']) > 0) && (strlen($vars['event_end']) > 0)) {
			$post->info->event_start = strtotime($vars['event_start']);
			$post->info->event_end = strtotime($vars['event_end']);
		} elseif((strlen($vars['event_start']) > 0)) {
			$post->info->event_start = strtotime($vars['event_start']);
			$post->info->event_end = '';
		} elseif((strlen($vars['event_end']) > 0)) {
			$post->info->event_start = strtotime(date('Y-m-d', strtotime($vars['event_end'])));
			$post->info->event_end = $post->info->event_start + (60*60*24);
		}
		
		if(strlen($vars['event_location']) > 0) {
			$post->info->event_location = $vars['event_location'];
		}
		
	}
}
?>