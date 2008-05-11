<?php
class partyTime extends Plugin {
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
		$this->add_template('event.single', dirname(__FILE__) . '/event.single.php');
		
		Post::add_new_type('event');
	}
	
	public function action_add_template_vars($theme, $vars) {
		
		if(isset($vars['slug'])) {
			$theme->event = partyTime::get($vars['slug']);
			$theme->event_out = partyTime::out($vars['slug']);
		}
	}
	
	public function filter_rewrite_rules( $rules ) {
		$rules[] = new RewriteRule(array(
			'name' => 'display_event',
			'parse_regex' => '%event/(?P<slug>.*)[\/]?$%i',
			'build_str' =>  'event/{$slug}',
			'handler' => 'UserThemeHandler',
			'action' => 'display_event',
			'priority' => 6,
			'is_active' => 1,
		));
		
		return $rules;
	}
	
	public function action_handler_display_event($vars) {		
		$post = Post::get(array('slug' => $vars['slug']));
		
		$this->theme->assign( 'post', $post);
		$this->theme->display( 'event.single' );
		
		exit;
	}
	
	public function get($slug) {
		$post = Post::get(array('slug' => $slug));
		
		
		if($post->content_type == Post::type('event')) {
			$return = $post;
			$return->start = $post->event_start;
			$return->end = $post->event_end;
			$return->location = $post->event_location;
			
			return $return;
		} else {
			return FALSE;
		}
		
	}
	
	public function out($slug) {
		if($event = $this->get($slug)) {
			return $this->html($event);
		} else {
			return FALSE;
		}
	}
	
	public function html($event) { ?>
		<div id="hcalendar-<?php echo $event->slug; ?>" class="vevent">
			<a href="<?php echo $event->permalink; ?>" class="url">
				<?php if(strlen($event->info->start) > 0) { ?><abbr title="<?php echo date("Ymd\THiO", $event->info->start); ?>" class="dtstart"><?php echo date("F jS, Y g:ia", $post->info->start); ?></abbr>, <?php } ?>
				<?php if(strlen($event->info->end) > 0) { ?><abbr title="<?php echo date("Ymd\THiO", $event->info->end); ?>" class="dtend"><?php echo date("F jS, Y g:ia", $post->info->end); ?></abbr><?php } ?>
				<span class="summary"><?php echo $event->title; ?></span>
				<?php if(strlen($event->info->location) > 0) { ?>– at <span class="location"><?php echo $event->info->location; ?></span><?php } ?>
			</a>
			<div class="description"><?php echo $event->content_out; ?></div>
			<div class="tags">Tags: <?php echo $event->tags_out; ?></div>
		</div>
	<?php }
	
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
			
			$controls['Details'] = $output;
		}
		
		return $controls;
	}
	
	public function action_post_update_status( $post, $new_status ) {
		$vars = Controller::get_handler_vars();
		
		if($post->content_type == Post::type('event')) {
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