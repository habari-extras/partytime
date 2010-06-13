<?php
class partyTime extends Plugin {
	
	public function action_init() {		
		
		// Double-check to make sure we have our event type		
		Post::add_new_type('event');
			
	}
	
	/**
	 * install on activation
	 */
	public function action_plugin_activation( $plugin_file )
	{
		Post::add_new_type( 'event' );
	}
	
	/**
	 * deactivate post type on deactivation
	 */
	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'event' );
	}
	
	/**
	 * install:
	 * - post type
	 * - permissions
	 */
	static public function install() {
		Post::add_new_type( 'event' );
		Post::activate_post_type( 'event' );
		
		// Give anonymous users access
		$group = UserGroup::get_by_name('anonymous');
		$group->grant('post_event', 'read');
	}
	
	/**
	 * Create name string
	 **/
	public function filter_post_type_display($type, $foruse) 
	{
		$names = array( 
			'event' => array(
				'singular' => _t('Event'),
				'plural' => _t('Events'),
			)
		); 
 		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}
	
	// public function action_add_template_vars($theme, $vars) {
	// 	
	// 	if(isset($vars['slug'])) {
	// 		$theme->event = partyTime::get($vars['slug']);
	// 		$theme->event_out = partyTime::out($vars['slug']);
	// 	}
	// }
	
	// public function filter_rewrite_rules( $rules ) {
	// 	$rules[] = new RewriteRule(array(
	// 		'name' => 'display_event',
	// 		'parse_regex' => '%event/(?P<slug>.*)[\/]?$%i',
	// 		'build_str' =>  'event/{$slug}',
	// 		'handler' => 'UserThemeHandler',
	// 		'action' => 'display_event',
	// 		'priority' => 6,
	// 		'is_active' => 1,
	// 	));
	// 	
	// 	return $rules;
	// }
	
	// public function action_handler_display_event($vars) {		
	// 	$post = Post::get(array('slug' => $vars['slug']));
	// 	
	// 	$this->theme->assign( 'post', $post);
	// 	$this->theme->display( 'event.single' );
	// 	
	// 	exit;
	// }
	
	public function get($slug) {
		$post = Post::get(array('slug' => $slug, 'content_type' => Post::type('event')));
		
		return $post;
		
	}

	/**
	 * Modify publish form
	 */
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('event')) {
			// Change content name
			$form->content->caption= _t('Description');
			
			// Create event tab
			$event_controls = $form->publish_controls->append('fieldset', 'event_controls', _t('Event'));
			
			$event_controls->append('text', 'start', 'null:null', _t('Start Date'), 'tabcontrol_text');
			$event_controls->start->value = $post->start->format('Y-m-d H:i:s');
									
			$event_controls->append('text', 'end', 'null:null', _t('End Date (Optional)'), 'tabcontrol_text');
			$event_controls->end->value = $post->end->format('Y-m-d H:i:s');
			
			$event_controls->append('text', 'location', 'null:null', _t('Location'), 'tabcontrol_text');
			$event_controls->location->value = $post->location;
			
		}
	}
	
	/**
	 * Save our data to the database
	 */
	public function action_publish_post( $post, $form )
	{
		if ($post->content_type == Post::type('event')) {
			$this->action_form_publish($form, $post);
			
			$post->info->event_start= HabariDateTime::date_create($form->start->value)->sql;
			$post->info->event_end= HabariDateTime::date_create($form->end->value)->sql;
			$post->info->event_location= $form->location->value;
		}
	}
	
	/**
	 * filter the dynamic start property of posts
	 */
	public function filter_post_start($start_date, $post) {
		if($post->content_type == Post::type('event')) {
			return HabariDateTime::date_create($post->info->event_start);
		}
		else {
			return $start_date;
		}
	}
	
	/**
	 * filter the dynamic end property of posts
	 */
	public function filter_post_end($end_date, $post) {
		if($post->content_type == Post::type('event')) {
			return HabariDateTime::date_create($post->info->event_end);
		}
		else {
			return $end_date;
		}
	}
	
	/**
	 * filter the dynamic location property of posts
	 */
	public function filter_post_location($end_date, $post) {
		if($post->content_type == Post::type('event')) {
			return $post->info->event_location;
		}
		else {
			return $end_date;
		}
	}
	
	/**
	 * Filter function called by the plugin hook `rewrite_rules`
	 * Add a new rewrite rule to the database's rules.
	 *
	 *
	 * @param array $db_rules Array of rewrite rules compiled so far
	 * @return array Modified rewrite rules array, we added our custom rewrite rule
	 */
	public function filter_rewrite_rules( $db_rules )
	{
		$db_rules[]= RewriteRule::create_url_rule( '"events"', 'partyTime', 'display_calendar' );

		return $db_rules;
	}
	
	/**
	 * Act function called by the `Controller` class.
	 * Dispatches the request to the proper action handling function.
	 *
	 * @param string $action Action called by request, we only support 'amcd' and 'host-meta'
	 */
	public function act( $action )
	{
		switch ( $action )
		{
			case 'display_calendar':
				self::display_calendar();
				break;
		}
	}
	
	public function display_calendar()
	{
		$theme = Themes::create();
		
		$events = Posts::get( array ( "status" => Post::status('published'), "nolimit" => true, "content_type" => Post::type('event') ) );
		
		$theme->events = $events;
		
		$theme->display('calendar');
		

	}
	
	
	
}
?>
