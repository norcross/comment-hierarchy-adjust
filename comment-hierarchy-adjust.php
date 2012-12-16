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
class Comment_Heirarchy_Adjust
{

    /**
     * This is our constructor
     *
     * @return Comment_Heirarchy_Adjust
     */
    public function __construct() {
        add_action      ( 'plugins_loaded',             array( $this, 'textdomain'          )           );
        add_action      ( 'admin_init',                 array( $this, 'cha_setup'           )           );
        add_action      ( 'admin_enqueue_scripts',      array( $this, 'admin_scripts'       ), 10       );
        add_action      ( 'wp_ajax_save_parent',        array( $this, 'save_parent'         )           );
    }

    /**
     * load textdomain for international goodness
     *
     * @return Comment_Heirarchy_Adjust
     */

    public function textdomain() {

        load_plugin_textdomain( 'cha', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * admin call for loading metabox on comments
     *
     * @return Comment_Heirarchy_Adjust
     */

    public function cha_setup() {

        add_meta_box('metabox_cha', __('Comment Heirarchy', 'cha'), array($this, 'metabox_cha'), 'comment', 'normal');

    }

    /**
     * Admin scripts and styles
     *
     * @return Comment_Heirarchy_Adjust
     */

    public function admin_scripts($hook) {

        if ( $hook == 'comment.php' ) :

            wp_enqueue_script( 'cha-admin', plugins_url('/js/cha.ajax.js', __FILE__) , array('jquery'), null, true );

        endif;

    }

    /**
     * helper function for grabbing comment info
     *
     * @return Comment_Heirarchy_Adjust
     */

    public function comment_list($current_post) {

        // comment query arguments
        $args = array (
            'post_id'   => $current_post,
            'orderby'   => '',
            'order'     => 'ASC',
            );


        $comment_list   = get_comments( $args );

        return $comment_list;

    }

    /**
     * store new data
     *
     * @return Comment_Heirarchy_Adjust
     */

    public function save_parent() {

        // get my variables
        $comment    = $_POST['comment'];
        $postID     = $_POST['postID'];
        $parent     = $_POST['parent'];

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
     * @return Comment_Heirarchy_Adjust
     */

    public function metabox_cha($comment) {

        // grab comment ID to pass onto post variable
        $current_id     = $comment->comment_ID;
        $current_post   = $comment->comment_post_ID;
        ?>
        <table class="form-table editcomment comment_xtra">
        <tbody>
        <tr valign="top">
            <td class="first"><?php _e( 'Change Parent:', 'cha' ); ?></td>
            <td>
            <select name="comment_parent" id="comment_parent">
                <option value="0" <?php selected( $comment->comment_parent, 0 ); ?> ><?php _e( 'None', 'cha' ); ?></option>
                <?php
                // grab comments in the post array
                $comment_list = $this->comment_list($current_post);
                // now loop through each comment
                foreach ( $comment_list as $single_comment ) :

                    // grab some variables for each comment
                    $single_id  = $single_comment->comment_ID;
                    $single_wds = $single_comment->comment_content;
                    $single_ath = $single_comment->comment_author;

                    // check for current parent
                    $current    = $single_id == $comment->comment_parent ? 'selected="selected"' : '';

                    // output each comment in the post array, excluding itself
                    if ($single_id != $current_id) :
                        $option = '<option value="' . $single_id . '" '.$current.'>';
                        $option .= '('.$single_ath.') '.wp_trim_words( $single_wds, 10, null );
                        $option .= '</option>';
                        // return each one
                        echo $option;
                    endif;

                endforeach;
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
$Comment_Heirarchy_Adjust = new Comment_Heirarchy_Adjust();
