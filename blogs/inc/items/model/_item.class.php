<?php
/**
 * This file implements the Item class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2010 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 * @author fplanque: Francois PLANQUE.
 * @author gorgeb: Bertrand GORGE / EPISTEMA
 * @author mbruneau: Marc BRUNEAU / PROGIDISTRI
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Includes:
 */
load_funcs( 'items/model/_item.funcs.php');
load_class( 'slugs/model/_slug.class.php', 'Slug' );

/**
 * Item Class
 *
 * @package evocore
 */
class Item extends ItemLight
{
	/**
	 * The User who has created the Item (lazy-filled).
	 * @see Item::get_creator_User()
	 * @see Item::set_creator_User()
	 * @var User
	 * @access protected
	 */
	var $creator_User;


	/**
	 * @deprecated by {@link $creator_User}
	 * @var User
	 */
	var $Author;


	/**
	 * ID of the user that created the item
	 * @var integer
	 */
	var $creator_user_ID;


	/**
	 * Login of the user that created the item (lazy-filled)
	 * @var string
	 */
	var $creator_user_login;


	/**
	 * The assigned User to the item.
	 * Can be NULL
	 * @see Item::get_assigned_User()
	 * @see Item::assign_to()
	 *
	 * @var User
	 * @access protected
	 */
	var $assigned_User;

	/**
	 * ID of the user that created the item
	 * Can be NULL
	 *
	 * @var integer
	 */
	var $assigned_user_ID;

	/**
	 * The visibility status of the item.
	 *
	 * 'published', 'deprecated', 'protected', 'private' or 'draft'
	 *
	 * @var string
	 */
	var $status;
	/**
	 * Locale code for the Item content.
	 *
	 * Examples: en-US, zh-CN-utf-8
	 *
	 * @var string
	 */
	var $locale;

	var $content;

	var $titletag;

	/**
	 * Meta Description tag for this post
	 */
	var $metadesc;

	/**
	 * Meta keywords for this post
	 */
	var $metakeywords;

	/**
	 * Lazy filled, use split_page()
	 */
	var $content_pages = NULL;


	var $wordcount;
	/**
	 * The list of renderers, imploded by '.'.
	 * @var string
	 * @access protected
	 */
	var $renderers;
	/**
	 * Comments status
	 *
	 * "open", "disabled" or "closed
	 *
	 * @var string
	 */
	var $comment_status;

	var $pst_ID;
	var $datedeadline = '';
	var $priority;

	/**
	 * @var float
	 */
	var $order;
	/**
	 * @var boolean
	 */
	var $featured;

	var $double1;
	var $double2;
	var $double3;
	var $double4;
	var $double5;
	var $varchar1;
	var $varchar2;
	var $varchar3;

	/**
	 * @var Plugin code used to edit contents of this Item:
	 */
	var $editor_code = NULL; // NULL will use whatever editor was last used

	/**
	 * Have post processing notifications been handled?
	 * @var string
	 */
	var $notifications_status;
	/**
	 * Which cron task is responsible for handling notifications?
	 * @var integer
	 */
	var $notifications_ctsk_ID;

	/**
	 * array of IDs or NULL if we don't know...
	 *
	 * @var array
	 */
	var $extra_cat_IDs = NULL;

	/**
	 * Array of tags (strings)
	 *
	 * Lazy loaded.
	 *
	 * @var array
	 */
	var $tags = NULL;

	/**
	 * Array of Links attached to this item.
	 *
	 * NULL when not initialized.
	 *
	 * @var array
	 * @access public
	 */
	var $Links = NULL;

	/**
	 * Has the publish date been explicitely set?
 	 *
	 * @var integer
	 */
	var $dateset = 1;

	var $priorities;

	/**
	 * Constructor
	 *
	 * @param object table Database row
	 * @param string
	 * @param string
	 * @param string
	 * @param string for derived classes
	 * @param string datetime field name
	 * @param string datetime field name
	 * @param string User ID field name
	 * @param string User ID field name
	 */
	function Item( $db_row = NULL, $dbtable = 'T_items__item', $dbprefix = 'post_', $dbIDname = 'post_ID', $objtype = 'Item',
	               $datecreated_field = 'datecreated', $datemodified_field = 'datemodified',
	               $creator_field = 'creator_user_ID', $lasteditor_field = 'lastedit_user_ID' )
	{
		global $localtimenow, $default_locale, $current_User;

		$this->priorities = array(
				1 => /* TRANS: Priority name */ T_('1 - Highest'),
				2 => /* TRANS: Priority name */ T_('2 - High'),
				3 => /* TRANS: Priority name */ T_('3 - Medium'),
				4 => /* TRANS: Priority name */ T_('4 - Low'),
				5 => /* TRANS: Priority name */ T_('5 - Lowest'),
			);

		// Call parent constructor:
		parent::ItemLight( $db_row, $dbtable, $dbprefix, $dbIDname, $objtype,
	               $datecreated_field, $datemodified_field,
	               $creator_field, $lasteditor_field );

		if( is_null($db_row) )
		{ // New item:
			if( isset($current_User) )
			{ // use current user as default, if available (which won't be the case during install)
				$this->creator_user_login = $current_User->login;
				$this->set_creator_User( $current_User );
			}
			$this->set( 'dateset', 0 );	// Date not explicitely set yet
			$this->set( 'notifications_status', 'noreq' );
			// Set the renderer list to 'default' will trigger all 'opt-out' renderers:
			$this->set( 'renderers', array('default') );
			// we prolluy don't need this: $this->set( 'status', 'published' );
			$this->set( 'locale', $default_locale );
			$this->set( 'priority', 3 );
			$this->set( 'ptyp_ID', 1 /* Post */ );
		}
		else
		{
			$this->datecreated = $db_row->post_datecreated; // Needed for history display
			$this->creator_user_ID = $db_row->post_creator_user_ID; // Needed for history display
			$this->lastedit_user_ID = $db_row->post_lastedit_user_ID; // Needed for history display
			$this->assigned_user_ID = $db_row->post_assigned_user_ID;
			$this->dateset = $db_row->post_dateset;
			$this->status = $db_row->post_status;
			$this->content = $db_row->post_content;
			$this->titletag = $db_row->post_titletag;
			$this->metadesc = $db_row->post_metadesc;
			$this->metakeywords = $db_row->post_metakeywords;
			$this->pst_ID = $db_row->post_pst_ID;
			$this->datedeadline = $db_row->post_datedeadline;
			$this->priority = $db_row->post_priority;
			$this->locale = $db_row->post_locale;
			$this->wordcount = $db_row->post_wordcount;
			$this->notifications_status = $db_row->post_notifications_status;
			$this->notifications_ctsk_ID = $db_row->post_notifications_ctsk_ID;
			$this->comment_status = $db_row->post_comment_status;			// Comments status
			$this->order = $db_row->post_order;
			$this->featured = $db_row->post_featured;
			for( $i = 1 ; $i <= 5; $i++ )
			{
				$this->{'double'.$i} = $db_row->{'post_double'.$i};
			}
			for( $i = 1 ; $i <= 3; $i++ )
			{
				$this->{'varchar'.$i} = $db_row->{'post_varchar'.$i};
			}

			// echo 'renderers=', $db_row->post_renderers;
			$this->renderers = $db_row->post_renderers;
			$this->editor_code = $db_row->post_editor_code;

			$this->views = $db_row->post_views;

			$this->excerpt_autogenerated = $db_row->post_excerpt_autogenerated;
		}
	}


	/**
	 * Set creator user
	 *
	 * @param string login
	 */
	function set_creator_by_login( $login )
	{
		$UserCache = & get_UserCache();
		if( ( $creator_User = &$UserCache->get_by_login( $login ) ) !== false )
		{
			$this->set( $this->creator_field, $creator_User->ID );
		}
	}


	/**
	 * @todo use extended dbchange instead of set_param...
	 * @todo Normalize to set_assigned_User!?
	 */
	function assign_to( $user_ID, $dbupdate = true /* BLOAT!? */ )
	{
		// echo 'assigning user #'.$user_ID;
		if( ! empty($user_ID) )
		{
			if( $dbupdate )
			{ // Record ID for DB:
				$this->set_param( 'assigned_user_ID', 'number', $user_ID, true );
			}
			else
			{
				$this->assigned_user_ID = $user_ID;
			}
			$UserCache = & get_UserCache();
			$this->assigned_User = & $UserCache->get_by_ID( $user_ID );
		}
		else
		{
			// fp>> DO NOT set (to null) immediately OR it may KILL the current User object (big problem if it's the Current User)
			unset( $this->assigned_User );
			if( $dbupdate )
			{ // Record ID for DB:
				$this->set_param( 'assigned_user_ID', 'number', NULL, true );
			}
			else
			{
				$this->assigned_User = NULL;
			}
			$this->assigned_user_ID = NULL;
		}

	}


	/**
	 * Template function: display author/creator of item
	 *
	 */
	function author( $params = array() )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'       => ' ',
				'after'        => ' ',
				'format'       => 'htmlbody',
				'link_to'		   => 'userpage',
				'link_text'    => 'preferredname',
				'link_rel'     => '',
				'link_class'   => '',
				'thumb_size'   => 'crop-32x32',
				'thumb_class'  => '',
			), $params );

		// Load User
		$this->get_creator_User();

		$r = $this->creator_User->get_link( $params );

		echo $params['before'].$r.$params['after'];
	}


	/**
	 * Load data from Request form fields.
	 *
	 * This requires the blog (e.g. {@link $blog_ID} or {@link $main_cat_ID} to be set).
	 *
	 * @param boolean true if we are returning to edit mode (new, switchtab...)
	 * @return boolean true if loaded data seems valid.
	 */
	function load_from_Request( $editing = false, $creating = false )
	{
		global $default_locale, $current_User, $localtimenow;
		global $posttypes_reserved_IDs, $item_typ_ID;

		if( param( 'post_locale', 'string', NULL ) !== NULL )
		{
			$this->set_from_Request( 'locale' );
		}

		if( param( 'item_typ_ID', 'integer', NULL ) !== NULL )
		{
			$this->set_from_Request( 'ptyp_ID', 'item_typ_ID' );

			if ( in_array( $item_typ_ID, $posttypes_reserved_IDs ) )
			{
				param_error( 'item_typ_ID', T_( 'This post type is reserved and cannot be used. Please choose another one.' ), '' );
			}
		}

		if( param( 'post_url', 'string', NULL ) !== NULL )
		{
			param_check_url( 'post_url', 'posting', '' );
			$this->set_from_Request( 'url' );
		}
		// Note: post_url is not part of the simple form, so this message can be a little bit awkward there
		if( $this->status == 'redirected' && empty($this->url) )
		{
			param_error( 'post_url', T_('If you want to redirect this post, you must specify an URL! (Expert mode)') );
		}

		if( $current_User->check_perm( 'edit_timestamp' ) )
		{
			$this->set( 'dateset', param( 'item_dateset', 'integer', 0 ) );

			if( $editing || $this->dateset == 1 )
			{ // We can use user date:
				if( param_date( 'item_issue_date', T_('Please enter a valid issue date.'), true )
					&& param_time( 'item_issue_time' ) )
				{ // only set it, if a (valid) date and time was given:
					$this->set( 'issue_date', form_date( get_param( 'item_issue_date' ), get_param( 'item_issue_time' ) ) ); // TODO: cleanup...
				}
			}
			elseif( $this->dateset == 0 )
			{	// Set date to NOW:
				$this->set( 'issue_date', date('Y-m-d H:i:s', $localtimenow) );
			}
		}

		if( param( 'post_excerpt', 'text', NULL ) !== NULL ) {
			$this->set_from_Request( 'excerpt' );
		}

		if( param( 'post_urltitle', 'string', NULL ) !== NULL ) {
			$this->set_from_Request( 'urltitle' );
		}

		if( param( 'titletag', 'string', NULL ) !== NULL ) {
			$this->set_from_Request( 'titletag', 'titletag' );
		}

		if( param( 'metadesc', 'string', NULL ) !== NULL ) {
			$this->set_from_Request( 'metadesc', 'metadesc' );
		}

		if( param( 'metakeywords', 'string', NULL ) !== NULL ) {
			$this->set_from_Request( 'metakeywords', 'metakeywords' );
		}

		if( param( 'item_tags', 'string', NULL ) !== NULL ) {
			$this->set_tags_from_string( get_param('item_tags') );
			// pre_dump( $this->tags );
		}

		// Workflow stuff:
		param( 'item_st_ID', 'integer', NULL );
		$this->set_from_Request( 'pst_ID', 'item_st_ID', true );

		param( 'item_assigned_user_ID', 'integer', NULL );
		$this->assign_to( get_param('item_assigned_user_ID') );

		param( 'item_priority', 'integer', NULL );
		$this->set_from_Request( 'priority', 'item_priority', true );

		$this->set( 'featured', param( 'item_featured', 'integer', 0 ), false );

		param( 'item_order', 'double', NULL );
		$this->set_from_Request( 'order', 'item_order', true );

		$this->creator_user_login = param( 'item_owner_login', 'string', NULL );

		if( $current_User->check_perm( 'users', 'edit' ) && param( 'item_owner_login_displayed', 'string', NULL ) !== NULL )
		{   // only admins can change this..
			if( param_check_not_empty( 'item_owner_login', T_('Please enter valid owner login.') ) && param_check_login( 'item_owner_login', true ) )
			{
				$this->set_creator_by_login( $this->creator_user_login );
			}
		}

		// CUSTOM FIELDS double
		for( $i = 1 ; $i <= 5; $i++ )
		{	// For each custom double field:
			if( isset_param('item_double'.$i) )
			{ // it is set
				param( 'item_double'.$i, 'double', NULL ); // get par value
				$this->set_from_Request( 'double'.$i, 'item_double'.$i, true );
			}
		}

		// CUSTOM FIELDS varchar
		for( $i = 1 ; $i <= 3; $i++ )
		{	// For each custom varchar field:
			if( param( 'item_varchar'.$i, 'string', NULL ) !== NULL )
			{	// we restrict to string to prevent javascript injection as in <b onhover="steal_cookies()">
				$this->set_from_Request( 'varchar'.$i, 'item_varchar'.$i, true );
			}
		}

		if( param_date( 'item_deadline', T_('Please enter a valid deadline.'), false, NULL ) !== NULL ) {
			$this->set_from_Request( 'datedeadline', 'item_deadline', true );
		}

		// Allow comments for this item (only if set to "post_by_post" for the Blog):
		$this->load_Blog();
		if( $this->Blog->allowcomments == 'post_by_post' )
		{
			if( param( 'post_comment_status', 'string', 'open' ) !== NULL )
			{ // 'open' or 'closed' or ...
				$this->set_from_Request( 'comment_status' );
			}
		}

		if( param( 'renderers_displayed', 'integer', 0 ) )
		{ // use "renderers" value only if it has been displayed (may be empty)
			global $Plugins;
			$renderers = $Plugins->validate_renderer_list( param( 'renderers', 'array', array() ) );
			$this->set( 'renderers', $renderers );
		}
		else
		{
			$renderers = $this->get_renderers_validated();
		}

		if( param( 'content', 'html', NULL ) !== NULL )
		{
			param( 'post_title', 'html', NULL );

			// Do some optional filtering on the content
			// Typically stuff that will help the content to validate
			// Useful for code display.
			// Will probably be used for validation also.
			$Plugins_admin = & get_Plugins_admin();
			$Plugins_admin->filter_contents( $GLOBALS['post_title'] /* by ref */, $GLOBALS['content'] /* by ref */, $renderers );


			// Title handling:
			$this->get_Blog();
			$require_title = $this->Blog->get_setting('require_title');

			if( ( ! $editing || $creating ) && $require_title == 'required' ) // creating is important, when the action is create_edit
			{
				param_check_not_empty( 'post_title', T_('Please provide a title.'), '' );
			}

			// Format raw HTML input to cleaned up and validated HTML:
			param_check_html( 'post_title', T_('Invalid title.'), '' );
			$this->set( 'title', get_param( 'post_title' ) );

			param_check_html( 'content', T_('Invalid content.') );
			$this->set( 'content', get_param( 'content' ) );
		}

		return ! param_errors_detected();
	}


	/**
	 * Template function: display anchor for permalinks to refer to.
	 */
	function anchor()
	{
		global $Settings;

		echo '<a id="'.$this->get_anchor_id().'"></a>';
	}


	/**
	 * @return string
	 */
	function get_anchor_id()
	{
		// In case you have old cafelog permalinks, uncomment the following line:
		// return preg_replace( '/[^a-zA-Z0-9_\.-]/', '_', $this->title );

		return 'item_'.$this->ID;
	}


	/**
	 * Template tag
	 */
	function anchor_id()
	{
		echo $this->get_anchor_id();
	}


	/**
	 * Template function: display assignee of item
	 *
	 * @param string
	 * @param string
	 * @param string Output format, see {@link format_to_output()}
	 */
	function assigned_to( $before = '', $after = '', $format = 'htmlbody' )
	{
		if( $this->get_assigned_User() )
		{
			echo $before;
			$this->assigned_User->preferred_name( $format );
			echo $after;
		}
	}


	/**
	 * Get list of assigned user options
	 *
	 * @uses UserCache::get_blog_member_option_list()
	 * @return string HTML select options list
	 */
	function get_assigned_user_options()
	{
		$UserCache = & get_UserCache();
		return $UserCache->get_blog_member_option_list( $this->get_blog_ID(), $this->assigned_user_ID,
							true,	($this->ID != 0) /* if this Item is already serialized we'll load the default anyway */ );
	}


	/**
	 * Check if user can see comments on this post, which he cannot if they
	 * are disabled for the Item or never allowed for the blog.
	 *
	 * @return boolean
	 */
	function can_see_comments()
	{
		if( $this->comment_status == 'disabled'
				|| $this->is_intro() // Intros: no comments
		    || ( $this->get_Blog() && $this->Blog->allowcomments == 'never' ) )
		{ // Comments are disabled on this post
			return false;
		}

		return true; // OK, user can see comments
	}


	/**
	 * Template function: Check if user can leave comment on this post or display error
	 *
	 * @param string|NULL string to display before any error message; NULL to not display anything, but just return boolean
	 * @param string string to display after any error message
	 * @param string error message for non published posts, '#' for default
	 * @param string error message for closed comments posts, '#' for default
	 * @return boolean true if user can post, false if s/he cannot
	 */
	function can_comment( $before_error = '<p><em>', $after_error = '</em></p>', $non_published_msg = '#', $closed_msg = '#' )
	{
		global $Plugins;

		$display = ( ! is_null($before_error) );

		// Ask Plugins (it can say NULL and would get skipped in Plugin::trigger_event_first_return()):
		// Examples:
		//  - A plugin might want to restrict comments on posts older than 20 days.
		//  - A plugin might want to allow comments always for certain users (admin).
		if( $event_return = $Plugins->trigger_event_first_return( 'ItemCanComment', array( 'Item' => $this ) ) )
		{
			$plugin_return_value = $event_return['plugin_return'];
			if( $plugin_return_value === true )
			{
				return true; // OK, user can comment!
			}

			if( $display && is_string($plugin_return_value) )
			{
				echo $before_error;
				echo $plugin_return_value;
				echo $after_error;
			}

			return false;
		}

		$this->get_Blog();

		if( $this->Blog->allowcomments == 'never')
		{
			return false;
		}

		if( $this->Blog->allowcomments == 'always')
		{
			return true;
		}

		if( $this->comment_status == 'disabled'  )
		{ // Comments are disabled on this post
			return false;
		}

		if( $this->comment_status == 'closed'  )
		{ // Comments are closed on this post

			if( $display)
			{
				if( $closed_msg == '#' )
					$closed_msg = T_( 'Comments are closed for this post.' );

				echo $before_error;
				echo $closed_msg;
				echo $after_error;
			}

			return false;
		}

		if( ($this->status == 'draft') || ($this->status == 'deprecated' ) || ($this->status == 'redirected' ) )
		{ // Post is not published

			if( $display )
			{
				if( $non_published_msg == '#' )
					$non_published_msg = T_( 'This post is not published. You cannot leave comments.' );

				echo $before_error;
				echo $non_published_msg;
				echo $after_error;
			}

			return false;
		}

		return true; // OK, user can comment!
	}


	/**
	 * Template function: Check if user can can rate this post
	 *
	 * @return boolean true if user can post, false if s/he cannot
	 */
	function can_rate()
	{
		$this->get_Blog();

		if( $this->Blog->get_setting('allow_rating') == 'never' )
		{
			return false;
		}

		return true; // OK, user can rate!
	}


	/**
	 * Get the prerendered content. If it has not been generated yet, it will.
	 *
	 * NOTE: This calls {@link Item::dbupdate()}, if renderers get changed (from Plugin hook).
	 *       (not for preview though)
	 *
	 * @param string Format, see {@link format_to_output()}.
	 *        Only "htmlbody", "entityencoded", "xml" and "text" get cached.
	 * @return string
	 */
	function get_prerendered_content( $format )
	{
		global $Plugins;
		global $preview;

		if( $preview )
		{
			$this->update_renderers_from_Plugins();
			$post_renderers = $this->get_renderers_validated();

			// Call RENDERER plugins:
			$r = $this->content;
			$Plugins->render( $r /* by ref */, $post_renderers, $format, array( 'Item' => $this ), 'Render' );

			return $r;
		}


		$r = null;

		$post_renderers = $this->get_renderers_validated();
		$cache_key = $format.'/'.implode('.', $post_renderers); // logic gets used below, for setting cache, too.

		$use_cache = $this->ID && in_array( $format, array('htmlbody', 'entityencoded', 'xml', 'text') );

		// $use_cache = false;

		if( $use_cache )
		{ // the format/item can be cached:
			$ItemPrerenderingCache = & get_ItemPrerenderingCache();

			if( isset($ItemPrerenderingCache[$format][$this->ID][$cache_key]) )
			{ // already in PHP cache.
				$r = $ItemPrerenderingCache[$format][$this->ID][$cache_key];
				// Save memory, typically only accessed once.
				unset($ItemPrerenderingCache[$format][$this->ID][$cache_key]);
			}
			else
			{	// Try loading from DB cache, including all items in MainList/ItemList.
				global $DB;

				if( ! isset($ItemPrerenderingCache[$format]) )
				{ // only do the prefetch loading once.
					$prefetch_IDs = $this->get_prefetch_itemlist_IDs();

					// Load prerendered content for all items in MainList/ItemList.
					// We load the current $format only, since it's most likely that only one gets used.
					$ItemPrerenderingCache[$format] = array();

					$rows = $DB->get_results( "
						SELECT itpr_itm_ID, itpr_format, itpr_renderers, itpr_content_prerendered
							FROM T_items__prerendering
						 WHERE itpr_itm_ID IN (".implode(',', $prefetch_IDs).")
							 AND itpr_format = '".$format."'",
							 OBJECT, 'Preload prerendered item content for MainList/ItemList ('.$format.')' );
					foreach($rows as $row)
					{
						$row_cache_key = $row->itpr_format.'/'.$row->itpr_renderers;

						if( ! isset($ItemPrerenderingCache[$format][$row->itpr_itm_ID]) )
						{ // init list
							$ItemPrerenderingCache[$format][$row->itpr_itm_ID] = array();
						}

						$ItemPrerenderingCache[$format][$row->itpr_itm_ID][$row_cache_key] = $row->itpr_content_prerendered;
					}

					// Set the value for current Item.
					if( isset($ItemPrerenderingCache[$format][$this->ID][$cache_key]) )
					{
						$r = $ItemPrerenderingCache[$format][$this->ID][$cache_key];
						// Save memory, typically only accessed once.
						unset($ItemPrerenderingCache[$format][$this->ID][$cache_key]);
					}
				}
				else
				{ // This item has not been fetched by the initial prefetch query; only get this item.
					// dh> This is quite unlikely to happen, but you never know.
					// This gets not added to ItemPrerenderingCache, since it would only waste
					// memory - an item gets typically only accessed once per page, and even if
					// it would get accessed more often, there is a cache higher in the chain
					// ($this->content_pages).
					$cache = $DB->get_var( "
						SELECT itpr_content_prerendered
							FROM T_items__prerendering
						 WHERE itpr_itm_ID = ".$this->ID."
							 AND itpr_format = '".$format."'
							 AND itpr_renderers = '".implode('.', $post_renderers)."'", 0, 0, 'Check prerendered item content' );
					if( $cache !== NULL ) // may be empty string
					{ // Retrieved from cache:
						// echo ' retrieved from prerendered cache';
						$r = $cache;
					}
				}
			}
		}

		if( ! isset( $r ) )
		{	// Not cached yet:
			global $Debuglog;

			if( $this->update_renderers_from_Plugins() )
			{
				$post_renderers = $this->get_renderers_validated(); // might have changed from call above
				$cache_key = $format.'/'.implode('.', $post_renderers);

				// Save new renderers with item:
				$this->dbupdate();
			}

			// Call RENDERER plugins:
			// pre_dump( $this->content );
			$r = $this->content;
			$Plugins->render( $r /* by ref */, $post_renderers, $format, array( 'Item' => $this ), 'Render' );
			// pre_dump( $r );

			$Debuglog->add( 'Generated pre-rendered content ['.$cache_key.'] for item #'.$this->ID, 'items' );

			if( $use_cache )
			{ // save into DB (using REPLACE INTO because it may have been pre-rendered by another thread since the SELECT above)
				$DB->query( "
					REPLACE INTO T_items__prerendering (itpr_itm_ID, itpr_format, itpr_renderers, itpr_content_prerendered)
					 VALUES ( ".$this->ID.", '".$format."', ".$DB->quote(implode('.', $post_renderers)).', '.$DB->quote($r).' )', 'Cache prerendered item content' );
			}
		}

		return $r;
	}


	/**
	 * Unset any prerendered content for this item (in PHP cache).
	 */
	function delete_prerendered_content()
	{
		global $DB;

		// Delete DB rows.
		$DB->query( 'DELETE FROM T_items__prerendering WHERE itpr_itm_ID = '.$this->ID );

		// Delete cache.
		$ItemPrerenderingCache = & get_ItemPrerenderingCache();
		foreach( array_keys($ItemPrerenderingCache) as $format )
		{
			unset($ItemPrerenderingCache[$format][$this->ID]);
		}

		// Delete derived properties.
		unset($this->content_pages);
	}


	/**
	 * Trigger {@link Plugin::ItemApplyAsRenderer()} event and adjust renderers according
	 * to return value.
	 * @return boolean True if renderers got changed.
	 */
	function update_renderers_from_Plugins()
	{
		global $Plugins;

		$r = false;

		if( !isset($Plugins) )
		{	// This can happen in maintenance modules running with minimal init, during install, or in tests.
			return $r;
		}

		foreach( $Plugins->get_list_by_event('ItemApplyAsRenderer') as $Plugin )
		{
			if( empty($Plugin->code) )
				continue;

			$plugin_r = $Plugin->ItemApplyAsRenderer( $tmp_params = array('Item' => & $this) );

			if( is_bool($plugin_r) )
			{
				if( $plugin_r )
				{
					$r = $this->add_renderer( $Plugin->code ) || $r;
				}
				else
				{
					$r = $this->remove_renderer( $Plugin->code ) || $r;
				}
			}
		}

		return $r;
	}


	/**
	 * Display excerpt of item
	 */
	function excerpt( $params )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'              => '<div class="excerpt">',
				'after'               => '</div>',
				'excerpt_before_more' => ' <span class="excerpt_more">',
				'excerpt_after_more'  => '</span>',
				'excerpt_more_text'   => T_('more').' &raquo;',
				'format'              => 'htmlbody',
				'allow_empty'         => false,						// force generation if excert empty
				'update_db'           => true,						// update the DB if we generated an excerpt
			), $params );

		if( ! $params['allow_empty'] )
		{	// Make sure excerpt is not empty...
			if( $this->update_excerpt() && $params['update_db'] )
			{	// We have updated... let's also update the DB:
				$this->dbupdate( false );		// Do not auto track modification date.
			}
		}

		$r = $this->excerpt;

		if( !empty($r) )
		{
			echo $params['before'];
			echo format_to_output( $this->excerpt, $params['format'] );
			if( !empty( $params['excerpt_more_text'] ) )
			{
				echo $params['excerpt_before_more'];
				echo '<a href="'.$this->get_permanent_url().'">'.$params['excerpt_more_text'].'</a>';
				echo $params['excerpt_after_more'];
			}
			echo $params['after'];
		}
	}

	/**
	 * Make sure, the pages have been obtained (and split up_ from prerendered cache.
	 *
	 * @param string Format, used to retrieve the matching cache; see {@link format_to_output()}
	 */
	function split_pages( $format = 'htmlbody' )
	{
		if( ! isset( $this->content_pages[$format] ) )
		{
			// SPLIT PAGES:
			$this->content_pages[$format] = explode( '<!--nextpage-->', $this->get_prerendered_content($format) );

			$this->pages = count( $this->content_pages[$format] );
			// echo ' Pages:'.$this->pages;
		}
	}


	/**
	 * Get a specific page to display (from the prerendered cache)
	 *
	 * @param integer Page number, NULL/"#" for current
	 * @param string Format, used to retrieve the matching cache; see {@link format_to_output()}
	 */
	function get_content_page( $page = NULL, $format = 'htmlbody' )
	{
		// Get requested content page:
		if( ! isset($page) || $page === '#' )
		{ // We want to display the page requested by the user:
			$page = isset($GLOBALS['page']) ? $GLOBALS['page'] : 1;
		}

		// Make sure, the pages are split up:
		$this->split_pages( $format );

		if( $page < 1 )
		{
			$page = 1;
		}

		if( $page > $this->pages )
		{
			$page = $this->pages;
		}

		return $this->content_pages[$format][$page-1];
	}


	/**
	 * This is like a teaser with no HTML and a cropping.
	 *
	 * Note: Excerpt and Teaser are TWO DIFFERENT THINGS.
	 *
	 * @param int Max length of excerpt
	 */
	function get_content_excerpt( $crop_at = 200 )
	{
		// Get teaser for page 1:
		// fp> Note: I'm not sure about using 'text' here, but there should definitely be no rendering here.
		$output = $this->get_content_teaser( 1, false, 'text' );

		// Get rid of all HTML:
		$output = strip_tags( $output );

		// Ger rid of all new lines:
		$output = trim( str_replace( array( "\r", "\n", "\t" ), array( ' ', ' ', ' ' ), $output ) );

		$output = strmaxlen($output, $crop_at);

		return $output;
	}


	/**
	 * Display content teaser of item (will stop at "<!-- more -->"
	 */
	function content_teaser( $params )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'      => '',
				'after'       => '',
				'disppage'    => '#',
				'stripteaser' => '#',
				'format'      => 'htmlbody',
			), $params );

		$r = $this->get_content_teaser( $params['disppage'], $params['stripteaser'], $params['format'] );

		if( !empty($r) )
		{
			echo $params['before'];
			echo $r;
			echo $params['after'];
		}
	}

	/**
	 * Template function: get content teaser of item (will stop at "<!-- more -->"
	 *
	 * @param mixed page number to display specific page, # for url parameter
	 * @param boolean # if you don't want to repeat teaser after more link was pressed and <-- noteaser --> has been found
	 * @param string filename to use to display more
	 * @return string
	 */
	function get_content_teaser( $disppage = '#', $stripteaser = '#', $format = 'htmlbody' )
	{
		global $Plugins, $preview, $Debuglog;
		global $more;

		$params = array('disppage' => $disppage, 'format' => $format);

		if( $this->has_content_parts($params) )
		{ // This is an extended post (has a more section):
			if( $stripteaser === '#' )
			{
				// If we're in "more" mode and we want to strip the teaser, we'll strip:
				$stripteaser = ( $more && $this->hidden_teaser($params) );
			}

			if( $stripteaser )
			{
				return NULL;
			}
		}

		$output = array_shift( $this->get_content_parts($params) );

		// Trigger Display plugins FOR THE STUFF THAT WOULD NOT BE PRERENDERED:
		$output = $Plugins->render( $output, $this->get_renderers_validated(), $format, array(
				'Item' => $this,
				'preview' => $preview,
				'dispmore' => ($more != 0),
			), 'Display' );

		// Character conversions
		$output = format_to_output( $output, $format );

		return $output;
	}


	/**
	 * Get content parts (split by "<!--more-->").
	 * @param array 'disppage', 'format'
	 * @return array Array of content parts
	 */
	function get_content_parts($params)
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'disppage'    => '#',
				'format'      => 'htmlbody',
			), $params );

		$content_page = $this->get_content_page( $params['disppage'], $params['format'] ); // cannot include format_to_output() because of the magic below.. eg '<!--more-->' will get stripped in "xml"
		// pre_dump($content_page);

		$content_parts = explode( '<!--more-->', $content_page );
		// echo ' Parts:'.count($content_parts);

		return $content_parts;
	}


	/**
	 * DEPRECATED
	 */
	function content()
	{
		// ---------------------- POST CONTENT INCLUDED HERE ----------------------
		skin_include( '_item_content.inc.php', array(
				'image_size'	=>	'fit-400x320',
			) );
		// Note: You can customize the default item feedback by copying the generic
		// /skins/_item_feedback.inc.php file into the current skin folder.
		// -------------------------- END OF POST CONTENT -------------------------
	}


	/**
	 * Display content teaser of item (will stop at "<!-- more -->"
	 */
	function content_extension( $params )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'      => '',
				'after'       => '',
				'disppage'    => '#',
				'format'      => 'htmlbody',
				'force_more'  => false,
			), $params );

		$r = $this->get_content_extension( $params['disppage'], $params['force_more'], $params['format'] );

		if( !empty($r) )
		{
			echo $params['before'];
			echo $r;
			echo $params['after'];
		}
	}


	/**
	 * Template function: get content extension of item (part after "<!-- more -->")
	 *
	 * @param mixed page number to display specific page, # for url parameter
	 * @param boolean
	 * @param string filename to use to display more
	 * @return string
	 */
	function get_content_extension( $disppage = '#', $force_more = false, $format = 'htmlbody' )
	{
		global $Plugins, $more, $preview;

		if( ! $more && ! $force_more )
		{	// NOT in more mode:
			return NULL;
		}

		$params = array('disppage' => $disppage, 'format' => $format);
		if( ! $this->has_content_parts($params) )
		{ // This is NOT an extended post
			return NULL;
		}

		$content_parts = $this->get_content_parts($params);

		// Output everything after <!-- more -->
		array_shift($content_parts);
		$output = implode('', $content_parts);

		// Trigger Display plugins FOR THE STUFF THAT WOULD NOT BE PRERENDERED:
		$output = $Plugins->render( $output, $this->get_renderers_validated(), $format, array(
				'Item' => $this,
				'preview' => $preview,
				'dispmore' => true,
			), 'Display' );

		// Character conversions
		$output = format_to_output( $output, $format );

		return $output;
	}


	/**
	 * Increase view counter
	 *
	 * @todo merge with inc_viewcount
	 */
	function count_view( $params = array() )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'allow_multiple_counts_per_page' => false,
			), $params );


		global $Hit, $preview, $Debuglog;

		if( $preview )
		{
			// echo 'PREVIEW';
			return false;
		}

		/*
		 * Check if we want to increment view count, see {@link Hit::is_new_view()}
		 */
		if( ! $Hit->is_new_view() )
		{	// This is a reload
			// echo 'RELOAD';
			return false;
		}

		if( ! $params['allow_multiple_counts_per_page'] )
		{	// Check that we don't increase multiple viewcounts on the same page
			// This make the assumption that the first post in a list is "viewed" and the other are not (necesarily)
			global $view_counts_on_this_page;
			if( $view_counts_on_this_page >= 1 )
			{	// we already had a count on this page
				// echo 'ALREADY HAD A COUNT';
				return false;
			}
			$view_counts_on_this_page++;
		}

		//echo 'COUNTING VIEW';

		// Increment view counter (only if current User is not the item's author)
		return $this->inc_viewcount(); // won't increment if current_User == Author
	}


	/**
	 * Display custom field
	 */
	function custom( $params )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'        => ' ',
				'after'         => ' ',
				'format'        => 'htmlbody',
				'decimals'      => 2,
				'dec_point'     => '.',
				'thousands_sep' => ',',
			), $params );

		if( empty( $params['field'] ) )
		{
			return;
		}

		$r = $this->{$params['field']};

		if( !empty( $params['max'] ) && substr($params['field'],0,6) == 'double' && $r == 9999999999 )
		{
			echo $params['max'];
		}
		elseif( !empty($r) )
		{
			echo $params['before'];
			if( substr( $params['field'], 0, 6 ) == 'double' )
			{
				echo number_format( $r, $params['decimals'], $params['dec_point'], $params['thousands_sep']  );
			}
			else
			{
				echo format_to_output( $r, $params['format'] );
			}
			echo $params['after'];
		}
	}


	/**
	 * Template tag
	 */
	function more_link( $params = array() )
	{
		echo $this->get_more_link( $params );
	}


	/**
	 * Display more link
	 */
	function get_more_link( $params = array() )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'force_more'  => false,
				'before'      => '<p class="bMore">',
				'after'       => '</p>',
				'link_text'   => '#',		// text to display as the more link
				'anchor_text' => '#',		// text to display as the more anchor (once the more link has been clicked, # defaults to "Follow up:")
				'disppage'    => '#',		// page number to display specific page, # for url parameter
				'format'      => 'htmlbody',
			), $params );

		global $more;

		if( ! $this->has_content_parts($params) )
		{ // This is NOT an extended post:
			return '';
		}

		$content_parts = $this->get_content_parts($params);

		if( ! $more && ! $params['force_more'] )
		{	// We're NOT in "more" mode:
			if( $params['link_text'] == '#' )
			{ // TRANS: this is the default text for the extended post "more" link
				$params['link_text'] = T_('Full story').' &raquo;';
				// Dummy in order to keep previous translation in the loop:
				$dummy = T_('Read more');
			}

			return format_to_output( $params['before']
						.'<a href="'.$this->get_permanent_url().'#more'.$this->ID.'">'
						.$params['link_text'].'</a>'
						.$params['after'], $params['format'] );
		}
		elseif( ! $this->hidden_teaser($params) )
		{	// We are in mode mode and we're not hiding the teaser:
			// (if we're hiding the teaser we display this as a normal page ie: no anchor)
			if( $params['anchor_text'] == '#' )
			{ // TRANS: this is the default text displayed once the more link has been activated
				$params['anchor_text'] = '<p class="bMore">'.T_('Follow up:').'</p>';
			}

			return format_to_output( '<a id="more'.$this->ID.'" name="more'.$this->ID.'"></a>'
							.$params['anchor_text'], $params['format'] );
		}
	}


	/**
	 * Does the post have different content parts (teaser/extension, divided by "<!--more-->")?
	 * This is also true for posts that have images with "aftermore" position.
	 *
	 * @access public
	 * @return boolean
	 */
	function has_content_parts($params)
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'disppage'    => '#',
				'format'      => 'htmlbody',
			), $params );

		$content_page = $this->get_content_page($params['disppage'], $params['format']);

		return strpos($content_page, '<!--more-->') !== false
			|| $this->get_images( array('restrict_to_image_position'=>'aftermore') );
	}


	/**
	 * Should the teaser get hidden when displaying full post ($more).
	 *
	 * @access protected
	 * @return boolean
	 */
	function hidden_teaser($params)
	{
		$content_page = $this->get_content_page($params['disppage'], $params['format']);

		return strpos($content_page, '<!--noteaser-->') !== false;
	}


	/**
	 * Template function: display deadline date (datetime) of Item
	 *
	 * @param string date/time format: leave empty to use locale default date format
	 * @param boolean true if you want GMT
	 */
	function deadline_date( $format = '', $useGM = false )
	{
		if( empty($format) )
			echo mysql2date( locale_datefmt(), $this->datedeadline, $useGM);
		else
			echo mysql2date( $format, $this->datedeadline, $useGM);
	}


	/**
	 * Template function: display deadline time (datetime) of Item
	 *
	 * @param string date/time format: leave empty to use locale default time format
	 * @param boolean true if you want GMT
	 */
	function deadline_time( $format = '', $useGM = false )
	{
		if( empty($format) )
			echo mysql2date( locale_timefmt(), $this->datedeadline, $useGM );
		else
			echo mysql2date( $format, $this->datedeadline, $useGM );
	}


	/**
	 * Get reference to array of Links
	 */
	function & get_Links()
	{
		// Make sure links are loaded:
		$this->load_links();

		return $this->Links;
	}


	/**
	 * Template function: display number of links attached to this Item
	 */
	function linkcount()
	{
		// Make sure links are loaded:
		$this->load_links();

		echo count($this->Links);
	}


	/**
	 * Load links if they were not loaded yet.
	 * @todo dh> gets not used anywhere?! and is the only user of LinkCache::get_by_item_ID().
	 */
	function load_links()
	{
		if( is_null( $this->Links ) )
		{ // Links have not been loaded yet:
			$LinkCache = & get_LinkCache();
			$this->Links = & $LinkCache->get_by_item_ID( $this->ID );
		}
	}


	/**
	 * Get array of tags.
	 *
	 * Load from DB if necessary, prefetching any other tags from MainList/ItemList.
	 *
	 * @return array
	 */
	function & get_tags()
	{
		global $DB;

		if( ! isset( $this->tags ) )
		{
			$ItemTagsCache = & get_ItemTagsCache();
			if( ! isset($ItemTagsCache[$this->ID]) )
			{
				/* Only try to fetch tags for items that are not yet in
				 * the cache. This will always give at least the ID of
				 * this Item.
				 */
				$prefetch_item_IDs = array_diff( $this->get_prefetch_itemlist_IDs(), array_keys( $ItemTagsCache ) );
				// Assume these items don't have any tags:
				foreach( $prefetch_item_IDs as $item_ID )
				{
					$ItemTagsCache[$item_ID] = array();
				}

				// Now fetch the tags:
				foreach( $DB->get_results('
					SELECT itag_itm_ID, tag_name
						FROM T_items__itemtag INNER JOIN T_items__tag ON itag_tag_ID = tag_ID
					 WHERE itag_itm_ID IN ('.$DB->quote($prefetch_item_IDs).')
					 ORDER BY tag_name', OBJECT, 'Get tags for items' ) as $row )
				{
					$ItemTagsCache[$row->itag_itm_ID][] = $row->tag_name;
				}

				//pre_dump( $ItemTagsCache );
			}

			$this->tags = $ItemTagsCache[$this->ID];
		}

		return $this->tags;
	}


	/**
	 * Get the title for the <title> tag
	 *
	 * If it's not specifically entered, use the regular post title instead
	 */
	function get_titletag()
	{
		if( empty($this->titletag) )
		{
			return $this->title;
		}

		return $this->titletag;
	}

	/**
	 * Get the meta description tag
	 *
	 */
	function get_metadesc()
	{
		return $this->metadesc;
	}

	/**
	 * Get the meta keyword tag
	 *
	 */
	function get_metakeywords()
	{
		return $this->metakeywords;
	}


	/**
	 * Split tags by comma or semicolon
	 *
	 * @param string The tags, separated by comma or semicolon
	 */
	function set_tags_from_string( $tags )
	{
		if( $tags === '' )
		{
			$this->tags = array();
			return;
		}
		$this->tags = preg_split( '/\s*[;,]+\s*/', $tags, -1, PREG_SPLIT_NO_EMPTY );

		if( function_exists( 'mb_strtolower' ) )
		{	// fp> TODO: instead of those "when used" ifs, it would make more sense to redefine mb_strtolower beforehand if it doesn"t exist (it would then just be a fallback to the strtolower + a Debuglog->add() )
			// Tblue> Note on charset: param() should have converted the tag string from $io_charset to $evo_charset.
			array_walk( $this->tags, create_function( '& $tag', '$tag = mb_strtolower( $tag, $GLOBALS[\'evo_charset\'] );' ) );
		}
		else
		{
			array_walk( $this->tags, create_function( '& $tag', '$tag = strtolower( $tag );' ) );
		}
		$this->tags = array_unique( $this->tags );
		// pre_dump( $this->tags );
	}


	/**
	 * Template function: Provide link to message form for this Item's author.
	 *
	 * @param string url of the message form
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @return boolean true, if a link was displayed; false if there's no email address for the Item's author.
	 */
	function msgform_link( $params = array() )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'      => ' ',
				'after'       => ' ',
				'text'        => '#',
				'title'       => '#',
				'class'       => '',
				'format'      => 'htmlbody',
				'form_url'    => '#current_blog#',
			), $params );


		if( $params['form_url'] == '#current_blog#' )
		{	// Get
			global $Blog;
			$params['form_url'] = $Blog->get('msgformurl');
		}

		$this->get_creator_User();
		$params['form_url'] = $this->creator_User->get_msgform_url( url_add_param( $params['form_url'], 'post_id='.$this->ID ) );

		if( empty( $params['form_url'] ) )
		{
			return false;
		}

		if( $params['title'] == '#' ) $params['title'] = T_('Send email to post author');
		if( $params['text'] == '#' ) $params['text'] = get_icon( 'email', 'imgtag', array( 'class' => 'middle', 'title' => $params['title'] ) );

		echo $params['before'];
		echo '<a href="'.$params['form_url'].'" title="'.$params['title'].'"';
		if( !empty( $params['class'] ) ) echo ' class="'.$params['class'].'"';
		echo ' rel="nofollow">'.$params['text'].'</a>';
		echo $params['after'];

		return true;
	}


	/**
	 * Template function: Provide link to message form for this Item's assigned User.
	 *
	 * @param string url of the message form
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @return boolean true, if a link was displayed; false if there's no email address for the assigned User.
	 */
	function msgform_link_assigned( $form_url, $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '' )
	{
		if( ! $this->get_assigned_User() || empty($this->assigned_User->email) )
		{ // We have no email for this Author :(
			return false;
		}

		$form_url = url_add_param( $form_url, 'recipient_id='.$this->assigned_User->ID );
		$form_url = url_add_param( $form_url, 'post_id='.$this->ID );

		if( $title == '#' ) $title = T_('Send email to assigned user');
		if( $text == '#' ) $text = get_icon( 'email', 'imgtag', array( 'class' => 'middle', 'title' => $title ) );

		echo $before;
		echo '<a href="'.$form_url.'" title="'.$title.'"';
		if( !empty( $class ) ) echo ' class="'.$class.'"';
		echo ' rel="nofollow">'.$text.'</a>';
		echo $after;

		return true;
	}


	/**
	 *
	 */
	function page_links( $before = '#', $after = '#', $separator = ' ', $single = '', $current_page = '#', $pagelink = '%d', $url = '' )
	{

		// Make sure, the pages are split up:
		$this->split_pages();

		if( $this->pages <= 1 )
		{	// Single page:
			echo $single;
			return;
		}

		if( $before == '#' ) $before = '<p>'.T_('Pages:').' ';
		if( $after == '#' ) $after = '</p>';

		if( $current_page == '#' )
		{
			global $page;
			$current_page = $page;
		}

		if( empty($url) )
		{
			$url = $this->get_permanent_url( '', '', '&amp;' );
		}

		$page_links = array();

		for( $i = 1; $i <= $this->pages; $i++ )
		{
			$text = str_replace('%d', $i, $pagelink);

			if( $i != $current_page )
			{
				if( $i == 1 )
				{	// First page special:
					$page_links[] = '<a href="'.$url.'">'.$text.'</a>';
				}
				else
				{
					$page_links[] = '<a href="'.url_add_param( $url, 'page='.$i ).'">'.$text.'</a>';
				}
			}
			else
			{
				$page_links[] = $text;
			}
		}

		echo $before;
		echo implode( $separator, $page_links );
		echo $after;
	}


	/**
	 * Display the images linked to the current Item
	 *
	 * @param array of params
	 * @param string Output format, see {@link format_to_output()}
	 */
	function images( $params = array(), $format = 'htmlbody' )
	{
		echo $this->get_images( $params, $format );
	}


	/**
	 * Get block of images linked to the current Item
	 *
	 * @param array of params
	 * @param string Output format, see {@link format_to_output()}
	 */
	function get_images( $params = array(), $format = 'htmlbody' )
	{
		$params = array_merge( array(
				'before' =>              '<div>',
				'before_image' =>        '<div class="image_block">',
				'before_image_legend' => '<div class="image_legend">',
				'after_image_legend' =>  '</div>',
				'after_image' =>         '</div>',
				'after' =>               '</div>',
				'image_size' =>          'fit-720x500',
				'image_link_to' =>       'original',  // Can be 'orginal' (image) or 'single' (this post)
				'limit' =>               1000,	// Max # of images displayed
				'restrict_to_image_position' => '',		// 'teaser' or 'aftermore'
			), $params );

		// Get list of attached files
		$FileList = $this->get_attachment_FileList( $params['limit'], $params['restrict_to_image_position'] );

		$r = '';
		/**
		 * @var File
		 */
		$File = NULL;
		while( $File = & $FileList->get_next() )
		{
			if( ! $File->exists() )
			{
				global $Debuglog;
				$Debuglog->add(sprintf('File linked to item #%d does not exist (%s)!', $this->ID, $File->get_full_path()), array('error', 'files'));
				continue;
			}
			if( ! $File->is_image() )
			{	// Skip anything that is not an image
				// fp> TODO: maybe this property should be stored in link_ltype_ID
				continue;
			}

			$link_to = $params['image_link_to']; // Can be 'orginal' (image) or 'single' (this post)
			if( $link_to == 'single' )
			{
				$link_to = $this->get_permanent_url( $link_to );
			}
			// Generate the IMG tag with all the alt, title and desc if available
			$r .= $File->get_tag( $params['before_image'], $params['before_image_legend'], $params['after_image_legend'], $params['after_image'], $params['image_size'], $link_to );
		}

		if( !empty($r) )
		{
			$r = $params['before'].$r.$params['after'];

			// Character conversions
			$r = format_to_output( $r, $format );
		}

		return $r;
	}


	/**
	 * Display the attachments/files linked to the current Item
	 *
	 * @param array Array of params
	 * @param string Output format, see {@link format_to_output()}
	 */
	function files( $params = array(), $format = 'htmlbody' )
	{
		echo $this->get_files( $params, $format );
	}


	/**
	 * Get block of attachments/files linked to the current Item
	 *
	 * @param array Array of params
	 * @param string Output format, see {@link format_to_output()}
	 * @return string HTML
	 */
	function get_files( $params = array(), $format = 'htmlbody' )
	{
		$params = array_merge( array(
				'before' =>              '<div class="item_attachments"><h3>'.T_('Attachments').':</h3><ul class="bFiles">',
				'before_attach' =>         '<li>',
				'before_attach_size' =>    ' <span class="file_size">',
				'after_attach_size' =>     '</span>',
				'after_attach' =>          '</li>',
				'after' =>               '</ul></div>',
			// fp> TODO: we should only have one limit param. Or is there a good reason for having two?
			// sam2kb> It's needed only for flexibility, in the meantime if user attaches 200 files he expects to see all of them in skin, I think.
				'limit_attach' =>        1000, // Max # of files displayed
				'limit' =>               1000,
				'restrict_to_image_position' => '',	// Optionally restrict to files/images linked to specific position: 'teaser'|'aftermore'
			), $params );

		// Get list of attached files
		$FileList = $this->get_attachment_FileList( $params['limit'], $params['restrict_to_image_position'] );

		load_funcs('files/model/_file.funcs.php');

		$r = '';
		$i = 0;
		$r_file = array();
		/**
		 * @var File
		 */
		$File = NULL;
		while( ( $File = & $FileList->get_next() ) && $params['limit_attach'] > $i )
		{
			if( $File->is_image() )
			{	// Skip images because these are displayed inline already
				// fp> TODO: have a setting for each linked file to decide whether it should be displayed inline or as an attachment
				continue;
			}

			// fp> note: it actually makes sense to show directories if the admin chose to link a directory
			// it may be a convenient way to link 1000 files at once... or even a whole source code tree of folders & files... and let apache do the navigation

			if ( $File->is_audio() )
			{
				$r_file[$i]  = '<div class="podplayer">';
				$r_file[$i] .= $this->get_player( $File->get_url() );
				$r_file[$i] .= '</div>';
			}
			else
			{
				$r_file[$i] = $params['before_attach'];
				$r_file[$i] .= action_icon( T_('Download file'), 'download', $File->get_url(), '', 5 ).' ';
				$r_file[$i] .= $File->get_view_link( $File->get_name() );
				$r_file[$i] .= $params['before_attach_size'].'('.bytesreadable( $File->get_size() ).')'.$params['after_attach_size'];
				$r_file[$i] .= $params['after_attach'];
			}

			$i++;
		}

		if( !empty($r_file) )
		{
			$r = $params['before'].implode( "\n", $r_file ).$params['after'];

			// Character conversions
			$r = format_to_output( $r, $format );
		}

		return $r;
	}


	/**
	 * Get list of attached files
	 *
	 * INNER JOIN on files ensures we only get back file links
	 *
	 * @todo dh> Add prefetching for MainList/ItemList (get_prefetch_itemlist_IDs)
	 *           The $limit param and DataObjectList2 makes this quite difficult
	 *           though. Would save (N-1) queries on a blog list page for N items.
	 *
	 * @access protected
	 *
	 * @param integer
	 * @param string Restrict to files/images linked to a specific position. Position can be 'teaser'|'aftermore'
	 * @param string
	 * @return DataObjectList2
	 */
	function get_attachment_FileList( $limit = 1000, $position = NULL, $order = 'link_ID' )
	{
		load_class( '_core/model/dataobjects/_dataobjectlist2.class.php', 'DataObjectList2' );

		$FileCache = & get_FileCache();

		$FileList = new DataObjectList2( $FileCache ); // IN FUNC

		$SQL = new SQL();
		$SQL->SELECT( 'file_ID, file_title, file_root_type, file_root_ID, file_path, file_alt, file_desc' );
		$SQL->FROM( 'T_links INNER JOIN T_files ON link_file_ID = file_ID' );
		$SQL->WHERE( 'link_itm_ID = '.$this->ID );
		if( !empty($position) )
		{
			global $DB;
			$SQL->WHERE_and( 'link_position = '.$DB->quote($position) );
		}
		//$SQL->ORDER_BY( $order );
		$SQL->ORDER_BY( 'link_order' );
		$SQL->LIMIT( $limit );

		$FileList->sql = $SQL->get();

		$FileList->query( false, false, false, 'get_attachment_FileList' );

		return $FileList;
	}


	/**
	 * Template function: Displays link to the feed for comments on this item
	 *
	 * @param string Type of feedback to link to (rss2/atom)
	 * @param string String to display before the link (if comments are to be displayed)
	 * @param string String to display after the link (if comments are to be displayed)
	 * @param string Link title
	 */
	function feedback_feed_link( $skin = '_rss2', $before = '', $after = '', $title='#' )
	{
		if( ! $this->can_see_comments() )
		{	// Comments disabled
			return;
		}

		if( $title == '#' )
		{
			$title = get_icon( 'feed' ).' '.T_('Comment feed for this post');
		}

		$url = $this->get_feedback_feed_url($skin);

		echo $before;
		echo '<a href="'.$url.'">'.format_to_output($title).'</a>';
		echo $after;
	}


	/**
	 * Get URL to display the post comments in an XML feed.
	 *
	 * @param string
	 */
	function get_feedback_feed_url( $skin_folder_name )
	{
		$this->load_Blog();

		return url_add_param( $this->Blog->get_tempskin_url( $skin_folder_name ), 'disp=comments&amp;p='.$this->ID );
	}


	/**
	 * Get URL to display the post comments.
	 *
	 * @return string
	 */
	function get_feedback_url( $popup = false, $glue = '&amp;' )
	{
		$url = $this->get_single_url( 'auto', '', $glue );
		if( $popup )
		{
			$url = url_add_param( $url, 'disp=feedback-popup', $glue );
		}

		return $url;
	}


	/**
	 * Template function: Displays link to feedback page (under some conditions)
	 *
	 * @param array
	 */
	function feedback_link( $params )
	{
		global $ReqURL;

		if( ! $this->can_see_comments() )
		{	// Comments disabled
			return;
		}

		$params = array_merge( array(
									'type' => 'feedbacks',		// Kind of feedbacks to count
									'status' => 'published',	// Status of feedbacks to count
									'link_before' => '',
									'link_after' => '',
									'link_text_zero' => '#',
									'link_text_one' => '#',
									'link_text_more' => '#',
									'link_anchor_zero' => '#',
									'link_anchor_one' => '#',
									'link_anchor_more' => '#',
									'link_title' => '#',
									'use_popup' => false,
									'show_in_single_mode' => false,		// Do we want to show this link even if we are viewing the current post in single view mode
									'url' => '#',
								), $params );

		if( $params['show_in_single_mode'] == false && is_same_url( $this->get_permanent_url('','','&'), $ReqURL ) )
		{	// We are viewing the single page for this pos, which (typically) )contains comments, so we dpn't want to display this link
			return;
		}

		// dh> TODO:	Add plugin hook, where a Pingback plugin could hook and provide "pingbacks"
		switch( $params['type'] )
		{
			case 'feedbacks':
				if( $params['link_title'] == '#' ) $params['link_title'] = T_('Display feedback / Leave a comment');
				if( $params['link_text_zero'] == '#' ) $params['link_text_zero'] = T_('Send feedback').' &raquo;';
				if( $params['link_text_one'] == '#' ) $params['link_text_one'] = T_('1 feedback').' &raquo;';
				if( $params['link_text_more'] == '#' ) $params['link_text_more'] = T_('%d feedbacks').' &raquo;';
				break;

			case 'comments':
				if( $params['link_title'] == '#' ) $params['link_title'] = T_('Display comments / Leave a comment');
				if( $params['link_text_zero'] == '#' )
				{
					if( $this->can_comment( NULL ) ) // NULL, because we do not want to display errors here!
					{
						$params['link_text_zero'] = T_('Leave a comment').' &raquo;';
					}
					else
					{
						$params['link_text_zero'] = '';
					}
				}
				if( $params['link_text_one'] == '#' ) $params['link_text_one'] = T_('1 comment').' &raquo;';
				if( $params['link_text_more'] == '#' ) $params['link_text_more'] = T_('%d comments').' &raquo;';
				break;

			case 'trackbacks':
				$this->get_Blog();
				if( ! $this->can_receive_pings() )
				{ // Trackbacks not allowed on this blog:
					return;
				}
				if( $params['link_title'] == '#' ) $params['link_title'] = T_('Display trackbacks / Get trackback address for this post');
				if( $params['link_text_zero'] == '#' ) $params['link_text_zero'] = T_('Send a trackback').' &raquo;';
				if( $params['link_text_one'] == '#' ) $params['link_text_one'] = T_('1 trackback').' &raquo;';
				if( $params['link_text_more'] == '#' ) $params['link_text_more'] = T_('%d trackbacks').' &raquo;';
				break;

			case 'pingbacks':
				// Obsolete, but left for skin compatibility
				$this->get_Blog();
				if( ! $this->can_receive_pings() )
				{ // Trackbacks not allowed on this blog:
					// We'll consider pingbacks to follow the same restriction
					return;
				}
				if( $params['link_title'] == '#' ) $params['link_title'] = T_('Display pingbacks');
				if( $params['link_text_zero'] == '#' ) $params['link_text_zero'] = T_('No pingback yet').' &raquo;';
				if( $params['link_text_one'] == '#' ) $params['link_text_one'] = T_('1 pingback').' &raquo;';
				if( $params['link_text_more'] == '#' ) $params['link_text_more'] = T_('%d pingbacks').' &raquo;';
				break;

			default:
				debug_die( "Unknown feedback type [{$params['type']}]" );
		}

		$link_text = $this->get_feedback_title( $params['type'], $params['link_text_zero'], $params['link_text_one'], $params['link_text_more'], $params['status'] );

		if( empty($link_text) )
		{	// No link, no display...
			return false;
		}

		if( $params['url'] == '#' )
		{ // We want a link to single post:
			$params['url'] = $this->get_feedback_url();
		}

		// Anchor position
		$number = generic_ctp_number( $this->ID, $params['type'], $params['status'] );

		if( $number == 0 )
			$anchor = $params['link_anchor_zero'];
		elseif( $number == 1 )
			$anchor = $params['link_anchor_one'];
		elseif( $number > 1 )
			$anchor = $params['link_anchor_more'];
		if( $anchor == '#' )
		{
			$anchor = '#'.$params['type'];
		}

		echo $params['link_before'];

		if( !empty( $params['url'] ) )
		{
			echo '<a href="'.$params['url'].$anchor.'" ';	// Position on feedback
			echo 'title="'.$params['link_title'].'"';
			if( $params['use_popup'] )
			{	// Special URL if we can open a popup (i-e if JS is enabled):
				$popup_url = url_add_param( $params['url'], 'disp=feedback-popup' );
				echo ' onclick="return pop_up_window( \''.$popup_url.'\', \'evo_comments\' )"';
			}
			echo '>';
			echo $link_text;
			echo '</a>';
		}
		else
		{
			echo $link_text;
		}

		echo $params['link_after'];
	}


	/**
	 * Return true if there is any feedback of given type.
	 *
	 * @param array
	 * @return boolean
	 */
	function has_feedback( $params )
	{
		$params = array_merge( array(
							'type' => 'feedbacks',
							'status' => 'published'
						), $params );

		// Check is a given type is allowed
		switch( $params['type'] )
		{
			case 'feedbacks':
			case 'comments':
			case 'trackbacks':
			case 'pingbacks':
				break;
			default:
				debug_die( "Unknown feedback type [{$params['type']}]" );
		}

		$number = generic_ctp_number( $this->ID, $params['type'], $params['status'] );

		return $number > 0;
	}


	/**
	 * Return true if trackbacks and pingbacks are allowed
	 *
	 * @return boolen
	 */
	function can_receive_pings()
	{
		return $this->Blog->get( 'allowtrackbacks' );
	}


	/**
	 * Get text depending on number of comments
	 *
	 * @param string Type of feedback to link to (feedbacks (all)/comments/trackbacks/pingbacks)
	 * @param string Link text to display when there are 0 comments
	 * @param string Link text to display when there is 1 comment
	 * @param string Link text to display when there are >1 comments (include %d for # of comments)
	 * @param string Status of feedbacks to count
	 */
	function get_feedback_title( $type = 'feedbacks',	$zero = '#', $one = '#', $more = '#', $status = 'published' )
	{
		if( ! $this->can_see_comments() )
		{	// Comments disabled
			return NULL;
		}

		// dh> TODO:	Add plugin hook, where a Pingback plugin could hook and provide "pingbacks"
		switch( $type )
		{
			case 'feedbacks':
				if( $zero == '#' ) $zero = '';
				if( $one == '#' ) $one = T_('1 feedback');
				if( $more == '#' ) $more = T_('%d feedbacks');
				break;

			case 'comments':
				if( $zero == '#' ) $zero = '';
				if( $one == '#' ) $one = T_('1 comment');
				if( $more == '#' ) $more = T_('%d comments');
				break;

			case 'trackbacks':
				if( $zero == '#' ) $zero = '';
				if( $one == '#' ) $one = T_('1 trackback');
				if( $more == '#' ) $more = T_('%d trackbacks');
				break;

			case 'pingbacks':
				// Obsolete, but left for skin compatibility
				if( $zero == '#' ) $zero = '';
				if( $one == '#' ) $one = T_('1 pingback');
				if( $more == '#' ) $more = T_('%d pingbacks');
				break;

			default:
				debug_die( "Unknown feedback type [$type]" );
		}

		$number = generic_ctp_number( $this->ID, $type, $status );

		if( $number == 0 )
			return $zero;
		elseif( $number == 1 )
			return $one;
		elseif( $number > 1 )
			return str_replace( '%d', $number, $more );
	}


	/**
	 * Template function: Displays feeback moderation info
	 *
	 * @param string Type of feedback to link to (feedbacks (all)/comments/trackbacks/pingbacks)
	 * @param string String to display before the link (if comments are to be displayed)
	 * @param string String to display after the link (if comments are to be displayed)
	 * @param string Link text to display when there are 0 comments
	 * @param string Link text to display when there is 1 comment
	 * @param string Link text to display when there are >1 comments (include %d for # of comments)
	 * @param string Link
	 * @param boolean true to hide if no feedback
	 */
	function feedback_moderation( $type = 'feedbacks', $before = '', $after = '',
			$zero = '', $one = '#', $more = '#', $edit_comments_link = '#', $params = array() )
	{
		/**
		 * @var User
		 */
		global $current_User;

		/* TODO: finish this...
		$params = array_merge( array(
									'type' => 'feedbacks',
									'block_before' => '',
									'blo_after' => '',
									'link_text_zero' => '#',
									'link_text_one' => '#',
									'link_text_more' => '#',
									'link_title' => '#',
									'use_popup' => false,
									'url' => '#',
									'type' => 'feedbacks',
								), $params );
		*/

		if( isset($current_User) && $current_User->check_perm( 'blog_comments', 'any', false, $this->get_blog_ID() ) )
		{	// We jave permission to edit comments:
			if( $edit_comments_link == '#' )
			{	// Use default link:
				global $admin_url;
				$edit_comments_link = '<a href="'.$admin_url.'?ctrl=items&amp;blog='.$this->get_blog_ID().'&amp;p='.$this->ID.'#comments" title="'.T_('Moderate these feedbacks').'">'.get_icon( 'edit' ).' '.T_('Moderate...').'</a>';
			}
		}
		else
		{ // User has no right to edit comments:
			$edit_comments_link = '';
		}

		// Inject Edit/moderate link as relevant:
		$zero = str_replace( '%s', $edit_comments_link, $zero );
		$one = str_replace( '%s', $edit_comments_link, $one );
		$more = str_replace( '%s', $edit_comments_link, $more );

		$r = $this->get_feedback_title( $type, $zero, $one, $more, 'draft' );

		if( !empty( $r ) )
		{
			echo $before.$r.$after;
		}
	}



	/**
	 * Template tag: display footer for the current Item.
	 *
	 * @param array
	 * @return boolean true if something has been displayed
	 */
	function footer( $params )
	{
		// Make sure we are not missing any param:
		$params = array_merge( array(
				'mode'        => '#',				// Will detect 'single' from $disp automatically
				'block_start' => '<div class="item_footer">',
				'block_end'   => '</div>',
				'format'      => 'htmlbody',
			), $params );

		if( $params['mode'] == '#' )
		{
			global $disp;
			$params['mode'] = $disp;
		}

		// pre_dump( $params['mode'] );

		$this->get_Blog();
		switch( $params['mode'] )
		{
			case 'xml':
				$text = $this->Blog->get_setting( 'xml_item_footer_text' );
				break;

			case 'single':
				$text = $this->Blog->get_setting( 'single_item_footer_text' );
				break;

			default:
				// Do NOT display!
				$text = '';
		}

		$text = preg_replace_callback( '?\$([a-z_]+)\$?', array( $this, 'replace_callback' ), $text );

		if( empty($text) )
		{
			return false;
		}

		echo format_to_output( $params['block_start'].$text.$params['block_end'], $params['format'] );

		return true;
	}


	/**
	 * Gets button for deleting the Item if user has proper rights
	 *
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @param boolean true to make this a button instead of a link
	 * @param string page url for the delete action
	 */
	function get_delete_link( $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '', $button = false, $actionurl = '#' )
	{
		global $current_User, $admin_url;

		if( ! is_logged_in() ) return false;

		if( ! $current_User->check_perm( 'blog_del_post', 'any', false, $this->get_blog_ID() ) )
		{ // User has right to delete this post
			return false;
		}

		if( $text == '#' )
		{
			if( ! $button )
			{
				$text = get_icon( 'delete', 'imgtag' ).' '.T_('Delete!');
			}
			else
			{
				$text = T_('Delete!');
			}
		}

		if( $title == '#' ) $title = T_('Delete this post');

		if( $actionurl == '#' )
		{
			$actionurl = $admin_url.'?ctrl=items&amp;action=delete&amp;post_ID=';
		}
		$url = $actionurl.$this->ID.'&amp;'.url_crumb('item');

		$r = $before;
		if( $button )
		{ // Display as button
			$r .= '<input type="button"';
			$r .= ' value="'.$text.'" title="'.$title.'" onclick="if ( confirm(\'';
			$r .= TS_('You are about to delete this post!\\nThis cannot be undone!');
			$r .= '\') ) { document.location.href=\''.$url.'\' }"';
			if( !empty( $class ) ) $r .= ' class="'.$class.'"';
			$r .= '/>';
		}
		else
		{ // Display as link
			$r .= '<a href="'.$url.'" title="'.$title.'" onclick="return confirm(\'';
			$r .= TS_('You are about to delete this post!\\nThis cannot be undone!');
			$r .= '\')"';
			if( !empty( $class ) ) $r .= ' class="'.$class.'"';
			$r .= '>'.$text.'</a>';
		}
		$r .= $after;

		return $r;
	}


	/**
	 * Displays button for deleting the Item if user has proper rights
	 *
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @param boolean true to make this a button instead of a link
	 * @param string page url for the delete action
	 */
	function delete_link( $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '', $button = false, $actionurl = '#' )
	{
		echo $this->get_delete_link( $before, $after, $text, $title, $class, $button, $actionurl );
	}


	/**
	 * Provide link to edit a post if user has edit rights
	 *
	 * @param array Params:
	 *  - 'before': to display before link
	 *  - 'after':    to display after link
	 *  - 'text': link text
	 *  - 'title': link title
	 *  - 'class': CSS class name
	 *  - 'save_context': redirect to current URL?
	 */
	function get_edit_link( $params = array() )
	{
		global $current_User, $admin_url;

		$actionurl = $this->get_edit_url($params);
		if( ! $actionurl )
		{
			return false;
		}

		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'       => ' ',
				'after'        => ' ',
				'text'         => '#',
				'title'        => '#',
				'class'        => '',
				'save_context' => true,
			), $params );


		if( $params['text'] == '#' ) $params['text'] = get_icon( 'edit' ).' '.T_('Edit...');
		if( $params['title'] == '#' ) $params['title'] = T_('Edit this post...');

		$r = $params['before'];
		$r .= '<a href="'.$actionurl;
		$r .= '" title="'.$params['title'].'"';
		if( !empty( $params['class'] ) ) $r .= ' class="'.$params['class'].'"';
		$r .=  '>'.$params['text'].'</a>';
		$r .= $params['after'];

		return $r;
	}


	/**
	 * Get URL to edit a post if user has edit rights.
	 *
	 * @param array Params:
	 *  - 'save_context': redirect to current URL?
	 */
	function get_edit_url($params = array())
	{
		global $admin_url, $current_User;

		if( ! is_logged_in() ) return false;

		if( ! $this->ID )
		{ // preview..
			return false;
		}

		if( ! $current_User->check_perm( 'item_post!CURSTATUS', 'edit', false, $this ) )
		{ // User has no right to edit this post
			return false;
		}

		// default params
		$params += array('save_context' => true);

		$url = $admin_url.'?ctrl=items&amp;action=edit&amp;p='.$this->ID;
		if( $params['save_context'] )
		{
			$url .= '&amp;redirect_to='.rawurlencode( regenerate_url( '', '', '', '&' ).'#'.$this->get_anchor_id() );
		}
		return $url;
	}


	/**
	 * Template tag
	 * @see Item::get_edit_link()
	 */
	function edit_link( $params = array() )
	{
		echo $this->get_edit_link( $params );
	}


	/**
	 * Provide link to publish a post if user has edit rights
	 *
	 * Note: publishing date will be updated
	 *
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @param string glue between url params
	 */
	function get_publish_link( $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '', $glue = '&amp;', $save_context = true )
	{
		global $current_User, $admin_url;

		if( $this->status != 'draft' )
		{
			return false;
		}

		if( ! is_logged_in() ) return false;

		if( ! ($current_User->check_perm( 'item_post!published', 'edit', false, $this ))
			|| ! ($current_User->check_perm( 'edit_timestamp' ) ) )
		{ // User has no right to publish this post now:
			return false;
		}

		if( $text == '#' ) $text = get_icon( 'publish', 'imgtag' ).' '.T_('Publish NOW!');
		if( $title == '#' ) $title = T_('Publish now using current date and time.');

		$r = $before;
		$r .= '<a href="'.$admin_url.'?ctrl=items'.$glue.'action=publish'.$glue.'post_ID='.$this->ID.$glue.url_crumb('item');
		if( $save_context )
		{
			$r .= $glue.'redirect_to='.rawurlencode( regenerate_url( '', '', '', '&' ) );
		}
		$r .= '" title="'.$title.'"';
		if( !empty( $class ) ) $r .= ' class="'.$class.'"';
		$r .= '>'.$text.'</a>';
		$r .= $after;

		return $r;
	}


	function publish_link( $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '', $glue = '&amp;', $save_context = true )
	{
		echo $this->get_publish_link( $before, $after, $text, $title, $class, $glue, $save_context );
	}


	/**
	 * Provide link to deprecate a post if user has edit rights
	 *
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @param string glue between url params
	 */
	function get_deprecate_link( $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '', $glue = '&amp;' )
	{
		global $current_User, $admin_url;

		if( ! is_logged_in() ) return false;

		if( ($this->status == 'deprecated') // Already deprecated!
			|| ! ($current_User->check_perm( 'item_post!deprecated', 'edit', false, $this )) )
		{ // User has no right to deprecated this post:
			return false;
		}

		if( $text == '#' ) $text = get_icon( 'deprecate', 'imgtag' ).' '.T_('Deprecate!');
		if( $title == '#' ) $title = T_('Deprecate this post!');

		$r = $before;
		$r .= '<a href="'.$admin_url.'?ctrl=items'.$glue.'action=deprecate'.$glue.'post_ID='.$this->ID.$glue.url_crumb('item');
		$r .= '" title="'.$title.'"';
		if( !empty( $class ) ) $r .= ' class="'.$class.'"';
		$r .= '>'.$text.'</a>';
		$r .= $after;

		return $r;
	}


	/**
	 * Display link to deprecate a post if user has edit rights
	 *
	 * @param string to display before link
	 * @param string to display after link
	 * @param string link text
	 * @param string link title
	 * @param string class name
	 * @param string glue between url params
	 */
	function deprecate_link( $before = ' ', $after = ' ', $text = '#', $title = '#', $class = '', $glue = '&amp;' )
	{
		echo $this->get_deprecate_link( $before, $after, $text, $title, $class, $glue );
	}


	/**
	 * Template function: display priority of item
	 *
	 * @param string
	 * @param string
	 */
	function priority( $before = '', $after = '' )
	{
		if( isset($this->priority) )
		{
			echo $before;
			echo $this->priority;
			echo $after;
		}
	}


	/**
	 * Template function: display list of priority options
	 */
	function priority_options( $field_value, $allow_none )
	{
		$priority = isset($field_value) ? $field_value : $this->priority;

		$r = '';
		if( $allow_none )
		{
			$r = '<option value="">'./* TRANS: "None" select option */T_('No priority').'</option>';
		}

		foreach( $this->priorities as $i => $name )
		{
			$r .= '<option value="'.$i.'"';
			if( $priority == $i )
			{
				$r .= ' selected="selected"';
			}
			$r .= '>'.$name.'</option>';
		}

		return $r;
	}


	/**
	 * Template function: display checkable list of renderers
	 *
	 * @param array|NULL If given, assume these renderers to be checked.
	 */
	function renderer_checkboxes( $item_renderers = NULL )
	{
		global $Plugins, $inc_path, $admin_url;

		load_funcs('plugins/_plugin.funcs.php');

		$Plugins->restart(); // make sure iterator is at start position

		$atLeastOneRenderer = false;

		if( is_null($item_renderers) )
		{
			$item_renderers = $this->get_renderers();
		}
		// pre_dump( $item_renderers );

		echo '<input type="hidden" name="renderers_displayed" value="1" />';

		foreach( $Plugins->get_list_by_events( array('RenderItemAsHtml', 'RenderItemAsXml', 'RenderItemAsText') ) as $loop_RendererPlugin )
		{ // Go through whole list of renders
			// echo ' ',$loop_RendererPlugin->code;
			if( empty($loop_RendererPlugin->code) )
			{ // No unique code!
				continue;
			}
			if( $loop_RendererPlugin->apply_rendering == 'stealth'
				|| $loop_RendererPlugin->apply_rendering == 'never' )
			{ // This is not an option.
				continue;
			}
			$atLeastOneRenderer = true;

			echo '<div>';

			// echo $loop_RendererPlugin->apply_rendering;

			echo '<input type="checkbox" class="checkbox" name="renderers[]" value="';
			echo $loop_RendererPlugin->code;
			echo '" id="renderer_';
			echo $loop_RendererPlugin->code;
			echo '"';

			switch( $loop_RendererPlugin->apply_rendering )
			{
				case 'always':
					echo ' checked="checked"';
					echo ' disabled="disabled"';
					break;

				case 'opt-out':
					if( in_array( $loop_RendererPlugin->code, $item_renderers ) // Option is activated
						|| in_array( 'default', $item_renderers ) ) // OR we're asking for default renderer set
					{
						echo ' checked="checked"';
					}
					break;

				case 'opt-in':
					if( in_array( $loop_RendererPlugin->code, $item_renderers ) ) // Option is activated
					{
						echo ' checked="checked"';
					}
					break;

				case 'lazy':
					if( in_array( $loop_RendererPlugin->code, $item_renderers ) ) // Option is activated
					{
						echo ' checked="checked"';
					}
					echo ' disabled="disabled"';
					break;
			}

			echo ' title="';
			echo format_to_output($loop_RendererPlugin->short_desc, 'formvalue');
			echo '" />'
			.' <label for="renderer_';
			echo $loop_RendererPlugin->code;
			echo '" title="';
			echo format_to_output($loop_RendererPlugin->short_desc, 'formvalue');
			echo '">';
			echo format_to_output($loop_RendererPlugin->name);
			echo '</label>';

			// fp> TODO: the first thing we want here is a TINY javascript popup with the LONG desc. The links to readme and external help should be inside of the tiny popup.
			// fp> a javascript DHTML onhover help would be evenb better than the JS popup

			// internal README.html link:
			echo ' '.$loop_RendererPlugin->get_help_link('$readme');
			// external help link:
			echo ' '.$loop_RendererPlugin->get_help_link('$help_url');

			echo "</div>\n";
		}

		if( !$atLeastOneRenderer )
		{
			global $admin_url, $mode;
			echo '<a title="'.T_('Configure plugins').'" href="'.$admin_url.'?ctrl=plugins"'.'>'.T_('No renderer plugins are installed.').'</a>';
		}
	}


	/**
	 * Template function: display status of item
	 *
	 * Statuses:
	 * - published
	 * - deprecated
	 * - protected
	 * - private
	 * - draft
	 *
	 * @param string Output format, see {@link format_to_output()}
	 */
	function status( $params = array() )
	{
		global $post_statuses;

		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'      => '',
				'after'       => '',
				'format'      => 'htmlbody',
			), $params );

		echo $params['before'];

		if( $params['format'] == 'raw' )
		{
			status_raw();
		}
		else
		{
			echo format_to_output( $this->get('t_status'), $params['format'] );
		}

		echo $params['after'];
	}


	/**
	 * Output classes for the Item <div>
	 */
	function div_classes( $params = array() )
	{
		global $post_statuses;

		// Make sure we are not missing any param:
		$params = array_merge( array(
				'item_class'        => 'bPost',
				'item_type_class'   => 'bPost_ptyp',
				'item_status_class' => 'bPost',
			), $params );

		echo $params['item_class'].' '.$params['item_type_class'].$this->ptyp_ID.' '.$params['item_status_class'].$this->status;
	}


	/**
	 * Output raw status.
	 */
	function status_raw()
	{
		echo $this->status;
	}


	/**
	 * Template function: display extra status of item
	 *
	 * @param string
	 * @param string
	 * @param string Output format, see {@link format_to_output()}
	 */
	function extra_status( $before = '', $after = '', $format = 'htmlbody' )
	{
		if( $format == 'raw' )
		{
			$this->disp( $this->get('t_extra_status'), 'raw' );
		}
		elseif( $extra_status = $this->get('t_extra_status') )
		{
			echo $before.format_to_output( $extra_status, $format ).$after;
		}
	}


 	/**
	 * Display tags for Item
	 *
	 * @param array of params
	 * @param string Output format, see {@link format_to_output()}
	 */
	function tags( $params = array() )
	{
		$params = array_merge( array(
				'before' =>           '<div>'.T_('Tags').': ',
				'after' =>            '</div>',
				'separator' =>        ', ',
				'links' =>            true,
			), $params );

		$tags = $this->get_tags();

		if( !empty( $tags ) )
		{
			echo $params['before'];

			if( $links = $params['links'] )
			{
				$this->get_Blog();
			}

			$i = 0;
			foreach( $tags as $tag )
			{
				if( $i++ > 0 )
				{
					echo $params['separator'];
				}

				if( $links )
				{	// We want links
					echo $this->Blog->get_tag_link( $tag );
				}
				else
				{
					echo htmlspecialchars($tag);
				}
			}

			echo $params['after'];
		}
	}


	/**
	 * Template function: Displays trackback autodiscovery information
	 *
	 * TODO: build into headers
	 */
	function trackback_rdf()
	{
		$this->get_Blog();
		if( ! $this->can_receive_pings() )
		{ // Trackbacks not allowed on this blog:
			return;
		}

		echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" '."\n";
		echo '  xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
		echo '  xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">'."\n";
		echo '<rdf:Description'."\n";
		echo '  rdf:about="';
		$this->permanent_url( 'single' );
		echo '"'."\n";
		echo '  dc:identifier="';
		$this->permanent_url( 'single' );
		echo '"'."\n";
		$this->title( array(
			'before'    => ' dc:title="',
			'after'     => '"'."\n",
			'link_type' => 'none',
			'format'    => 'xmlattr',
			) );
		echo '  trackback:ping="';
		$this->trackback_url();
		echo '" />'."\n";
		echo '</rdf:RDF>';
	}


	/**
	 * Template function: displays url to use to trackback this item
	 */
	function trackback_url()
	{
		echo $this->get_trackback_url();
	}


	/**
	 * Template function: get url to use to trackback this item
	 * @return string
	 */
	function get_trackback_url()
	{
		global $htsrv_url, $Settings;

		// fp> TODO: get a clean (per blog) setting for this
		//	return $htsrv_url.'trackback.php/'.$this->ID;

		return $htsrv_url.'trackback.php?tb_id='.$this->ID;
	}


	/**
	 * Get HTML code to display a flash audio player for playback of a
	 * given URL.
	 *
	 * @param string The URL of a MP3 audio file.
	 * @return string The HTML code.
	 */
	function get_player( $url )
	{
		global $rsc_url;

		return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="200" height="20" id="dewplayer" align="middle"><param name="wmode" value="transparent"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="'.$rsc_url.'swf/dewplayer.swf?mp3='.$url.'&amp;showtime=1" /><param name="quality" value="high" /><param name="bgcolor" value="" /><embed src="'.$rsc_url.'swf/dewplayer.swf?mp3='.$url.'&amp;showtime=1" quality="high" bgcolor="" width="200" height="20" name="dewplayer" wmode="transparent" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>';
	}


	/**
	 * Template function: Display link to item related url.
	 *
	 * By default the link is displayed as a link.
	 * Optionally some smart stuff may happen.
	 */
	function url_link( $params = array() )
	{

		if( empty( $this->url ) )
		{
			return;
		}

		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'        => ' ',
				'after'         => ' ',
				'text_template' => '$url$',		// If evaluates to empty, nothing will be displayed (except player if podcast)
				'url_template'  => '$url$',
				'target'        => '',
				'format'        => 'htmlbody',
				'podcast'       => '#',						// handle as podcast. # means depending on post type
				'before_podplayer' => '<div class="podplayer">',
				'after_podplayer'  => '</div>',
			), $params );

		if( $params['podcast'] == '#' )
		{	// Check if this post is a podcast
			$params['podcast'] = ( $this->ptyp_ID == 2000 );
		}

		if( $params['podcast'] && $params['format'] == 'htmlbody' )
		{	// We want podcast display:

			echo $params['before_podplayer'];

			echo $this->get_player( $this->url );

			echo $params['after_podplayer'];

		}
		else
		{ // Not displaying podcast player:

			$text = str_replace( '$url$', $this->url, $params['text_template'] );
			if( empty($text) )
			{	// Nothing to display
				return;
			}

			$r = $params['before'];

			$r .= '<a href="'.str_replace( '$url$', $this->url, $params['url_template'] ).'"';

			if( !empty( $params['target'] ) )
			{
				$r .= ' target="'.$params['target'].'"';
			}

			$r .= '>'.$text.'</a>';

			$r .= $params['after'];

			echo format_to_output( $r, $params['format'] );
		}
	}


	/**
	 * Template function: Display the number of words in the post
	 */
	function wordcount()
	{
		echo (int)$this->wordcount; // may have been saved as NULL until 1.9
	}


	/**
	 * Template function: Display the number of times the Item has been viewed
	 *
	 * Note: viewcount is incremented whenever the Item's content is displayed with "MORE"
	 * (i-e full content), see {@link Item::content()}.
	 *
	 * Viewcount is NOT incremented on page reloads and other special cases, see {@link Hit::is_new_view()}
	 *
	 * %d gets replaced in all params by the number of views.
	 *
	 * @param string Link text to display when there are 0 views
	 * @param string Link text to display when there is 1 views
	 * @param string Link text to display when there are >1 views
	 * @return string The phrase about the number of views.
	 */
	function get_views( $zero = '#', $one = '#', $more = '#' )
	{
		if( !$this->views )
		{
			$r = ( $zero == '#' ? T_( 'No views' ) : $zero );
		}
		elseif( $this->views == 1 )
		{
			$r = ( $one == '#' ? T_( '1 view' ) : $one );
		}
		else
		{
			$r = ( $more == '#' ? T_( '%d views' ) : $more );
		}

		return str_replace( '%d', $this->views, $r );
	}


	/**
	 * Template function: Display a phrase about the number of Item views.
	 *
	 * @param string Link text to display when there are 0 views
	 * @param string Link text to display when there is 1 views
	 * @param string Link text to display when there are >1 views (include %d for # of views)
	 * @return integer Number of views.
	 */
	function views( $zero = '#', $one = '#', $more = '#' )
	{
		echo $this->get_views( $zero, $one, $more );

		return $this->views;
	}


	/**
	 * Set param value
	 *
	 * By default, all values will be considered strings
	 *
	 * @todo extra_cat_IDs recording
	 *
	 * @param string parameter name
	 * @param mixed parameter value
	 * @param boolean true to set to NULL if empty value
	 * @return boolean true, if a value has been set; false if it has not changed
	 */
	function set( $parname, $parvalue, $make_null = false )
	{
		switch( $parname )
		{
			case 'pst_ID':
				return $this->set_param( $parname, 'number', $parvalue, true );

			case 'content':
				$r1 = $this->set_param( 'content', 'string', $parvalue, $make_null );
				// Update wordcount as well:
				$r2 = $this->set_param( 'wordcount', 'number', bpost_count_words($this->content), false );
				return ( $r1 || $r2 ); // return true if one changed

			case 'wordcount':
			case 'featured':
				return $this->set_param( $parname, 'number', $parvalue, false );

			case 'datedeadline':
				return $this->set_param( 'datedeadline', 'date', $parvalue, true );

			case 'order':
				return $this->set_param( 'order', 'number', $parvalue, true );

			case 'renderers': // deprecated
				return $this->set_renderers( $parvalue );

			case 'datestart':
			case 'issue_date':
				// Remove seconds from issue date and start date
// fp> TODO: this should only be done if the date is in the future. If it's in the past there are no sideeffects to having seconds.
				return parent::set( $parname, remove_seconds(strtotime($parvalue)) );

			case 'excerpt':
				if( parent::set( 'excerpt', $parvalue, $make_null ) )
				{ // mark excerpt as not being autogenerated anymore
					$this->set('excerpt_autogenerated', 0);
				}
				break;

			default:
				return parent::set( $parname, $parvalue, $make_null );
		}
	}


	/**
	 * Set the renderers of the Item.
	 *
	 * @param array List of renderer codes.
	 * @return boolean true, if it has been set; false if it has not changed
	 */
	function set_renderers( $renderers )
	{
		return $this->set_param( 'renderers', 'string', implode( '.', $renderers ) );
	}


	/**
	 * Set the Author of the Item.
	 *
	 * @param User (Do NOT set to NULL or you may kill the current_User)
	 * @return boolean true, if it has been set; false if it has not changed
	 */
	function set_creator_User( & $creator_User )
	{
		$this->creator_User = & $creator_User;
		$this->Author = & $this->creator_User; // deprecated  fp> TODO: Test and see if this line can be put once and for all in the constructor
		return $this->set( $this->creator_field, $creator_User->ID );
	}


	/**
	 * Create a new Item/Post and insert it into the DB
	 *
	 * This function has to handle all needed DB dependencies!
	 *
	 * @deprecated Use set() + dbinsert() instead
	 */
	function insert(
		$author_user_ID,              // Author
		$post_title,
		$post_content,
		$post_timestamp,              // 'Y-m-d H:i:s'
		$main_cat_ID = 1,             // Main cat ID
		$extra_cat_IDs = array(),     // Table of extra cats
		$post_status = 'published',
		$post_locale = '#',
		$post_urltitle = '',
		$post_url = '',
		$post_comment_status = 'open',
		$post_renderers = array('default'),
		$item_typ_ID = 1,
		$item_st_ID = NULL )
	{
		global $DB, $query, $UserCache;
		global $localtimenow, $default_locale;

		if( $post_locale == '#' ) $post_locale = $default_locale;

		// echo 'INSERTING NEW POST ';

		if( isset( $UserCache ) )	// DIRTY HACK
		{ // If not in install procedure...
			$this->set_creator_User( $UserCache->get_by_ID( $author_user_ID ) );
		}
		else
		{
			$this->set( $this->creator_field, $author_user_ID );
		}
		$this->set( $this->lasteditor_field, $this->{$this->creator_field} );
		$this->set( 'title', $post_title );
		$this->set( 'urltitle', $post_urltitle );
		$this->set( 'content', $post_content );
		$this->set( 'datestart', $post_timestamp );
		$this->set( 'datemodified', date('Y-m-d H:i:s',$localtimenow) );

		$this->set( 'main_cat_ID', $main_cat_ID );
		$this->set( 'extra_cat_IDs', $extra_cat_IDs );
		$this->set( 'status', $post_status );
		$this->set( 'locale', $post_locale );
		$this->set( 'url', $post_url );
		$this->set( 'comment_status', $post_comment_status );
		$this->set_renderers( $post_renderers );
		$this->set( 'ptyp_ID', $item_typ_ID );
		$this->set( 'pst_ID', $item_st_ID );

		// INSERT INTO DB:
		$this->dbinsert();

		return $this->ID;
	}


	/**
	 * Insert object into DB based on previously recorded changes
	 *
	 * @return boolean true on success
	 */
	function dbinsert()
	{
		global $DB, $current_User, $Plugins;

		$DB->begin();

		if( $this->status != 'draft' )
		{	// The post is getting published in some form, set the publish date so it doesn't get auto updated in the future:
			$this->set( 'dateset', 1 );
		}

		if( empty($this->creator_user_ID) )
		{ // No creator assigned yet, use current user:
			$this->set_creator_User( $current_User );
		}

		// Create new slug with validated title
		$new_Slug = new Slug();
		$new_Slug->set( 'title', urltitle_validate( $this->urltitle, $this->title, $this->ID, false, $new_Slug->dbprefix.'title', $new_Slug->dbprefix.'itm_ID', $new_Slug->dbtablename, $this->locale ) );
		$new_Slug->set( 'type', 'item' );
		$this->set( 'urltitle', $new_Slug->get( 'title' ) );

		$this->update_renderers_from_Plugins();

		$this->update_excerpt();

		if( isset($Plugins) )
		{	// Note: Plugins may not be available during maintenance, install or test cases
			// TODO: allow a plugin to cancel update here (by returning false)?
			$Plugins->trigger_event( 'PrependItemInsertTransact', $params = array( 'Item' => & $this ) );
		}

		$dbchanges = $this->dbchanges; // we'll save this for passing it to the plugin hook

		if( $result = parent::dbinsert() )
		{ // We could insert the item object..

			// Let's handle the extracats:
			$this->insert_update_extracats( 'insert' );

			// Let's handle the tags:
			$this->insert_update_tags( 'insert' );

			// Let's handle the slugs:
			// set slug item ID
			$new_Slug->set( 'itm_ID', $this->ID );

			//create tiny slug
			$new_tiny_Slug = new Slug();
			load_funcs( 'slugs/model/_slug.funcs.php' );
			$tinyurl = getnext_tinyurl();
			$new_tiny_Slug->set( 'title', $tinyurl );
			$new_tiny_Slug->set( 'type', 'item' );
			$new_tiny_Slug->set( 'itm_ID', $this->ID );

			if( $result = ( $new_Slug->dbinsert() && $new_tiny_Slug->dbinsert() ) )
			{
				$this->set( 'canonical_slug_ID', $new_Slug->ID );
				$this->set( 'tiny_slug_ID', $new_tiny_Slug->ID );
				if( $result = parent::dbupdate() )
				{
					$DB->commit();

					// save the last tinyurl
					global $Settings;
					$Settings->set( 'tinyurl', $tinyurl );
					$Settings->dbupdate();

					if( isset($Plugins) )
					{	// Note: Plugins may not be available during maintenance, install or test cases
						$Plugins->trigger_event( 'AfterItemInsert', $params = array( 'Item' => & $this, 'dbchanges' => $dbchanges ) );
					}
				}
			}
		}
		if( ! $result )
		{
			$DB->rollback();
		}

		return $result;
	}




	/**
	 * Update the DB based on previously recorded changes
	 *
	 * @param boolean do we want to auto track the mod date?
	 * @return boolean true on success
	 */
	function dbupdate( $auto_track_modification = true, $update_slug = true )
	{
		global $DB, $Plugins;

		$DB->begin();

		if( $this->status != 'draft' )
		{	// The post is getting published in some form, set the publish date so it doesn't get auto updated in the future:
			$this->set( 'dateset', '1' );
		}

		// validate url title / slug
		if( ( empty($this->urltitle) || isset($this->dbchanges['post_urltitle']) ) && $update_slug )
		{ // Url title has changed or is empty
			// echo 'updating url title';

			// Create new slug with validated title
			$new_Slug = new Slug();
			$new_Slug->set( 'title', urltitle_validate( $this->urltitle, $this->title, $this->ID, false, $new_Slug->dbprefix.'title', $new_Slug->dbprefix.'itm_ID', $new_Slug->dbtablename, $this->locale ) );
			$new_Slug->set( 'type', 'item' );
			$new_Slug->set( 'itm_ID', $this->ID );

			// Set item urltitle
			$this->set( 'urltitle', $new_Slug->get( 'title' ) );

			$SlugCache = get_SlugCache();
			if( $SlugCache->get_by_name($new_Slug->get('title'), false, false) )
			{ // slug already exists (for this same item)
				unset($new_Slug);
			}
		}

		$this->update_renderers_from_Plugins();

		$this->update_excerpt();

		// TODO: dh> allow a plugin to cancel update here (by returning false)?
		$Plugins->trigger_event( 'PrependItemUpdateTransact', $params = array( 'Item' => & $this ) );

		$dbchanges = $this->dbchanges; // we'll save this for passing it to the plugin hook

		// pre_dump($this->dbchanges);
		// fp> note that dbchanges isn't actually 100% accurate. At this time it does include variables that actually haven't changed.
		if( isset($this->dbchanges['post_status'])
			|| isset($this->dbchanges['post_title'])
			|| isset($this->dbchanges['post_content']) )
		{	// One of the fields we track in the revision history has changed:
			// Save the "current" (soon to be "old") data as a version before overwriting it in parent::dbupdate:
			// fp> TODO: actually, only the fields that have been changed should be copied to the version, the other should be left as NULL
			$sql = 'INSERT INTO T_items__version( iver_itm_ID, iver_edit_user_ID, iver_edit_datetime, iver_status, iver_title, iver_content )
				SELECT post_ID, post_lastedit_user_ID, post_datemodified, post_status, post_title, post_content
					FROM T_items__item
				 WHERE post_ID = '.$this->ID;
			$DB->query( $sql, 'Save a version of the Item' );
		}

		if( $result = ( parent::dbupdate( $auto_track_modification ) !== false ) )
		{ // We could update the item object:

			// Let's handle the extracats:
			$this->insert_update_extracats( 'update' );

			// Let's handle the tags:
			$this->insert_update_tags( 'update' );

			// Let's handle the slugs:
			if( isset( $new_Slug ) )
			{
				$new_Slug->set( 'itm_ID', $this->ID );
				if( $result = $new_Slug->dbinsert() )
				{
					$this->set( 'canonical_slug_ID', $new_Slug->ID );
					$result = parent::dbupdate();
				}
			}
		}

		if( $result )
		{
			$this->delete_prerendered_content();

			$DB->commit();

			$Plugins->trigger_event( 'AfterItemUpdate', $params = array( 'Item' => & $this, 'dbchanges' => $dbchanges ) );
		}
		else
		{
			$DB->rollback();
		}

		// Load the blog we're in:
		$Blog = & $this->get_Blog();

		// Thick grained invalidation:
		// This collection has been modified, cached content depending on it should be invalidated:
		BlockCache::invalidate_key( 'coll_ID', $Blog->ID );

		// Fine grained invalidation:
		// EXPERIMENTAL: Below are more granular invalidation dates:
		// set_coll_ID // Settings have not changed
		BlockCache::invalidate_key( 'cont_coll_ID', $Blog->ID ); // Content has changed

		return $result;
	}


	/**
	 * Trigger event AfterItemDelete after calling parent method.
	 *
	 * @todo fp> delete related stuff: comments, cats, file links...
	 *
	 * @return boolean true on success
	 */
	function dbdelete()
	{
		global $DB, $Plugins;

		// remember ID, because parent method resets it to 0
		$old_ID = $this->ID;

		$DB->begin();

		if( $r = parent::dbdelete() )
		{
			$this->delete_prerendered_content();

			$DB->commit();

			// re-set the ID for the Plugin event
			$this->ID = $old_ID;

			$Plugins->trigger_event( 'AfterItemDelete', $params = array( 'Item' => & $this ) );

			$this->ID = 0;
		}
		else
		{
			$DB->rollback();
		}

		return $r;
	}


	/**
	 * Quick and dirty "excerpts should not stay empty".
	 *
	 * @todo have a maxlength param for excerpts in blog properties
	 * @todo crop at word boundary, maybe even sentence boundary.
	 *
	 * @return boolean true if excerpt has been changed
	 */
	function update_excerpt( $crop_length = 254, $suffix = '&hellip;' )
	{
		if( empty($this->excerpt)
			|| ( ! empty($this->excerpt_autogenerated) && isset($this->dbchanges['post_content']) ) )
		{
			$stripped_content = str_replace( '<p>', ' <p>', $this->content );
			$stripped_content = trim(strip_tags($stripped_content));
			$excerpt = trim( evo_substr( $stripped_content, 0, $crop_length ) );
			if( !empty($excerpt) )
			{	// We finally have something to act as an excerpt...
				if( evo_strlen( $excerpt ) < evo_strlen( $stripped_content ) )
				{	// If excerpt shorter than original content, add suffix:
					$excerpt .= $suffix;
				}

				$this->set( 'excerpt', $excerpt );
				$this->set( 'excerpt_autogenerated', 1 );
				return true;
			}
		}

		return false;
	}


	/**
	 * @param string 'insert' | 'update'
	 */
	function insert_update_extracats( $mode )
	{
		global $DB;

		$DB->begin();

		if( ! is_null( $this->extra_cat_IDs ) )
		{ // Okay the extra cats are defined:

			if( $mode == 'update' )
			{
				// delete previous extracats:
				$DB->query( 'DELETE FROM T_postcats WHERE postcat_post_ID = '.$this->ID, 'delete previous extracats' );
			}

			// insert new extracats:
			$query = "INSERT INTO T_postcats( postcat_post_ID, postcat_cat_ID ) VALUES ";
			foreach( $this->extra_cat_IDs as $extra_cat_ID )
			{
				//echo "extracat: $extracat_ID <br />";
				$query .= "( $this->ID, $extra_cat_ID ),";
			}
			$query = substr( $query, 0, strlen( $query ) - 1 );
			$DB->query( $query, 'insert new extracats' );
		}

		$DB->commit();
	}


	/**
	 * Save tags to DB
	 *
	 * @param string 'insert' | 'update'
	 */
	function insert_update_tags( $mode )
	{
		global $DB;

		if( isset( $this->tags ) )
		{ // Okay the tags are defined:

			$DB->begin();

			if( $mode == 'update' )
			{	// delete previous tag associations:
				// Note: actual tags never get deleted
				$DB->query( 'DELETE FROM T_items__itemtag
											WHERE itag_itm_ID = '.$this->ID, 'delete previous tags' );
			}

			if( !empty($this->tags) )
			{
				// Find the tags that are already in the DB
				$query = 'SELECT LOWER( tag_name )
										FROM T_items__tag
									 WHERE tag_name IN ('.$DB->quote($this->tags).')';
				$existing_tags = $DB->get_col( $query, 0, 'Find existing tags' );

				$new_tags = array_diff( $this->tags, $existing_tags );
				//pre_dump($new_tags);

				if( !empty( $new_tags ) )
				{	// insert new tags:
					$query = "INSERT INTO T_items__tag( tag_name ) VALUES ";
					foreach( $new_tags as $tag )
					{
						$query .= '( '.$DB->quote($tag).' ),';
					}
					$query = substr( $query, 0, strlen( $query ) - 1 );
					$DB->query( $query, 'insert new tags' );
				}

				// ASSOC:
				$query = 'INSERT INTO T_items__itemtag( itag_itm_ID, itag_tag_ID )
								  SELECT '.$this->ID.', tag_ID
									  FROM T_items__tag
									 WHERE tag_name IN ('.$DB->quote($this->tags).')';
				$DB->query( $query, 'Make tag associations!' );
			}

			$DB->commit();
		}
	}


	/**
	 * Increment the view count of the item directly in DB (if the item's Author is not $current_User).
	 *
	 * This method serves TWO purposes (that would break if we used dbupdate() ) :
	 *  - Increment the viewcount WITHOUT affecting the lastmodified date and user.
	 *  - Increment the viewcount in an ATOMIC manner (even if several hits on the same Item occur simultaneously).
	 *
	 * This also triggers the plugin event 'ItemViewsIncreased' if the view count has been increased.
	 *
	 * @return boolean Did we increase view count?
	 */
	function inc_viewcount()
	{
		global $Plugins, $DB, $current_User, $Debuglog;

		if( isset( $current_User ) && ( $current_User->ID == $this->creator_user_ID ) )
		{
			$Debuglog->add( 'Not incrementing view count, because viewing user is creator of the item.', 'items' );

			return false;
		}

		$DB->query( 'UPDATE T_items__item
		                SET post_views = post_views + 1
		              WHERE '.$this->dbIDname.' = '.$this->ID );

		// Trigger event that the item's view has been increased
		$Plugins->trigger_event( 'ItemViewsIncreased', array( 'Item' => & $this ) );

		return true;
	}


	/**
	 * Get the User who is assigned to the Item.
	 *
	 * @return User|NULL NULL if no user is assigned.
	 */
	function get_assigned_User()
	{
		if( ! isset($this->assigned_User) && isset($this->assigned_user_ID) )
		{
			$UserCache = & get_UserCache();
			$this->assigned_User = & $UserCache->get_by_ID( $this->assigned_user_ID );
		}

		return $this->assigned_User;
	}


	/**
	 * Get the User who created the Item.
	 *
	 * @return User
	 */
	function & get_creator_User()
	{
		if( is_null($this->creator_User) )
		{
			$UserCache = & get_UserCache();
			$this->creator_User = & $UserCache->get_by_ID( $this->creator_user_ID );
			$this->Author = & $this->creator_User;  // deprecated
		}

		return $this->creator_User;
	}


	/**
	 * Get login of the User who created the Item.
	 *
	 * @return string login
	 */
	function get_creator_login()
	{
		$this->get_creator_User();
		if( is_null( $this->creator_user_login ) && !is_null( $this->creator_User ) )
		{
			$this->creator_user_login = $this->creator_User->login;
		}
		return $this->creator_user_login;
	}


	/**
	 * Execute or schedule post(=after) processing tasks
	 *
	 * Includes notifications & pings
	 *
	 * @param boolean give more info messages (we want to avoid that when we save & continue editing)
	 */
	function handle_post_processing( $verbose = true )
	{
		global $Settings, $Messages;

		$notifications_mode = $Settings->get('outbound_notifications_mode');

		if( $notifications_mode == 'off' )
		{	// Exit silently
			return false;
		}

		if( $this->notifications_status == 'finished' )
		{ // pings have been done before
			if( $verbose )
			{
				$Messages->add( T_('Post had already pinged: skipping notifications...'), 'note' );
			}
			return false;
		}

		if( $this->notifications_status != 'noreq' )
		{ // pings have been done before

			// TODO: Check if issue_date has changed and reschedule
			if( $verbose )
			{
				$Messages->add( T_('Post processing already pending...'), 'note' );
			}
			return false;
		}

		if( $this->status != 'published' )
		{
			// TODO: discard any notification that may be pending!
			if( $verbose )
			{
				$Messages->add( T_('Post not publicly published: skipping notifications...'), 'note' );
			}
			return false;
		}

		if( in_array( $this->ptyp_ID, array( 1500,1520,1530,1570,1600,3000 ) ) )
		{
			// TODO: discard any notification that may be pending!
			if( $verbose )
			{
				$Messages->add( T_('This post type doesn\'t need notifications...'), 'note' );
			}
			return false;
		}

		if( $notifications_mode == 'immediate' )
		{	// We want to do the post processing immediately:
			// send outbound pings:
			$this->send_outbound_pings( $verbose );

			// Send email notifications now!
			$this->send_email_notifications( false );

			// Record that processing has been done:
			$this->set( 'notifications_status', 'finished' );
		}
		else
		{	// We want asynchronous post processing:
			$Messages->add( T_('Scheduling asynchronous notifications...'), 'note' );

			// CREATE OBJECT:
			load_class( '/cron/model/_cronjob.class.php', 'Cronjob' );
			$edited_Cronjob = new Cronjob();

			// start datetime. We do not want to ping before the post is effectively published:
			$edited_Cronjob->set( 'start_datetime', $this->issue_date );

			// no repeat.

			// name:
			$edited_Cronjob->set( 'name', sprintf( T_('Send notifications for &laquo;%s&raquo;'), strip_tags($this->title) ) );

			// controller:
			$edited_Cronjob->set( 'controller', 'cron/jobs/_post_notifications.job.php' );

			// params: specify which post this job is supposed to send notifications for:
			$edited_Cronjob->set( 'params', array( 'item_ID' => $this->ID ) );

			// Save cronjob to DB:
			$edited_Cronjob->dbinsert();

			// Memorize the cron job ID which is going to handle this post:
			$this->set( 'notifications_ctsk_ID', $edited_Cronjob->ID );

			// Record that processing has been scheduled:
			$this->set( 'notifications_status', 'todo' );
		}

		// Save the new processing status to DB
		$this->dbupdate();

		return true;
	}


	/**
	 * Send email notifications to subscribed users
	 *
	 * @todo fp>> shall we notify suscribers of blog were this is in extra-cat? blueyed>> IMHO yes.
	 */
	function send_email_notifications( $display = true )
	{
		global $DB, $admin_url, $debug, $Debuglog;

 		$edited_Blog = & $this->get_Blog();

		if( ! $edited_Blog->get_setting( 'allow_subscriptions' ) )
		{	// Subscriptions not enabled!
			return;
		}

		if( $display )
		{
			echo "<div class=\"panelinfo\">\n";
			echo '<h3>', T_('Notifying subscribed users...'), "</h3>\n";
		}

		// Get list of users who want to be notfied:
		// TODO: also use extra cats/blogs??
		$sql = 'SELECT DISTINCT user_email, user_locale
							FROM T_subscriptions INNER JOIN T_users ON sub_user_ID = user_ID
						WHERE sub_coll_ID = '.$this->get_blog_ID().'
							AND sub_items <> 0
							AND LENGTH(TRIM(user_email)) > 0';
		$notify_list = $DB->get_results( $sql );

		// Preprocess list: (this comes form Comment::send_email_notifications() )
		$notify_array = array();
		foreach( $notify_list as $notification )
		{
			$notify_array[$notification->user_email] = $notification->user_locale;
		}

		if( empty($notify_array) )
		{ // No-one to notify:
			if( $display )
			{
				echo '<p>', T_('No-one to notify.'), "</p>\n</div>\n";
			}
			return false;
		}

		/*
		 * We have a list of email addresses to notify:
		 */
		$this->get_creator_User();

		// Send emails:
		$cache_by_locale = array();
		foreach( $notify_array as $notify_email => $notify_locale )
		{
			if( ! isset($cache_by_locale[$notify_locale]) )
			{ // No message for this locale generated yet:
				locale_temp_switch($notify_locale);

				// Calculate length for str_pad to align labels:
				$pad_len = max( evo_strlen(T_('Blog')), evo_strlen(T_('Author')), evo_strlen(T_('Title')), evo_strlen(T_('Url')), evo_strlen(T_('Content')) );

				$cache_by_locale[$notify_locale]['subject'] = sprintf( T_('[%s] New post: "%s"'), $edited_Blog->get('shortname'), $this->get('title') );

				$cache_by_locale[$notify_locale]['message'] =
					str_pad( T_('Blog'), $pad_len ).': '.$edited_Blog->get('shortname')
					.' ( '.str_replace('&amp;', '&', $edited_Blog->gen_blogurl())." )\n"

					.str_pad( T_('Author'), $pad_len ).': '.$this->creator_User->get('preferredname').' ('.$this->creator_User->get('login').")\n"

					.str_pad( T_('Title'), $pad_len ).': '.$this->get('title')."\n"

					// linked URL or "-" if empty:
					.str_pad( T_('Url'), $pad_len ).': '.( empty( $this->url ) ? '-' : str_replace('&amp;', '&', $this->get('url')) )."\n"

					.str_pad( T_('Content'), $pad_len ).': '
						// TODO: We MAY want to force a short URL and avoid it to wrap on a new line in the mail which may prevent people from clicking
						// TODO: might get moved onto a single line, at the end of the content..
						.str_replace('&amp;', '&', $this->get_permanent_url())."\n\n"

					.$this->get('content')."\n"

					// Footer:
					."\n-- \n"
					.T_('Edit/Delete').': '.$admin_url.'?ctrl=items&blog='.$this->get_blog_ID().'&p='.$this->ID."\n\n"

					.T_('Edit your subscriptions/notifications').': '.str_replace('&amp;', '&', url_add_param( $edited_Blog->gen_blogurl(), 'disp=subs' ) )."\n";

				locale_restore_previous();
			}

			if( $display ) echo T_('Notifying:').$notify_email."<br />\n";
			if( $debug >= 2 )
			{
				echo "<p>Sending notification to $notify_email:<pre>$cache_by_locale[$notify_locale]['message']</pre>";
			}

			send_mail( $notify_email, NULL, $cache_by_locale[$notify_locale]['subject'], $cache_by_locale[$notify_locale]['message'],
									$this->creator_User->get('email'), $this->creator_User->get('preferredname') );
		}

		if( $display ) echo '<p>', T_('Done.'), "</p>\n</div>\n";
	}


	/**
	 * Send outbound pings for a post
	 *
	 * @param boolean give more info messages (we want to avoid that when we save & continue editing)
	 */
	function send_outbound_pings( $verbose = true )
	{
		global $Plugins, $baseurl, $Messages, $evonetsrv_host, $test_pings_for_real;

		load_funcs('xmlrpc/model/_xmlrpc.funcs.php');

		$this->load_Blog();
		$ping_plugins = array_unique(explode(',', $this->Blog->get_setting('ping_plugins')));

		if( (preg_match( '#^http://localhost[/:]#', $baseurl)
				|| preg_match( '~^\w+://[^/]+\.local/~', $baseurl ) ) /* domain ending in ".local" */
			&& $evonetsrv_host != 'localhost'	// OK if we are pinging locally anyway ;)
			&& empty($test_pings_for_real) )
		{
			if( $verbose )
			{
				$Messages->add( T_('Skipping pings (Running on localhost).'), 'note' );
			}
		}
		else foreach( $ping_plugins as $plugin_code )
		{
			$Plugin = & $Plugins->get_by_code($plugin_code);

			if( $Plugin )
			{
				$Messages->add( sprintf(T_('Pinging %s...'), $Plugin->ping_service_name), 'note' );
				$params = array( 'Item' => & $this, 'xmlrpcresp' => NULL, 'display' => false );

				$r = $Plugin->ItemSendPing( $params );

				if( !empty($params['xmlrpcresp']) )
				{
					if( is_a($params['xmlrpcresp'], 'xmlrpcresp') )
					{
						// dh> TODO: let xmlrpc_displayresult() handle $Messages (e.g. "error", but should be connected/after the "Pinging %s..." from above)
						ob_start();
						xmlrpc_displayresult( $params['xmlrpcresp'], true );
						$Messages->add( ob_get_contents(), 'note' );
						ob_end_clean();
					}
					else
					{
						$Messages->add( $params['xmlrpcresp'], 'note' );
					}
				}
			}
		}
	}


	/**
	 * Callback user for footer()
	 */
	function replace_callback( $matches )
	{
		switch( $matches[1] )
		{
			case 'item_perm_url':
				return $this->get_permanent_url();

			case 'item_title':
				return $this->title;

			default:
				return $matches[1];
		}
	}

	/**
	 * Get a member param by its name
	 *
	 * @param mixed Name of parameter
	 * @return mixed Value of parameter
	 */
	function get( $parname )
	{
		global $post_statuses;

		switch( $parname )
		{
			case 't_author':
				// Text: author
				$this->get_creator_User();
				return $this->creator_User->get( 'preferredname' );

			case 't_assigned_to':
				// Text: assignee
				if( ! $this->get_assigned_User() )
				{
					return '';
				}
				return $this->assigned_User->get( 'preferredname' );

			case 't_status':
				// Text status:
				return T_( $post_statuses[$this->status] );

			case 't_extra_status':
				$ItemStatusCache = & get_ItemStatusCache();
				if( ! ($Element = & $ItemStatusCache->get_by_ID( $this->pst_ID, true, false ) ) )
				{ // No status:
					return '';
				}
				return $Element->get_name();

			case 't_type':
				// Item type (name):
				if( empty($this->ptyp_ID) )
				{
					return '';
				}

				$ItemTypeCache = & get_ItemTypeCache();
				$type_Element = & $ItemTypeCache->get_by_ID( $this->ptyp_ID );
				return $type_Element->get_name();

			case 't_priority':
				return $this->priorities[ $this->priority ];

			case 'pingsdone':
				// Deprecated by fp 2006-08-21
				return ($this->post_notifications_status == 'finished');
		}

		return parent::get( $parname );
	}


	/**
	 * Assign the item to the first category we find in the requested collection
	 *
	 * @param integer $collection_ID
	 */
	function assign_to_first_cat_for_collection( $collection_ID )
	{
		global $DB;

		// Get the first category ID for the collection ID param
		$cat_ID = $DB->get_var( '
				SELECT cat_ID
					FROM T_categories
				 WHERE cat_blog_ID = '.$collection_ID.'
				 ORDER BY cat_ID ASC
				 LIMIT 1' );

		// Set to the item the first category we got
		$this->set( 'main_cat_ID', $cat_ID );
	}


	/**
	 * Get the list of renderers for this Item.
	 * @return array
	 */
	function get_renderers()
	{
		return explode( '.', $this->renderers );
	}


	/**
	 * Get the list of validated renderers for this Item. This includes stealth plugins etc.
	 * @return array List of validated renderer codes
	 */
	function get_renderers_validated()
	{
		if( ! isset($this->renderers_validated) )
		{
			global $Plugins;
			$this->renderers_validated = $Plugins->validate_renderer_list( $this->get_renderers() );
		}
		return $this->renderers_validated;
	}


	/**
	 * Add a renderer (by code) to the Item.
	 * @param string Renderer code to add for this item
	 * @return boolean True if renderers have changed
	 */
	function add_renderer( $renderer_code )
	{
		$renderers = $this->get_renderers();
		if( in_array( $renderer_code, $renderers ) )
		{
			return false;
		}

		$renderers[] = $renderer_code;
		$this->set_renderers( $renderers );

		$this->renderers_validated = NULL;
		//echo 'Added renderer '.$renderer_code;
	}


	/**
	 * Remove a renderer (by code) from the Item.
	 * @param string Renderer code to remove for this item
	 * @return boolean True if renderers have changed
	 */
	function remove_renderer( $renderer_code )
	{
		$r = false;
		$renderers = $this->get_renderers();
		while( ( $key = array_search( $renderer_code, $renderers ) ) !== false )
		{
			$r = true;
			unset($renderers[$key]);
		}

		if( $r )
		{
			$this->set_renderers( $renderers );
			$this->renderers_validated = NULL;
			//echo 'Removed renderer '.$renderer_code;
		}
		return $r;
	}


	/**
	 * Get a list of item IDs from $MainList and $ItemList, if they are loaded.
	 * This is used for prefetching item related data for the whole list(s).
	 * This will at least return the item's ID itself.
	 * @return array
	 */
	function get_prefetch_itemlist_IDs()
	{
		global $MainList, $ItemList;

		// Add the current ID to the list to prefetch, if it's not in the MainList/ItemList (e.g. featured item).
		$r = array($this->ID);

		if( $MainList )
		{
			$r = array_merge($r, $MainList->get_page_ID_array());
		}
		if( $ItemList )
		{
			$r = array_merge($r, $ItemList->get_page_ID_array());
		}

		return array_unique( $r );
	}


	/**
	 * Get the item tinyurl. If not exists -> create new
	 *
	 * @return string|boolean tinyurl on success, false otherwise
	 */
	function get_tinyurl()
	{
		$tinyurl_ID = $this->tiny_slug_ID;
		if( $tinyurl_ID != NULL )
		{ // the tiny url for this item was already created
			$SlugCache = & get_SlugCache();
			return $SlugCache->get_by_ID($tinyurl_ID)->get( 'title' );
		}
		else
		{ // create new tiny Slug for this item
			$Slug = new Slug();
			load_funcs( 'slugs/model/_slug.funcs.php' );
			$Slug->set( 'title', getnext_tinyurl() );
			$Slug->set( 'itm_ID', $this->ID );
			$Slug->set( 'type', 'item' );
			global $DB;
			$DB->begin();
			if( ! $Slug->dbinsert() )
			{ // Slug dbinsert failed
				$DB->rollback();
				return false;
			}
			$this->set( 'tiny_slug_ID', $Slug->ID );
			if( ! $this->dbupdate() )
			{ // Item dbupdate failed
				$DB->rollback();
				return false;
			}
			$DB->commit();

			// update last tinyurl value on database
			global $Settings;
			$Settings->set( 'tinyurl', $Slug->get( 'title' ) );

			return $Slug->get( 'title' );
		}
	}


	/**
	 * Create and return the item tinyurl link.
	 *
	 * @param array Params:
	 *  - 'before': to display before link
	 *  - 'after': to display after link
	 *  - 'text': link text
	 *  - 'title': link title
	 *  - 'class': class name
	 *  - 'style': link style
	 * @return string the tinyurl link on success, empty string otherwise
	 */
	function get_tinyurl_link( $params = array() )
	{
		if( ( $tinyurl = $this->get_tinyurl() ) == false )
		{
			return '';
		}

		if( ! $this->ID )
		{ // preview..
			return false;
		}

		// Make sure we are not missing any param:
		$params = array_merge( array(
				'before'       => ' ',
				'after'        => ' ',
				'text'         => '#',
				'title'        => '#',
				'class'        => '',
				'style'		   => '',
			), $params );

		if( $params['title'] == '#' )
		{
			$params['title'] = T_( 'This is a tinyurl you can copy/paste into twitter, emails and other places where you need a short link to this post' );
		}
		if( $params['text'] == '#' )
		{
			$params['text'] = $tinyurl;
		}

		$actionurl = url_add_tail( $this->get_Blog()->get( 'url'), '/'.$tinyurl );

		$r = $params['before'];
		$r .= '<a href="'.$actionurl;
		$r .= '" title="'.$params['title'].'"';
		if( !empty( $params['class'] ) ) $r .= ' class="'.$params['class'].'"';
		if( !empty( $params['style'] ) ) $r .= ' style="'.$params['style'].'"';
		$r .=  '>'.$params['text'].'</a>';
		$r .= $params['after'];

		return $r;
	}


	/*
	 * Display the item tinyurl link
	 */
	function tinyurl_link( $params = array() )
	{
		echo $this->get_tinyurl_link( $params );
	}
}


/*
 * $Log$
 * Revision 1.194  2010/05/02 00:02:06  blueyed
 * Fix items slug handling on item update: with an empty slug the currently used slug should get used. Not a new one: fix dbIDname param passed to urltitle_validate (item ID, not slug ID) and do not insert the slug if it already exists.
 *
 * Revision 1.193  2010/04/27 20:58:39  blueyed
 * Slug refactoring.
 *
 * Revision 1.192  2010/04/27 20:08:59  blueyed
 * doc fixes
 *
 * Revision 1.191  2010/04/19 18:38:37  blueyed
 * Fix get_tinyurl, via /blogs/admin.php?ctrl=items&blog=1&filter=restore
 *
 * Revision 1.190  2010/04/12 15:14:25  efy-asimo
 * resolver bug - fix
 *
 * Revision 1.189  2010/04/12 09:41:36  efy-asimo
 * private URL shortener - task
 *
 * Revision 1.188  2010/04/07 08:26:10  efy-asimo
 * Allow multiple slugs per post - update & fix
 *
 * Revision 1.187  2010/03/30 23:23:13  blueyed
 * whitespace
 *
 * Revision 1.186  2010/03/29 20:17:44  blueyed
 * Query title. This needs optimization.
 *
 * Revision 1.185  2010/03/29 12:25:31  efy-asimo
 * allow multiple slugs per post
 *
 * Revision 1.184  2010/03/18 16:20:17  efy-asimo
 * bug about custom fields - fix
 *
 * Revision 1.183  2010/03/18 09:42:09  efy-asimo
 * mass edit posts - task
 *
 * Revision 1.182  2010/03/12 10:20:27  efy-asimo
 * Don't let to create a post with no title if, always needs a title is set
 *
 * Revision 1.181  2010/02/26 22:15:47  fplanque
 * whitespace/doc/minor
 *
 * Revision 1.179  2010/02/21 01:25:47  sam2kb
 * item_varchar fields rolled back to 'string'
 *
 * Revision 1.178  2010/02/10 22:16:17  sam2kb
 * Allow HTML in item_varchar fields
 *
 * Revision 1.177  2010/02/08 17:53:10  efy-yury
 * copyright 2009 -> 2010
 *
 * Revision 1.176  2010/01/31 19:23:24  blueyed
 * Item::has_content_parts: return true also if there are images with 'aftermore' position. This avoids having to add a MORE separator into an image-only post.
 *
 * Revision 1.175  2010/01/30 18:55:30  blueyed
 * Fix "Assigning the return value of new by reference is deprecated" (PHP 5.3)
 *
 * Revision 1.174  2010/01/19 19:38:45  fplanque
 * minor
 *
 * Revision 1.173  2010/01/18 08:06:17  sam2kb
 * ~file renamed to ~attach
 *
 * Revision 1.172  2010/01/03 18:52:57  fplanque
 * crumbs...
 *
 * Revision 1.171  2009/12/22 08:53:32  fplanque
 * global $ReqURL
 *
 * Revision 1.170  2009/12/22 03:30:25  blueyed
 * cleanup
 *
 * Revision 1.169  2009/12/21 01:29:54  fplanque
 * little things
 *
 * Revision 1.168  2009/12/11 22:55:33  fplanque
 * Changing default of "Follow up:" to "..."
 *
 * Revision 1.167  2009/12/08 20:16:12  fplanque
 * Better handling of the publish! button on post forms
 *
 * Revision 1.166  2009/12/03 23:03:15  blueyed
 * Item::get_content_excerpt: use strmaxlen. Fixes broken chars when cut in the middle.
 *
 * Revision 1.165  2009/12/01 03:45:37  fplanque
 * multi dimensional invalidation
 *
 * Revision 1.164  2009/11/28 16:42:05  efy-maxim
 * field name fix
 *
 * Revision 1.163  2009/11/23 11:58:04  efy-maxim
 * owner fix
 *
 * Revision 1.162  2009/11/22 20:29:38  fplanque
 * minor/doc
 *
 * Revision 1.161  2009/11/22 18:52:21  efy-maxim
 * change owner; is login
 *
 * Revision 1.160  2009/11/21 16:39:55  fplanque
 * fix / doc
 *
 * Revision 1.159  2009/11/21 13:31:59  efy-maxim
 * 1. users controller has been refactored to users and user controllers
 * 2. avatar tab
 * 3. jQuery to show/hide custom duration
 *
 * Revision 1.158  2009/11/20 23:56:42  fplanque
 * minor  + doc
 *
 * Revision 1.157  2009/11/20 09:06:09  efy-maxim
 * change owner
 *
 * Revision 1.156  2009/11/19 19:07:46  efy-maxim
 * workflow debug
 *
 * Revision 1.155  2009/11/19 17:25:09  tblue246
 * Make evo_iconv_transliterate() aware of the post locale
 *
 * Revision 1.154  2009/11/15 19:05:45  fplanque
 * no message
 *
 * Revision 1.153  2009/11/11 03:24:51  fplanque
 * misc/cleanup
 *
 * Revision 1.152  2009/11/04 13:20:24  efy-maxim
 * some new functions
 *
 * Revision 1.151  2009/10/27 21:57:45  fplanque
 * minor/doc
 *
 * Revision 1.150  2009/10/11 03:00:11  blueyed
 * Add "position" and "order" properties to attachments.
 * Position can be "teaser" or "aftermore" for now.
 * Order defines the sorting of attachments.
 * Needs testing and refinement. Upgrade might work already, be careful!
 *
 * Revision 1.149  2009/10/10 21:22:46  blueyed
 * Default to page=1. Must have been the case before, too. Notice triggered in admin item list.
 *
 * Revision 1.148  2009/10/10 20:27:09  blueyed
 * Item: fix params to get_content_page
 *
 * Revision 1.147  2009/10/10 20:10:34  blueyed
 * Some refactoring in Item class.
 * Add get_content_parts, has_content_parts and hidden_teaser.
 * Apart from making the code more readable, this allows for more
 * abstraction in the future, e.g. not storing this in the posts itself.
 *
 * This takes us to the "do not display linked files with teaser" feature:
 * Attached images and files are not displayed with teasers anymore, but
 * only with the full post.
 *
 * Revision 1.146  2009/10/07 23:43:25  fplanque
 * doc
 *
 * Revision 1.145  2009/10/04 14:48:23  blueyed
 * Excerpts: as long as it is autogenerated, it follows post_content now. Add doc/todo.
 *
 * Revision 1.144  2009/09/26 12:00:43  tblue246
 * Minor/coding style
 *
 * Revision 1.143  2009/09/25 07:32:52  efy-cantor
 * replace get_cache to get_*cache
 *
 * Revision 1.142  2009/09/21 03:44:32  fplanque
 * doc
 *
 * Revision 1.141  2009/09/20 21:45:08  blueyed
 * Fix preview: actually display changed content. This must have been broken since we have the pre-rendered cache?!
 * fp>I believe it has been broken for less than that since we didn't pass the post_ID until recently (but I may not see the whole picture clearly)
 *
 * Revision 1.140  2009/09/20 19:47:07  blueyed
 * Add post_excerpt_autogenerated field.
 * "text" params get unified newlines now and "excerpt" is a text param.
 * This is required for detecting if it has been changed really.
 * If something is wrong about this, please make sure that an unchanged
 * excerpt won't update the one in DB (when posting the item form).
 *
 * Revision 1.139  2009/09/20 01:35:52  fplanque
 * Factorized User::get_link()
 *
 * Revision 1.138  2009/09/15 19:31:54  fplanque
 * Attempt to load classes & functions as late as possible, only when needed. Also not loading module specific stuff if a module is disabled (module granularity still needs to be improved)
 * PHP 4 compatible. Even better on PHP 5.
 * I may have broken a few things. Sorry. This is pretty hard to do in one swoop without any glitch.
 * Thanks for fixing or reporting if you spot issues.
 *
 * Revision 1.137  2009/09/14 18:37:07  fplanque
 * doc/cleanup/minor
 *
 * Revision 1.136  2009/09/14 13:17:28  efy-arrin
 * Included the ClassName in load_class() call with proper UpperCase
 *
 * Revision 1.135  2009/09/13 21:29:21  blueyed
 * MySQL query cache optimization: remove information about seconds from post_datestart and item_issue_date.
 *
 * Revision 1.134  2009/09/06 21:50:24  tblue246
 * Remove EVO_NEXT_VERSION, which at a point was not replaced and is useless now (since nobody knows by what version it should be replaced now).
 *
 * Revision 1.133  2009/09/05 19:04:23  tblue246
 * Item::get_files(): Do not output HTML code
 *
 * Revision 1.132  2009/09/05 18:34:48  fplanque
 * minor
 *
 * Revision 1.131  2009/09/04 17:21:34  waltercruz
 * minor
 *
 * Revision 1.130  2009/09/04 17:07:15  waltercruz
 * Showing a player when the attachment is a mp3
 *
 * Revision 1.129  2009/08/29 19:10:55  tblue246
 * - Item::set_tags_from_string(): Use $evo_charset instead of $io_charset for mb_strtolower.
 * - XML-RPC library: Set internal encoding to $evo_charset.
 *
 * Revision 1.128  2009/08/25 16:49:48  tblue246
 * Always trigger AfterCommentUpdate/AfterItemUpdate, not only if Comment/Item has changed. Don't kill me, it makes more sense and, as Yabs said: 'yabs nagged the arse off me to reverse this'
 *
 * Revision 1.125  2009/08/25 15:47:26  tblue246
 * Item::get_tags(): Bugfix and optimization: Remember items without tags and do not try to fetch tags for them on the next call. Bug discovered by and fixed with help from yabs.
 *
 * Revision 1.124  2009/08/22 21:19:20  tblue246
 * Item::dbupdate(): Allow ItemLight::dbupdate() to return NULL. Fixes https://bugs.launchpad.net/b2evolution/+bug/415436
 *
 * Revision 1.123  2009/07/19 22:14:22  fplanque
 * Clean resolution of the excerpt mod date bullcrap.
 * It took 4 lines of code...
 *
 * Revision 1.117  2009/07/08 02:38:55  sam2kb
 * Replaced strlen & substr with their mbstring wrappers evo_strlen & evo_substr when needed
 *
 * Revision 1.116  2009/07/07 00:34:42  fplanque
 * Remember whether or not the TinyMCE editor was last used on a per post and per blog basis.
 *
 * Revision 1.115  2009/07/06 22:49:11  fplanque
 * made some small changes on "publish now" handling.
 * Basically only display it for drafts everywhere.
 *
 * Revision 1.114  2009/07/06 21:49:30  blueyed
 * todo
 *
 * Revision 1.113  2009/07/06 13:37:16  tblue246
 * - Backoffice, write screen:
 * 	- Hide the "Publish NOW !" button using JavaScript if the post types "Protected" or "Private" are selected.
 * 	- Do not publish draft posts whose post status has been set to either "Protected" or "Private" and inform the user (note).
 * - Backoffice, post lists:
 * 	- Only display the "Publish NOW!" button for draft posts.
 *
 * Revision 1.112  2009/07/04 18:24:54  tblue246
 * a) Another attempt at fixing Item::set_tags_from_string(). b) Doc @ fp
 *
 * Revision 1.111  2009/07/02 00:39:52  fplanque
 * doc.
 *
 * Revision 1.110  2009/06/28 20:16:06  tblue246
 * doc :(
 *
 * Revision 1.109  2009/06/28 19:58:54  fplanque
 * ROLLBACK: REMINDER: "mb_detect_encoding() is now officially banned from being used anywhere"
 * Tblue> Then remove it from convert_charset(), too (I fear this will
 *        introduce new problems). mb_detect_encoding() was necessary to
 *        fix SQL errors caused by umlauts etc. (see commit message for
 *        rev 1.106). Did you at least try if my code still triggered the
 *        bug on your blog which caused you to ban mb_detect_encoding()?
 * fp> I know there is an issue. I just really don't like mb_detect_encoding() because it's no reliable.
 * Why don't you just pass b2evo's encoding? It has to be in a variable somewhere!! (either $evo_charset or $io_charset -- not sure) but the tags do not come in in a random encoding.
 * I agree mb_detect_encoding() in convert_charset() needs to be killed too.
 * Tblue> I now pass $io_charset, since this is what header_content_type() outputs.
 *
 * Revision 1.108  2009/06/20 17:19:32  leeturner2701
 * meta desc and meta keywords per blog post
 *
 * Revision 1.107  2009/06/13 18:47:01  tblue246
 * minor
 *
 * Revision 1.106  2009/06/01 11:26:06  tblue246
 * Item::set_tags_from_string(): a) Code improvements, b) trying to fix issue with umlauts causing SQL errors: http://forums.b2evolution.net/viewtopic.php?t=18213
 *
 * Revision 1.105  2009/05/27 20:24:44  blueyed
 * Cleanup
 *
 * Revision 1.104  2009/05/26 17:29:46  fplanque
 * A little bit of error management
 * (ps: BeforeEnable unecessary? how so?)
 *
 * Revision 1.103  2009/05/26 16:16:38  fplanque
 * minor
 *
 * Revision 1.102  2009/05/23 20:20:18  fplanque
 * Skins can now have a _skin.class.php file to override default Skin behaviour. Currently only the default name but can/will be extended.
 *
 * Revision 1.101  2009/05/23 14:12:42  fplanque
 * All default skins now support featured posts and intro posts.
 *
 * Revision 1.100  2009/05/22 06:35:13  sam2kb
 * minor, doc
 *
 * Revision 1.99  2009/05/21 14:01:32  tblue246
 * Minor (fixed doc typo)
 *
 * Revision 1.98  2009/05/21 13:05:59  fplanque
 * doc + moved attachments below post in skins
 *
 * Revision 1.97  2009/05/21 10:37:54  tblue246
 * Minor/doc
 *
 * Revision 1.96  2009/05/21 04:53:37  sam2kb
 * Display a list of files attached to post
 * See http://forums.b2evolution.net/viewtopic.php?t=18749
 *
 * Revision 1.95  2009/05/20 13:53:49  fplanque
 * Return to a clean url after posting a comment
 *
 * Revision 1.94  2009/05/19 14:34:31  fplanque
 * Category, tag, archive and serahc page snow only display post excerpts by default. (Requires a 3.x skin; otherwise the skin will display full posts as before). This can be controlled with the ''content_mode'' param in the skin tags.
 *
 * Revision 1.93  2009/05/17 19:51:10  fplanque
 * minor/doc
 *
 * Revision 1.92  2009/04/23 19:51:40  blueyed
 * Add Blog::get_tag_link, use it where appropriate.
 *
 * Revision 1.91  2009/04/22 22:46:34  blueyed
 * Add support for rel=tag in tag URLs. This adds a new tag_links mode 'prefix-only', which requires a prefix (default: tag) and uses no suffix (dash/colon/semicolon). Also adds more JS juice and cleans up/normalized previously existing JS. Not much tested, but implemented as discussed on ML.
 *
 * Revision 1.90  2009/04/21 22:31:35  fplanque
 * rollback of special character killer
 *
 * Revision 1.89  2009/03/22 17:19:37  fplanque
 * better intro posts handling
 *
 * Revision 1.88  2009/03/18 01:09:11  fplanque
 * rel="tag" implementation does not comply with spec
 *
 * Revision 1.87  2009/03/14 03:34:05  fplanque
 * blop
 *
 * Revision 1.86  2009/03/14 03:28:40  fplanque
 * Quick and dirty "excerpts should not stay empty".
 *
 * Revision 1.85  2009/03/08 23:57:43  fplanque
 * 2009
 *
 * Revision 1.84  2009/02/27 20:25:08  blueyed
 * Move Plugins_admin::validate_renderer_list back to Plugins, since it gets used for displaying items and saves (at least) a load_plugins_table call/query
 *
 * Revision 1.83  2009/02/27 20:19:33  blueyed
 * Add prefetching of tags for Items in MainList/ItemList (through ItemTagsCache). This results in (N-1) less queries for N items on a typical blog list page.
 *
 * Revision 1.82  2009/02/27 20:11:18  blueyed
 * Streamline code for prefetching of prerendered content.
 *
 * Revision 1.81  2009/02/27 20:07:47  blueyed
 *  - Add Item::get_prefetch_itemlist_IDs
 *  - Also prefetch prerendered content for ItemList (admin)
 *
 * Revision 1.80  2009/02/27 19:57:05  blueyed
 * doc/TODO
 *
 * Revision 1.79  2009/02/25 22:17:53  blueyed
 * ItemLight: lazily load blog_ID and main_Chapter.
 * There is more, but I do not want to skim the diff again, after
 * "cvs ci" failed due to broken pipe.
 *
 * Revision 1.78  2009/02/25 00:10:16  blueyed
 * doc, add some memory optimisation (ItemPrerenderingCache items are typically only accessed once). Also fix query title for preloading.
 *
 * Revision 1.77  2009/02/24 22:58:20  fplanque
 * Basic version history of post edits
 *
 * Revision 1.76  2009/02/23 21:32:38  blueyed
 * Pre-fetching Mainlists's pre-renderered content: Taking the suggested approach, by adding any out-of-Mainlist items to the query. Might be good for production now.
 *
 * Revision 1.75  2009/02/23 06:44:50  sam2kb
 * Lowercase tags using mbstring funcs if avaliable,
 * see http://forums.b2evolution.net/viewtopic.php?t=14904
 *
 * Revision 1.74  2009/02/23 00:40:09  fplanque
 * doc
 *
 * Revision 1.73  2009/02/22 23:59:53  blueyed
 * ItemPrerenderingCache:
 *  - simple array to prefetch all prerendered MainList items
 *  - There's some flaw still, see the TODO(s)
 *  - add delete_prerendered_content method, also invalidating
 *    content_pages
 *
 * Revision 1.72  2009/02/22 21:49:57  blueyed
 * Add Debuglog-error to File::get_images for non-existing images.
 *
 * Revision 1.71  2009/02/21 23:10:43  fplanque
 * Minor
 *
 * Revision 1.70  2009/02/19 17:51:55  blueyed
 * TODO about plan of PrerenderedContentCache, please comment
 *
 * Revision 1.69  2009/02/12 19:59:41  blueyed
 * - Install: define $localtimenow, so post_datemodified gets set correctly.
 * - Send Cache-Control: no-cache for install/index.php: should not get cached, e.g. when going back to "delete", it should delete!?
 * - indent fixes
 *
 * Revision 1.68  2009/02/03 22:14:10  blueyed
 * Fix indent; TODO about some $params
 *
 * Revision 1.67  2009/02/03 17:45:43  blueyed
 * Item::get_publish_link: check for appropriate status before any more expensive tests.
 *
 * Revision 1.66  2009/02/03 16:55:47  waltercruz
 * Doesn't make sense to publish a redirected post
 *
 * Revision 1.65  2009/01/29 15:48:54  tblue246
 * Use 1 as the default post type ID when creating a new Item object. Fixes http://forums.b2evolution.net/viewtopic.php?t=17780 .
 *
 * Revision 1.64  2009/01/23 22:08:12  tblue246
 * - Filter reserved post types from dropdown box on the post form (expert tab).
 * - Indent/doc fixes
 * - Do not check whether a post title is required when only e. g. switching tabs.
 *
 * Revision 1.63  2009/01/22 00:59:00  blueyed
 * minor
 *
 * Revision 1.62  2009/01/21 23:30:12  fplanque
 * feature/intro posts display adjustments
 *
 * Revision 1.61  2009/01/21 20:33:49  fplanque
 * different display between featured and intro posts
 *
 * Revision 1.60  2008/12/28 23:35:51  fplanque
 * Autogeneration of category/chapter slugs(url names)
 *
 * Revision 1.59  2008/12/22 01:56:54  fplanque
 * minor
 *
 * Revision 1.58  2008/12/22 00:45:26  fplanque
 * antispam
 *
 * Revision 1.57  2008/09/29 08:30:39  fplanque
 * Avatar support
 *
 * Revision 1.56  2008/09/23 07:56:47  fplanque
 * Demo blog now uses shared files folder for demo media + more images in demo posts
 *
 * Revision 1.55  2008/09/15 10:44:16  fplanque
 * skin cleanup
 *
 * Revision 1.54  2008/07/24 01:24:24  afwas
 * $this->title() in function trackback_rdf() used old style parameters. Reported by Austriaco.
 *
 * Revision 1.53  2008/07/11 22:16:52  blueyed
 * Item::get_edit_link(): append anchor to item in redirect_to URL, so that you get to the actual item when pressing save after editing; indent fixes
 *
 * Revision 1.52  2008/06/30 23:47:04  blueyed
 * require_title setting for Blogs, defaulting to 'required'. This makes the title field now a requirement (by default), since it often gets forgotten when posting first (and then the urltitle is ugly already)
 *
 * Revision 1.51  2008/06/01 23:57:20  waltercruz
 * Adding rel=tag (some microformats love)
 *
 * Revision 1.50  2008/05/26 19:22:00  fplanque
 * fixes
 *
 * Revision 1.49  2008/04/26 22:20:45  fplanque
 * Improved compatibility with older skins.
 *
 * Revision 1.48  2008/04/14 17:52:07  fplanque
 * link images to original by default
 *
 * Revision 1.47  2008/04/13 23:38:53  fplanque
 * Basic public user profiles
 *
 * Revision 1.46  2008/04/13 22:07:59  fplanque
 * email fixes
 *
 * Revision 1.45  2008/04/13 15:15:59  fplanque
 * attempt to fix email headers for non latin charsets
 *
 * Revision 1.44  2008/04/12 19:34:21  fplanque
 * bugfix
 *
 * Revision 1.43  2008/04/09 15:28:25  fplanque
 * debug stuff
 *
 * Revision 1.42  2008/04/03 22:03:09  fplanque
 * added "save & edit" and "publish now" buttons to edit screen.
 *
 * Revision 1.41  2008/04/03 13:39:15  fplanque
 * fix
 *
 * Revision 1.40  2008/03/22 19:39:28  fplanque
 * <title> tag support
 *
 * Revision 1.39  2008/03/22 15:20:19  fplanque
 * better issue time control
 *
 * Revision 1.38  2008/02/18 20:22:40  fplanque
 * no message
 *
 * Revision 1.37  2008/02/12 04:59:01  fplanque
 * more custom field handling
 *
 * Revision 1.36  2008/02/11 23:44:44  fplanque
 * more tag params
 *
 * Revision 1.35  2008/02/10 00:58:57  fplanque
 * no message
 *
 * Revision 1.34  2008/02/09 20:14:14  fplanque
 * custom fields management
 *
 * Revision 1.33  2008/02/09 02:56:00  fplanque
 * explicit order by field
 *
 * Revision 1.32  2008/01/23 12:51:20  fplanque
 * posts now have divs with IDs
 *
 * Revision 1.31  2008/01/21 09:35:31  fplanque
 * (c) 2008
 *
 * Revision 1.30  2008/01/20 18:20:26  fplanque
 * Antispam per group setting
 *
 * Revision 1.29  2008/01/19 15:45:28  fplanque
 * refactoring
 *
 * Revision 1.28  2008/01/18 15:53:42  fplanque
 * Ninja refactoring
 *
 * Revision 1.27  2008/01/17 14:38:30  fplanque
 * Item Footer template tag
 *
 * Revision 1.26  2008/01/14 07:22:07  fplanque
 * Refactoring
 *
 * Revision 1.25  2008/01/08 03:31:51  fplanque
 * podcast support
 *
 * Revision 1.24  2008/01/07 02:53:27  fplanque
 * cleaner tag urls
 *
 * Revision 1.23  2007/12/08 14:43:36  yabs
 * bugfix ( http://forums.b2evolution.net/viewtopic.php?t=13482 )
 *
 * Revision 1.22  2007/11/30 21:37:29  fplanque
 * removed empty tags
 *
 * Revision 1.21  2007/11/29 22:47:12  fplanque
 * tags everywhere + debug
 *
 * Revision 1.20  2007/11/29 20:53:45  fplanque
 * Fixed missing url link in basically all skins ...
 *
 * Revision 1.19  2007/11/25 14:28:17  fplanque
 * additional SEO settings
 *
 * Revision 1.18  2007/11/24 21:24:14  fplanque
 * display tags in backoffice
 *
 * Revision 1.17  2007/11/22 17:53:39  fplanque
 * filemanager display cleanup, especially in IE (not perfect)
 *
 * Revision 1.16  2007/11/15 23:45:41  blueyed
 * (Re-)Added phpdoc for get_edit_link
 *
 * Revision 1.15  2007/11/04 01:10:57  fplanque
 * skin cleanup continued
 *
 * Revision 1.14  2007/11/03 23:54:38  fplanque
 * skin cleanup continued
 *
 * Revision 1.13  2007/11/03 21:04:26  fplanque
 * skin cleanup
 *
 * Revision 1.12  2007/11/02 01:54:46  fplanque
 * comment ratings
 *
 * Revision 1.11  2007/09/13 19:16:14  fplanque
 * feedback_link() cleanup
 *
 * Revision 1.10  2007/09/13 02:37:22  fplanque
 * special cases
 *
 * Revision 1.9  2007/09/11 23:10:39  fplanque
 * translation updates
 *
 * Revision 1.8  2007/09/10 14:53:04  fplanque
 * cron fix
 *
 * Revision 1.7  2007/09/09 12:51:58  fplanque
 * cleanup
 *
 * Revision 1.6  2007/09/09 09:15:59  yabs
 * validation
 *
 * Revision 1.5  2007/09/08 19:31:28  fplanque
 * cleanup of XML feeds for comments on individual posts.
 *
 * Revision 1.4  2007/09/04 22:16:33  fplanque
 * in context editing of posts
 *
 * Revision 1.3  2007/08/28 02:43:40  waltercruz
 * Template function to get the rss link to the feeds of the comments on each post
 *
 * Revision 1.2  2007/07/03 23:21:32  blueyed
 * Fixed includes/requires in/for tests
 *
 * Revision 1.1  2007/06/25 11:00:24  fplanque
 * MODULES (refactored MVC)
 *
 * Revision 1.184  2007/06/24 22:26:34  fplanque
 * improved feedback template
 *
 * Revision 1.183  2007/06/21 00:44:37  fplanque
 * linkblog now a widget
 *
 * Revision 1.182  2007/06/20 21:42:13  fplanque
 * implemented working widget/plugin params
 *
 * Revision 1.180  2007/06/18 20:59:55  fplanque
 * do not display link to comments if comments are disabled
 *
 * Revision 1.179  2007/06/13 23:29:02  fplanque
 * minor
 *
 * Revision 1.178  2007/06/11 01:55:57  fplanque
 * level based user permissions
 *
 * Revision 1.177  2007/05/28 15:18:30  fplanque
 * cleanup
 *
 * Revision 1.176  2007/05/28 01:33:22  fplanque
 * permissions/fixes
 *
 * Revision 1.175  2007/05/27 00:35:26  fplanque
 * tag display + tag filtering
 *
 * Revision 1.174  2007/05/20 01:01:35  fplanque
 * make trackback silent when it should be
 *
 * Revision 1.173  2007/05/14 02:47:23  fplanque
 * (not so) basic Tags framework
 *
 * Revision 1.172  2007/05/13 22:02:07  fplanque
 * removed bloated $object_def
 *
 * Revision 1.171  2007/04/26 00:11:11  fplanque
 * (c) 2007
 *
 * Revision 1.170  2007/04/15 13:34:36  blueyed
 * Fixed default $url generation in page_links()
 *
 * Revision 1.169  2007/04/05 22:57:33  fplanque
 * Added hook: UnfilterItemContents
 *
 * Revision 1.168  2007/03/31 22:46:46  fplanque
 * FilterItemContent event
 *
 * Revision 1.167  2007/03/26 12:59:18  fplanque
 * basic pages support
 *
 * Revision 1.166  2007/03/25 10:19:30  fplanque
 * cleanup
 *
 * Revision 1.165  2007/03/24 20:41:16  fplanque
 * Refactored a lot of the link junk.
 * Made options blog specific.
 * Some junk still needs to be cleaned out. Will do asap.
 *
 * Revision 1.164  2007/03/19 23:59:32  fplanque
 * minor
 *
 * Revision 1.163  2007/03/18 03:43:19  fplanque
 * EXPERIMENTAL
 * Splitting Item/ItemLight and ItemList/ItemListLight
 * Goal: Handle Items with less footprint than with their full content
 * (will be even worse with multiple languages/revisions per Item)
 *
 * Revision 1.162  2007/03/11 23:57:07  fplanque
 * item editing: allow setting to 'redirected' status
 *
 * Revision 1.161  2007/03/06 12:18:08  fplanque
 * got rid of dirty Item::content()
 * Advantage: the more link is now independant. it can be put werever people want it
 *
 * Revision 1.160  2007/03/05 04:52:42  fplanque
 * better precision for viewcounts
 *
 * Revision 1.159  2007/03/05 04:49:17  fplanque
 * better precision for viewcounts
 *
 * Revision 1.158  2007/03/05 02:13:26  fplanque
 * improved dashboard
 *
 * Revision 1.157  2007/03/05 01:47:50  fplanque
 * splitting up Item::content() - proof of concept.
 * needs to be optimized.
 *
 * Revision 1.156  2007/03/03 01:14:11  fplanque
 * new methods for navigating through posts in single item display mode
 *
 * Revision 1.155  2007/03/02 04:40:38  fplanque
 * fixed/commented a lot of stuff with the feeds
 *
 * Revision 1.154  2007/03/02 03:09:12  fplanque
 * rss length doesn't make sense since it doesn't apply to html format anyway.
 * clean solutionwould be to handle an "excerpt" field separately
 *
 * Revision 1.153  2007/02/23 19:16:07  blueyed
 * MFB: Fixed handling of Item::content for pre-rendering (it gets passed by reference!)
 *
 * Revision 1.152  2007/02/18 22:51:26  waltercruz
 * Fixing a little confusion with quotes and string concatenation
 *
 * Revision 1.151  2007/02/08 03:45:40  waltercruz
 * Changing double quotes to single quotes
 *
 * Revision 1.150  2007/02/05 13:32:49  waltercruz
 * Changing double quotes to single quotes
 *
 * Revision 1.149  2007/01/26 04:52:53  fplanque
 * clean comment popups (skins 2.0)
 *
 * Revision 1.148  2007/01/26 02:12:06  fplanque
 * cleaner popup windows
 *
 * Revision 1.147  2007/01/23 03:46:24  fplanque
 * cleaned up presentation
 *
 * Revision 1.146  2007/01/19 10:45:42  fplanque
 * images everywhere :D
 * At this point the photoblogging code can be considered operational.
 *
 * Revision 1.145  2007/01/11 19:29:50  blueyed
 * Fixed E_NOTICE when using the "excerpt" feature
 *
 * Revision 1.144  2006/12/26 00:08:29  fplanque
 * wording
 *
 * Revision 1.143  2006/12/21 22:35:28  fplanque
 * No regression. But a change in usage. The more link must be configured in the skin.
 * Renderers cannot side-effect on the more tag any more and that actually makes the whole thing safer.
 *
 * Revision 1.142  2006/12/20 13:57:34  blueyed
 * TODO about regression because of pre-rendering and the <!--more--> tag
 *
 * Revision 1.141  2006/12/18 13:31:12  fplanque
 * fixed broken more tag
 *
 * Revision 1.140  2006/12/16 01:30:46  fplanque
 * Setting to allow/disable email subscriptions on a per blog basis
 *
 * Revision 1.139  2006/12/15 22:59:05  fplanque
 * doc
 *
 * Revision 1.138  2006/12/14 22:26:31  blueyed
 * Fixed E_NOTICE and displaying of pings into $Messages (though "hackish")
 *
 * Revision 1.137  2006/12/12 02:53:56  fplanque
 * Activated new item/comments controllers + new editing navigation
 * Some things are unfinished yet. Other things may need more testing.
 *
 * Revision 1.136  2006/12/07 23:13:11  fplanque
 * @var needs to have only one argument: the variable type
 * Otherwise, I can't code!
 *
 * Revision 1.135  2006/12/06 23:55:53  fplanque
 * hidden the dead body of the sidebar plugin + doc
 *
 * Revision 1.134  2006/12/05 14:28:29  blueyed
 * Fixed wordcount==0 handling; has been saved as NULL
 *
 * Revision 1.133  2006/12/05 06:38:40  blueyed
 * doc
 *
 * Revision 1.132  2006/12/05 00:39:56  fplanque
 * fixed some more permalinks/archive links
 *
 * Revision 1.131  2006/12/05 00:34:39  blueyed
 * Implemented custom "None" option text in DataObjectCache; Added for $ItemStatusCache, $GroupCache, UserCache and BlogCache; Added custom text for Item::priority_options()
 *
 * Revision 1.130  2006/12/04 20:52:40  blueyed
 * typo
 *
 * Revision 1.129  2006/12/04 19:57:58  fplanque
 * How often must I fix the weekly archives until they stop bugging me?
 *
 * Revision 1.128  2006/12/04 19:41:11  fplanque
 * Each blog can now have its own "archive mode" settings
 *
 * Revision 1.127  2006/12/03 18:15:32  fplanque
 * doc
 *
 * Revision 1.126  2006/12/01 20:04:31  blueyed
 * Renamed Plugins_admin::validate_list() to validate_renderer_list()
 *
 * Revision 1.125  2006/12/01 19:46:42  blueyed
 * Moved Plugins::validate_list() to Plugins_admin class; added stub in Plugins, because at least the starrating_plugin uses it
 *
 * Revision 1.124  2006/11/28 20:04:11  blueyed
 * No edit link, if ID==0 to avoid confusion in preview, see http://forums.b2evolution.net/viewtopic.php?p=47422#47422
 *
 * Revision 1.123  2006/11/24 18:27:24  blueyed
 * Fixed link to b2evo CVS browsing interface in file docblocks
 *
 * Revision 1.122  2006/11/22 20:48:58  blueyed
 * Added Item::get_Chapters() and Item::get_main_Chapter(); refactorized
 *
 * Revision 1.121  2006/11/22 20:12:18  blueyed
 * Use $format param in Item::categories()
 *
 * Revision 1.120  2006/11/19 22:17:42  fplanque
 * minor / doc
 *
 * Revision 1.119  2006/11/19 16:07:31  blueyed
 * Fixed saving empty renderers list. This should also fix the saving of "default" instead of the explicit renderer list
 *
 * Revision 1.118  2006/11/17 18:36:23  blueyed
 * dbchanges param for AfterItemUpdate, AfterItemInsert, AfterCommentUpdate and AfterCommentInsert
 *
 * Revision 1.117  2006/11/13 20:49:52  fplanque
 * doc/cleanup :/
 *
 * Revision 1.116  2006/11/10 20:14:11  blueyed
 * doc, fix
 *
 * Revision 1.115  2006/11/02 16:12:49  blueyed
 * MFB
 *
 * Revision 1.114  2006/11/02 16:01:00  blueyed
 * doc
 *
 * Revision 1.113  2006/10/29 18:33:23  blueyed
 * doc fix
 *
 * Revision 1.112  2006/10/23 22:19:02  blueyed
 * Fixed/unified encoding of redirect_to param. Use just rawurlencode() and no funky &amp; replacements
 *
 * Revision 1.111  2006/10/18 00:03:51  blueyed
 * Some forgotten url_rel_to_same_host() additions
 */
?>
