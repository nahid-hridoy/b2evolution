<?php
/**
 * This file implements the post browsing in tracker mode
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2005 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * @package admin
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var Blog
 */
global $Blog;
/**
 * @var ItemList2
 */
global $ItemList;

global $add_item_url, $edit_item_url, $delete_item_url;


// Display title depending on selection params:
echo $ItemList->get_filter_title( '<h2>', '</h2>', '<br />', NULL, 'htmlbody' );


/*
	**
	 * Callback to add filters on top of the result set
	 *
	function filter_on_post_title( & $Form )
	{
		global $pagenow, $post_filter;

		$Form->hidden( 'filter_on_post_title', 1 );
		$Form->text( 'post_filter', $post_filter, 20, T_('Task title'), '', 60 );
	}
	$ItemList->filters_callback = 'filter_on_post_title';
*/


$ItemList->title = T_('Task list');


$ItemList->cols[] = array(
						'th' => /* TRANS: abbrev for Priority */ T_('P'),
						'order' => 'post_priority',
						'th_start' => '<th class="shrinkwrap">',
						'td_start' => '<td class="center tskst_$post_pst_ID$">',
						'td' => '$post_priority$',
					);


/**
 * Task title
 */
function task_title_link( $Item )
{
	global $current_User;

	$col = '';

	$Item->load_Blog();
  if( $Item->Blog->allowcomments != 'never' )
	{	// The current blog can have comments:
		$nb_comments = generic_ctp_number($Item->ID, 'feedback');
		$col .= '<a href="?ctrl=browse&amp;tab=posts&amp;blog='.$Item->blog_ID.'&amp;p='.$Item->ID.'&amp;c=1&amp;tb=1&amp;pb=1"
						title="'.sprintf( T_('%d feedbacks'), $nb_comments ).'" class="">';
		if( $nb_comments )
		{
			$col .= get_icon( 'comments' );
		}
		else
		{
			$col .= get_icon( 'nocomment' );
		}
		$col .= '</a> ';
	}

	$col .= '<a href="?ctrl=browse&amp;blog='.$Item->blog_ID.'&amp;p='.$Item->ID.'&amp;c=1&amp;tb=1&amp;pb=1" class="" title="'.
								T_('Edit this task...').'">'.$Item->dget('title').'</a></strong>';

	return $col;
}
$ItemList->cols[] = array(
						'th' => T_('Task'),
						'order' => 'post_title',
						'td_start' => '<td class="tskst_$post_pst_ID$">',
						'td' => '<strong lang="@get(\'locale\')@">%task_title_link( {Obj} )%</strong>',
					);


/**
 * Visibility:
 */
function item_visibility( $Item )
{
	$r = $Item->get( 't_status' );

	// Display publish NOW button if current user has the rights:
	$r .= $Item->get_publish_link( ' ', ' ', get_icon( 'publish' ), '#', '' );

	// Display deprecate if current user has the rights:
	$r .= $Item->get_deprecate_link( ' ', ' ', get_icon( 'deprecate' ), '#', '' );

	return $r;
}
$ItemList->cols[] = array(
						'th' => T_('Visibility'),
						'order' => 'post_status',
						'td_start' => '<td class="tskst_$post_pst_ID$ nowrap">',
						'td' => '%item_visibility( {Obj} )%',
				);

$ItemList->cols[] = array(
						'th' => T_('Status'),
						'order' => 'post_pst_ID',
						'td_start' => '<td class="tskst_$post_pst_ID$ nowrap">',
						'td' => '@get(\'t_extra_status\')@',
					);

$ItemList->cols[] = array(
						'th' => T_('Type'),
						'order' => 'post_ptyp_ID',
						'td_start' => '<td class="tskst_$post_pst_ID$ nowrap">',
						'td' => '@get(\'t_type\')@',
					);

$ItemList->cols[] = array(
						'th' => T_('ID'),
						'order' => 'post_ID',
						'th_start' => '<th class="shrinkwrap">',
						'td_start' => '<td class="tskst_$post_pst_ID$ shrinkwrap">',
						'td_start' => '<td class="center">',
						'td' => '$post_ID$',
					);

$ItemList->cols[] = array(
						'th' => T_('Assigned'),
						'order' => 'post_assigned_user_ID',
						'td' => '@get(\'t_assigned_to\')@',
					);


/**
 * Deadline
 */
/*
function deadline( $date )
{
	global $localtimenow;

	$timestamp = mysql2timestamp( $date );

 	if( $timestamp <= 0 )
	{
		return '&nbsp;';	// IE needs that crap in order to display cell border :/
	}

	$output = mysql2localedate( $date );

	if( $timestamp < $localtimenow )
	{
		$output =  '<span class="past_deadline">! '.$output.'</span>';
	}

	return $output;
}
$ItemList->cols[] = array(
						'th' => T_('Deadline'),
						'order' => 'post_datedeadline',
						'td_start' => '<td class="center tskst_$post_pst_ID$">',
						'td' => '%deadline( #post_datedeadline# )%',
					);
*/

$ItemList->cols[] = array(
	'th' => /* TRANS: abbrev for info */ T_('i'),
	'order' => 'post_datemodified',
	'th_start' => '<th class="shrinkwrap">',
	'td_start' => '<td class="shrinkwrap">',
	'td' => '@history_info_icon()@',
);



/**
 * Edit Actions:
 */
function item_edit_actions( $Item )
{
	// Display edit button if current user has the rights:
	$r = $Item->get_edit_link( ' ', ' ', get_icon( 'edit' ), '#', '' );

	// Display delete button if current user has the rights:
	$r .= $Item->get_delete_link( ' ', ' ', get_icon( 'delete' ), '#', '', false );

	return $r;
}
$ItemList->cols[] = array(
		'th' => T_('Act.'),
		'td_start' => '<td class="shrinkwrap">',
		'td' => '%item_edit_actions( {Obj} )%',
	);

if( $current_User->check_perm( 'tasks', 'add', false, NULL ) )
{	// User can add a task:
	if( isset( $edited_Contact ) )
	{
		$ItemList->global_icon( T_('Add a linked task...'), 'new',
			regenerate_url( 'action,cont_ID', 'action=new&amp;cont_ID='.$edited_Contact->ID, 'tasks.php' ), T_('Add linked task') );
	}
	else
	{
		$ItemList->global_icon( T_('Add a task...'), 'new', regenerate_url( 'action', 'action=new', 'tasks.php' ), T_('Add task') );
	}
}


if( $current_User->check_perm( 'blog_post_statuses', 'any', false, $Blog->ID ) )
{	// We have permission to add a post with at least one status:
	$ItemList->global_icon( T_('Add a task...'), 'new', $add_item_url, T_('Add task') );
}


// EXECUTE the query now:
$ItemList->restart();

// Initialize funky display vars now:
global $postIDlist, $postIDarray;
$postIDlist = $ItemList->get_page_ID_list();
$postIDarray = $ItemList->get_page_ID_array();

// DISPLAY table now:
$ItemList->display();


/*
 * $Log$
 * Revision 1.3  2006/03/10 21:08:26  fplanque
 * Cleaned up post browsing a little bit..
 *
 * Revision 1.2  2006/03/08 19:53:16  fplanque
 * fixed quite a few broken things...
 *
 * Revision 1.1  2006/02/23 21:12:18  fplanque
 * File reorganization to MVC (Model View Controller) architecture.
 * See index.hml files in folders.
 * (Sorry for all the remaining bugs induced by the reorg... :/)
 *
 * Revision 1.8  2006/02/03 21:58:04  fplanque
 * Too many merges, too little time. I can hardly keep up. I'll try to check/debug/fine tune next week...
 *
 * Revision 1.7  2006/01/29 20:36:35  blueyed
 * Renamed Item::getBlog() to Item::get_Blog()
 *
 * Revision 1.6  2006/01/12 19:20:00  fplanque
 * no message
 *
 * Revision 1.5  2006/01/06 16:47:42  fplanque
 * no message
 *
 * Revision 1.4  2005/12/20 18:12:50  fplanque
 * enhanced filtering/titling framework
 *
 * Revision 1.3  2005/12/19 19:30:14  fplanque
 * minor
 *
 * Revision 1.2  2005/12/19 18:10:18  fplanque
 * Normalized the exp and tracker tabs.
 *
 * Revision 1.1  2005/12/08 13:13:33  fplanque
 * no message
 *
 */
?>