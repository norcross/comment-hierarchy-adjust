<?php
/*
Plugin Name: Comment Hierarchy Adjust
Plugin URI: http://andrewnorcross.com/plugins/
Description: Something
Version: 0.1
Author: Andrew Norcross
Author URI: http://andrewnorcross.com

    Copyright 2012 Andrew Norcross

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Start up the engine
class Comment_Hierarchy_Adjust
{

    /**
     * This is our constructor
     *
     * @return Comment_Hierarchy_Adjust
     */
    public function __construct() {
        
		add_action( 'plugins_loaded',        array( $this, 'textdomain'    )     );
        add_action( 'add_meta_boxes',        array( $this, 'cha_setup'     )     );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10 );
        add_action( 'wp_ajax_save_parent',   array( $this, 'save_parent'   )     );
		
    }

    /**
     * load textdomain for international goodness
     *
     * @return Comment_Hierarchy_Adjust
     */

    public function textdomain() {

        load_plugin_textdomain( 'cha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
    }

    /**
     * admin call for loading metabox on comments
     *
     * @return Comment_Hierarchy_Adjust
     */

    public function cha_setup() {

        add_meta_box('metabox_cha', __('Comment Hierarchy', 'cha'), array($this, 'metabox_cha'), 'comment', 'normal');

    }

    /**
     * Admin scripts and styles
     *
     * @return Comment_Hierarchy_Adjust
     */

    public function admin_scripts($hook) {

        if ( $hook == 'comment.php' ) {

            wp_enqueue_script( 'cha-admin', plugins_url('/js/cha.ajax.js', __FILE__) , array('jquery'), null, true );

		}

    }

    /**
     * helper function for grabbing comment info
     *
     * @return Comment_Hierarchy_Adjust
     */

    public function comment_list($current_post) {

        // comment query arguments
        $args = array (
            'post_id' => $current_post,
            'type'    => 'comment',
            'order'   => 'ASC',
            );

        $comment_list = get_comments($args);

        return $comment_list;

    }

    /**
     * store new data
     *
     * @return Comment_Hierarchy_Adjust
     */

    public function save_parent() {

        // get my variables
        $comment = $_POST['comment'];
        $postID  = $_POST['postID'];
        $parent  = $_POST['parent'];

        $ret = array();

        // check post ID first
        if( !isset( $postID ) || !is_numeric( $postID ) ) {

            $ret['success'] = false;
            $ret['message'] = 'No post exists to update';

            echo json_encode($ret);
            die();
        }

        // check comment ID
        if( !isset( $comment ) || !is_numeric( $comment ) ) {

            $ret['success'] = false;
            $ret['message'] = 'No parent selected';

            echo json_encode($ret);
            die();
        }

        // now check parent
        if( !isset( $parent ) || !is_numeric( $parent ) ) {

            $ret['success'] = false;
            $ret['message'] = 'No parent selected';

            echo json_encode($ret);
            die();
        }

        // all good? then let's proceed
        $ret['success'] = true;
        $ret['message'] = 'Comment updated';

        // update the comment setup now
        $updates = get_comment($comment, ARRAY_A);
        $updates['comment_parent'] = $parent;
        wp_update_comment( $updates );

        echo json_encode($ret);
        die();

    }

    /**
     * comment metabox
     *
     * @return Comment_Hierarchy_Adjust
     */

    public function metabox_cha($comment) {
		include_once( plugin_dir_path( __FILE__ ) . 'class-cha-walker-comment-dropdown.php' );
		
		// display an error if comment threading is disabled
		if ( ! get_option( 'thread_comments' ) ) {
			
			printf( '<div class="error below-h2"><p><a href="%s">%s</a></p></div>',
				admin_url( 'options-discussion.php' ),
				__( 'Threaded comments are disabled.', 'cha' )
			);
			
			return;
			
		}
        ?>
        <table class="form-table editcomment comment_xtra">
        <tbody>
        <tr valign="top">
            <td class="first"><label for="comment_parent"><?php _e( 'Change Parent:', 'cha' ); ?></label></td>
            <td>
            <select name="comment_parent" id="comment_parent">
                <option value="0" <?php selected( $comment->comment_parent, 0 ); ?> ><?php _e( 'None', 'cha' ); ?></option>
                <?php
                // grab comments in the post array
                $comment_list = $this->comment_list($comment->comment_post_ID);
                
				$comment_list_args = array(
					'current_comment' => $comment->comment_ID,
					'current_parent'  => $comment->comment_parent,
					'max_depth'       => get_option( 'thread_comments_depth' ),
					'walker'          => new CHA_Walker_Comment_Dropdown
				);
				
				wp_list_comments( $comment_list_args, $comment_list );
                ?>
            </select>
            </td>
        </tr>
        </tbody>
        </table>
    <?php
    }

/// end class
}


// Instantiate our class
$Comment_Hierarchy_Adjust = new Comment_Hierarchy_Adjust();