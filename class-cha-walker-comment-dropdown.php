<?php
/**
 * Create HTML dropdown of comments.
 *
 * @uses Walker_Comment
 */
class CHA_Walker_Comment_Dropdown extends Walker_Comment {
	/**
	 * @see Walker::$tree_type
	 * @var string
	 */
	var $tree_type = 'comment';

	/**
	 * @see Walker::$db_fields
	 * @var array
	 */
	var $db_fields = array ('parent' => 'comment_parent', 'id' => 'comment_ID');

	/**
	 * Override the parent walker output.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;
	}
	
	/**
	 * Override the parent walker output.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$GLOBALS['comment_depth'] = $depth + 1;
	}
	
	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment Comment data object.
	 * @param int $depth Depth of comment in reference to parents.
	 * @param array $args
	 */
	function start_el( &$output, $comment, $depth, $args, $id = 0 ) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		$GLOBALS['comment'] = $comment;
		
		// Comments at the deepest level shouldn't be set as a parent.
		// A comment can't set itself as the parent.
		// And don't include anything that isn't an actual comment.
		if ( $depth >= $args['max_depth'] || $comment->comment_ID == $args['current_comment'] || ! empty( $comment->comment_type ) ) {
			return;
		}
		
		printf( '<option value="%d"%s>%s(%s) %s',
			$comment->comment_ID,
			selected( $comment->comment_ID, $args['current_parent'], false ), // Select the current parent.
			str_repeat( '&nbsp;', ( $depth - 1 ) * 4 ), // Depth padding.
			esc_html( $comment->comment_author ),
			wp_trim_words( wp_strip_all_tags( $comment->comment_content ), 10 )
		);
	}
	
	/**
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment
	 * @param int $depth Depth of comment.
	 * @param array $args
	 */
	function end_el(&$output, $comment, $depth = 0, $args = array() ) {
		echo '</option>';
	}

}