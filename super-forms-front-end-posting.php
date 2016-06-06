<?php
/**
 * Super Forms Front-end Posting
 *
 * @package   Super Forms Front-end Posting
 * @author    feeling4design
 * @link      http://codecanyon.net/item/super-forms-drag-drop-form-builder/13979866
 * @copyright 2015 by feeling4design
 *
 * @wordpress-plugin
 * Plugin Name: Super Forms Front-end Posting
 * Plugin URI:  http://codecanyon.net/item/super-forms-drag-drop-form-builder/13979866
 * Description: Let visitors create posts from your front-end website
 * Version:     1.0.0
 * Author:      feeling4design
 * Author URI:  http://codecanyon.net/user/feeling4design
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!class_exists('SUPER_Frontend_Posting')) :


    /**
     * Main SUPER_Frontend_Posting Class
     *
     * @class SUPER_Frontend_Posting
     * @version	1.0.0
     */
    final class SUPER_Frontend_Posting {
    
        
        /**
         * @var string
         *
         *	@since		1.0.0
        */
        public $version = '1.0.0';

        
        /**
         * @var SUPER_Frontend_Posting The single instance of the class
         *
         *	@since		1.0.0
        */
        protected static $_instance = null;

        
        /**
         * Main SUPER_Frontend_Posting Instance
         *
         * Ensures only one instance of SUPER_Frontend_Posting is loaded or can be loaded.
         *
         * @static
         * @see SUPER_Frontend_Posting()
         * @return SUPER_Frontend_Posting - Main instance
         *
         *	@since		1.0.0
        */
        public static function instance() {
            if(is_null( self::$_instance)){
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        
        /**
         * SUPER_Frontend_Posting Constructor.
         *
         *	@since		1.0.0
        */
        public function __construct(){
            $this->init_hooks();
            do_action('SUPER_Frontend_Posting_loaded');
        }

        
        /**
         * Define constant if not already set
         *
         * @param  string $name
         * @param  string|bool $value
         *
         *	@since		1.0.0
        */
        private function define($name, $value){
            if(!defined($name)){
                define($name, $value);
            }
        }

        
        /**
         * What type of request is this?
         *
         * string $type ajax, frontend or admin
         * @return bool
         *
         *	@since		1.0.0
        */
        private function is_request($type){
            switch ($type){
                case 'admin' :
                    return is_admin();
                case 'ajax' :
                    return defined( 'DOING_AJAX' );
                case 'cron' :
                    return defined( 'DOING_CRON' );
                case 'frontend' :
                    return (!is_admin() || defined('DOING_AJAX')) && ! defined('DOING_CRON');
            }
        }

        
        /**
         * Hook into actions and filters
         *
         *	@since		1.0.0
        */
        private function init_hooks() {
            
            if ( $this->is_request( 'frontend' ) ) {
                
                // Filters since 1.0.0

                // Actions since 1.0.0

            }
            
            if ( $this->is_request( 'admin' ) ) {
                
                // Filters since 1.0.0
                add_filter( 'super_settings_after_smtp_server_filter', array( $this, 'add_settings' ), 10, 2 );

                // Actions since 1.0.0

            }
            
            if ( $this->is_request( 'ajax' ) ) {

                // Filters since 1.0.0

                // Actions since 1.0.0
                add_action( 'super_before_sending_email_hook', array( $this, 'before_sending_email' ) );

            }
            
        }

        /**
         * Hook into before sending email and check if we need to create or update a post or taxonomy
         *
         *  @since      1.0.0
        */
        public static function before_sending_email( $atts ) {

            $data = $atts['post']['data'];
            $settings = $atts['settings'];
            
            if( !isset( $settings['frontend_posting_action'] ) ) return true;
            if( $settings['frontend_posting_action']=='none' ) return true;

            // Create a new post
            if( $settings['frontend_posting_action']=='create_post' ) {
                
                // post_title and post_content are required so let's check if these are both set
                if( !isset( $data['post_title'] ) ) {
                    $msg = __( 'We couldn\'t find the <strong>post_title</strong> field which is required in order to create a new post. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again', 'super' );
                    SUPER_Common::output_error(
                        $error = true,
                        $msg = $msg,
                        $redirect = null
                    );
                }

                // Lets check if post type exists
                if( $settings['frontend_posting_post_type']=='' ) $settings['frontend_posting_post_type'] = 'page';
                if ( !post_type_exists( $settings['frontend_posting_post_type'] ) ) {
                    $msg = sprintf( __( 'The post type <strong>%s</strong> doesn\'t seem to exist. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' ), $settings['frontend_posting_post_type'] );
                    SUPER_Common::output_error(
                        $error = true,
                        $msg = $msg,
                        $redirect = null
                    );
                }

                // Lets check if tax_input field exists
                // If so, let's check if the post_taxonomy exists, because this is required in order to connect the categories accordingly to the post.
                if( isset( $data['tax_input'] ) ) {
                    if( (!isset( $settings['frontend_posting_post_taxonomy'] )) || ($settings['frontend_posting_post_taxonomy']=='') ) {
                        $msg = __( 'You have a field called <strong>tax_input</strong> but you haven\'t set a valid taxonomy name. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' );
                        SUPER_Common::output_error(
                            $error = true,
                            $msg = $msg,
                            $redirect = null
                        );
                    }else{
                        if ( !taxonomy_exists( $settings['frontend_posting_post_taxonomy'] ) ) {
                            $msg = sprintf( __( 'The taxonomy <strong>%s</strong> doesn\'t seem to exist. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' ), $settings['frontend_posting_post_taxonomy'] );
                            SUPER_Common::output_error(
                                $error = true,
                                $msg = $msg,
                                $redirect = null
                            );
                        }
                    }
                }

                $postarr = array();
                
                // Default values from the form settings
                $postarr['post_type'] = sanitize_text_field( $settings['frontend_posting_post_type'] );
                $postarr['post_status'] = sanitize_text_field( $settings['frontend_posting_status'] );
                $postarr['post_parent'] = absint( $settings['frontend_posting_post_parent'] );
                $postarr['comment_status'] = sanitize_text_field( $settings['frontend_posting_comment_status'] );
                $postarr['ping_status'] = sanitize_text_field( $settings['frontend_posting_ping_status'] );
                $postarr['post_password'] = $settings['frontend_posting_post_password'];
                $postarr['post_author'] = absint( $settings['frontend_posting_author'] );

                // Override default values for form field values
                $postarr['post_title'] = $data['post_title']['value'];
                if( isset( $data['post_content'] ) ) $postarr['post_content'] = $data['post_content']['value'];
                if( isset( $data['post_excerpt'] ) ) $postarr['post_excerpt'] = $data['post_excerpt']['value'];
                if( isset( $data['post_type'] ) ) $postarr['post_type'] = sanitize_text_field( $data['post_type']['value'] );
                if( isset( $data['post_status'] ) ) $postarr['post_status'] = sanitize_text_field( $data['post_status']['value'] );
                if( isset( $data['post_parent'] ) ) $postarr['post_parent'] = absint( $data['post_parent']['value'] );
                if( isset( $data['comment_status'] ) ) $postarr['comment_status'] = sanitize_text_field( $data['comment_status']['value'] );
                if( isset( $data['ping_status'] ) ) $postarr['ping_status'] = sanitize_text_field( $data['ping_status']['value'] );
                if( isset( $data['post_password'] ) ) $postarr['post_password'] = $data['post_password']['value'];
                if( isset( $data['post_author'] ) ) $postarr['post_author'] = absint( $data['post_author']['value'] );
                if( (isset( $data['post_date'] )) && (isset( $data['post_time'] )) ) {
                    $postarr['post_time'] = date( 'H:i:s', strtotime($data['post_time']['value'] ) ); // Must be formatted as '18:57:33';
                    $postarr['post_date'] = date( 'Y-m-d', strtotime($data['post_date']['value'] ) ); // Must be formatted as '2010-02-23';
                    $postarr['post_date'] = $postarr['post_date'] . ' ' . $postarr['post_time']; // Must be formatted as '2010-02-23 18:57:33';
                }else{
                    if( isset( $data['post_date'] ) ) {
                        $postarr['post_date'] = date( 'Y-m-d H:i:s', strtotime($data['post_date']['value'] ) ); // Must be formatted as '2010-02-23 18:57:33';
                    }
                }

                // Collect categories from the field tax_input (only if field can be found)
                if( isset( $data['tax_input'] ) ) {
                    $tax_input = array();
                    $categories = explode( ",", $data['tax_input']['value'] );
                    foreach( $categories as $slug ) {
                        $slug = trim($slug);
                        if( !empty( $slug ) ) {
                            $term = get_term_by( 'slug', $slug, $settings['frontend_posting_post_taxonomy'] );
                            $term_id = $term->term_id;
                            $tax_input[$settings['frontend_posting_post_taxonomy']][] = $term_id;
                        }
                    }
                    $postarr['tax_input'] = $tax_input;
                }

                // Get the post ID or return the error(s)
                $result = wp_insert_post( $postarr, true );
                if( isset( $result->errors ) ) {
                    $msg = '';
                    foreach( $result->errors as $v ) {
                        $msg .= '- ' . $v[0] . '<br />';
                    }
                    SUPER_Common::output_error(
                        $error = true,
                        $msg = $msg,
                        $redirect = null
                    );
                }else{
                    // Save custom post meta
                    $post_id = $result;
                    $meta_data = array();
                    $custom_meta = explode( "\n", $settings['frontend_posting_meta'] );
                    foreach( $custom_meta as $k ) {
                        $field = explode( "|", $k );
                        if( isset( $data[$field[0]]['value'] ) ) {
                            $meta_data[$field[1]] = $data[$field[0]]['value'];
                        }
                    }
                    foreach( $meta_data as $k => $v ) {
                        add_post_meta( $post_id, $k, $v );
                    }
                }
                exit;

            }
            
            // Update an existing post
            if( $settings['frontend_posting_action']=='update_post' ) {

            }
            
            // Create a new taxonomy
            if( $settings['frontend_posting_action']=='create_taxonomy' ) {

            }            
            
            // Update an existing taxonomy
            if( $settings['frontend_posting_action']=='update_taxonomy' ) {

            }

            /*
            'ID'
            (int) The post ID. If equal to something other than 0, the post with that ID will be updated. Default 0.
            'post_author'
            (int) The ID of the user who added the post. Default is the current user ID.
            'post_date'
            (string) The date of the post. Default is the current time.
            'post_date_gmt'
            (string) The date of the post in the GMT timezone. Default is the value of $post_date.
            'post_content'
            (mixed) The post content. Default empty.
            'post_content_filtered'
            (string) The filtered post content. Default empty.
            'post_title'
            (string) The post title. Default empty.
            'post_excerpt'
            (string) The post excerpt. Default empty.
            'post_status'
            (string) The post status. Default 'draft'.
            'post_type'
            (string) The post type. Default 'post'.
            'comment_status'
            (string) Whether the post can accept comments. Accepts 'open' or 'closed'. Default is the value of 'default_comment_status' option.
            'ping_status'
            (string) Whether the post can accept pings. Accepts 'open' or 'closed'. Default is the value of 'default_ping_status' option.
            'post_password'
            (string) The password to access the post. Default empty.
            'post_name'
            (string) The post name. Default is the sanitized post title when creating a new post.
            'to_ping'
            (string) Space or carriage return-separated list of URLs to ping. Default empty.
            'pinged'
            (string) Space or carriage return-separated list of URLs that have been pinged. Default empty.
            'post_modified'
            (string) The date when the post was last modified. Default is the current time.
            'post_modified_gmt'
            (string) The date when the post was last modified in the GMT timezone. Default is the current time.
            'post_parent'
            (int) Set this for the post it belongs to, if any. Default 0.
            'menu_order'
            (int) The order the post should be displayed in. Default 0.
            'post_mime_type'
            (string) The mime type of the post. Default empty.
            'guid'
            (string) Global Unique ID for referencing the post. Default empty.
            'tax_input'
            (array) Array of taxonomy terms keyed by their taxonomy name. Default empty.
            'meta_input'
            (array) Array of post meta values keyed by their post meta key. Default empty.
            */
        }


    


        /**
         * Hook into settings and add Front-end Posting settings
         *
         *  @since      1.0.0
        */
        public static function add_settings( $array, $settings ) {
            global $wp_roles;
            $all_roles = $wp_roles->roles;
            $editable_roles = apply_filters( 'editable_roles', $all_roles );
            $roles = array(
                '' => __( 'All user roles', 'super' )
            );
            foreach( $editable_roles as $k => $v ) {
                $roles[$k] = $v['name'];
            }
            $reg_roles = $roles;
            unset($reg_roles['']);
            $array['frontend_posting'] = array(        
                'name' => __( 'Front-end Posting', 'super' ),
                'label' => __( 'Front-end Posting Settings', 'super' ),
                'fields' => array(
                    'frontend_posting_action' => array(
                        'name' => __( 'Actions', 'super' ),
                        'desc' => __( 'Select what this form should do (register or login)?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_action', $settings['settings'], 'none' ),
                        'filter' => true,
                        'type' => 'select',
                        'values' => array(
                            'none' => __( 'None (do nothing)', 'super' ),
                            'create_post' => __( 'Create new Post', 'super' ), //(post, page, product etc.)
                            'update_post' => __( 'Update existing Post', 'super' ),
                            'create_taxonomy' => __( 'Create new Taxonomy', 'super' ), //(category, product_cat etc.)
                            'update_taxonomy' => __( 'Update existing Taxonomy', 'super' ),
                        ),
                    ),
                    'frontend_posting_post_type' => array(
                        'name' => __( 'Post type', 'super' ),
                        'desc' => __( 'Enter the name of the post type (e.g: post, page, product)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_type', $settings['settings'], 'page' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post,update_post',
                    ),
                    'frontend_posting_taxonomy' => array(
                        'name' => __( 'Taxonomy type', 'super' ),
                        'desc' => __( 'Enter the name of the taxonomy (e.g: category, product_cat)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_taxonomy', $settings['settings'], 'category' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_taxonomy,update_taxonomy',
                    ),
                    'frontend_posting_status' => array(
                        'name' => __( 'Status', 'super' ),
                        'desc' => __( 'Select what the status should be (publish, future, draft, pending, private, trash, auto-draft)?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_status', $settings['settings'], 'publish' ),
                        'type' => 'select',
                        'values' => array(
                            'publish' => __( 'Publish (default)', 'super' ),
                            'future' => __( 'Future', 'super' ),
                            'draft' => __( 'Draft', 'super' ),
                            'pending' => __( 'Pending', 'super' ),
                            'private' => __( 'Private', 'super' ),
                            'trash' => __( 'Trash', 'super' ),
                            'auto-draft' => __( 'Auto-Draft', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_post_parent' => array(
                        'name' => __( 'Parent ID (leave blank for none)', 'super' ),
                        'desc' => __( 'Enter a parent ID if you want the post to have a parent', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_parent', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_comment_status' => array(
                        'name' => __( 'Allow comments', 'super' ),
                        'desc' => __( 'Whether the post can accept comments', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_comment_status', $settings['settings'], '' ),
                        'type' => 'select',
                        'values' => array(
                            '' => __( 'Default (use the default_comment_status option)', 'super' ),
                            'open' => __( 'Open (allow comments)', 'super' ),
                            'closed' => __( 'Closed (disallow comments)', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_ping_status' => array(
                        'name' => __( 'Allow comments', 'super' ),
                        'desc' => __( 'Whether the post can accept comments', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_ping_status', $settings['settings'], '' ),
                        'type' => 'select',
                        'values' => array(
                            '' => __( 'Default (use the default_ping_status option)', 'super' ),
                            'open' => __( 'Open (allow comments)', 'super' ),
                            'closed' => __( 'Closed (disallow comments)', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_post_password' => array(
                        'name' => __( 'Password protect (leave blank for none)', 'super' ),
                        'desc' => __( 'The password to access the post', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_password', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_menu_order' => array(
                        'name' => __( 'Menu order (blank = 0)', 'super' ),
                        'desc' => __( 'The order the post should be displayed in', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_menu_order', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_guid' => array(
                        'name' => __( 'GUID', 'super' ),
                        'desc' => __( 'Global Unique ID for referencing the post', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_guid', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_meta' => array(
                        'name' => __( 'Save custom post meta', 'super' ),
                        'desc' => __( 'Based on your form fields you can save custom meta for your post', 'super' ),
                        'type' => 'textarea',
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_meta', $settings['settings'], "field_name|meta_key\nfield_name2|meta_key2\nfield_name3|meta_key3" ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_author' => array(
                        'name' => __( 'Author ID (default = current user ID if logged in)', 'super' ),
                        'desc' => __( 'The ID of the user where the post will belong to', 'super' ),
                        'type' => 'textarea',
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_author', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_post_taxonomy' => array(
                        'name' => __( 'The taxonomy name (e.g: category or product_cat', 'super' ),
                        'desc' => __( 'Required to connect the post to categories (if found)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_taxonomy', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),

                    /*
                    'frontend_posting_post_type' => array(
                        'name' => __( 'Post type', 'super' ),
                        'desc' => __( 'Select what this form should do (register or login)?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_action', $settings['settings'], 'none' ),
                        'filter' => true,
                        'type' => 'select',
                        'values' => array(
                            'none' => __( 'None (do nothing)', 'super' ),
                            'create' => __( 'Create', 'super' ),
                            'update' => __( 'Update', 'super' ),
                        ),
                    ),
                    'login_user_role' => array(
                        'name' => __( 'Allowed user role(s)', 'super' ),
                        'desc' => __( 'Which user roles are allowed to login?', 'super' ),
                        'type' => 'select',
                        'multiple' => true,
                        'default' => SUPER_Settings::get_value( 0, 'login_user_role', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'login',
                        'values' => $roles,
                    ),
                    'register_user_role' => array(
                        'name' => __( 'User role', 'super' ),
                        'desc' => __( 'What user role should this user get?', 'super' ),
                        'type' => 'select',
                        'default' => SUPER_Settings::get_value( 0, 'register_user_role', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'register',
                        'values' => $reg_roles,
                    ),
                    'frontend_posting_activation' => array(
                        'name' => __( 'Send activation email', 'super' ),
                        'desc' => __( 'Optionally let users activate their account or let them instantly login without verification', 'super' ),
                        'type' => 'select',
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_activation', $settings['settings'], 'verify' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'register',
                        'values' => array(
                            'verify' => __( 'Send activation email', ' super' ),
                            'auto' => __( 'Auto activate and login', 'super' ),
                        ),
                    ),
                    'frontend_posting_url' => array(
                        'name' => __( 'Login page URL', 'super' ),
                        'desc' => __( 'URL of your login page where you placed the login form, here users can activate their account', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_url', $settings['settings'], get_site_url() . '/login/' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'register,login,reset_password',
                    ),
                    'register_welcome_back_msg' => array(
                        'name' => __( 'Welcome back message', 'super' ),
                        'desc' => __( 'Display a welcome message after user has logged in (leave blank for no message)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_welcome_back_msg', $settings['settings'], __( 'Welcome back {field_user_login}!', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'login',
                    ),
                    'register_incorrect_code_msg' => array(
                        'name' => __( 'Incorrect activation code message', 'super' ),
                        'desc' => __( 'Display a message when the activation code is incorrect', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_incorrect_code_msg', $settings['settings'], __( 'The combination username, password and activation code is incorrect!', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'login',
                    ),
                    'register_account_activated_msg' => array(
                        'name' => __( 'Account activated message', 'super' ),
                        'desc' => __( 'Display a message when account has been activated', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_account_activated_msg', $settings['settings'], __( 'Hello {field_user_login}, your account has been activated!', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'login',
                    ),
                    'register_activation_subject' => array(
                        'name' => __( 'Activation Email Subject', 'super' ),
                        'desc' => __( 'Example: Activate your account', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_activation_subject', $settings['settings'], __( 'Activate your account', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'register,login',
                    ),
                    'register_activation_email' => array(
                        'name' => __( 'Activation Email Body', 'super' ),
                        'desc' => __( 'The email message. You can use {activation_code} and {frontend_posting_url}', 'super' ),
                        'type' => 'textarea',
                        'default' => SUPER_Settings::get_value( 0, 'register_activation_email', $settings['settings'], "Dear {field_user_login},\n\nThank you for registering! Before you can login you will need to activate your account.\nBelow you will find your activation code. You need this code to activate your account:\n\nActivation Code: <strong>{register_activation_code}</strong>\n\nClick <a href=\"{frontend_posting_url}?code={register_activation_code}\">here</a> to activate your account with the provided code.\n\n\nBest regards,\n\n{option_blogname}" ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'register,login',
                    ),                                      
                    'frontend_posting_user_meta' => array(
                        'name' => __( 'Save custom user meta', 'super' ),
                        'desc' => __( 'Usefull for external plugins such as WooCommerce. Example: "field_name|meta_key" (each on a new line)', 'super' ),
                        'type' => 'textarea',
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_user_meta', $settings['settings'], "first_name|billing_first_name\nlast_name|billing_last_name\naddress|billing_address" ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'register',
                    ),
                    'register_reset_password_success_msg' => array(
                        'name' => __( 'Success message', 'super' ),
                        'desc' => __( 'Display a message after user has reset their password (leave blank for no message)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_reset_password_success_msg', $settings['settings'], __( 'Your password has been reset. We have just send you a new password to your email address.', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'reset_password',
                    ),
                    'register_reset_password_not_exists_msg' => array(
                        'name' => __( 'Not found message', 'super' ),
                        'desc' => __( 'Display a message when no user was found (leave blank for no message)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_reset_password_not_exists_msg', $settings['settings'], __( 'We couldn\'t find a user with the given email address!', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'reset_password',
                    ),
                    'register_reset_password_subject' => array(
                        'name' => __( 'Lost Password Email Subject', 'super' ),
                        'desc' => __( 'Example: Your new password. You can use {user_login}', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'register_reset_password_subject', $settings['settings'], __( 'Your new password', 'super' ) ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'reset_password',
                    ),
                    'register_reset_password_email' => array(
                        'name' => __( 'Lost Password Email Body', 'super' ),
                        'desc' => __( 'The email message. You can use {user_login}, {register_generated_password} and {frontend_posting_url}', 'super' ),
                        'type' => 'textarea',
                        'default' => SUPER_Settings::get_value( 0, 'register_reset_password_email', $settings['settings'], "Dear {user_login},\n\nYou just requested to reset your password.\nUsername: <strong>{user_login}</strong>\nPassword: <strong>{register_generated_password}</strong>\n\nClick <a href=\"{frontend_posting_url}\">here</a> to login with your new password.\n\n\nBest regards,\n\n{option_blogname}" ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'reset_password',
                    ),
                    */
                )
            );
            return $array;
        }



    }
        
endif;


/**
 * Returns the main instance of SUPER_Frontend_Posting to prevent the need to use globals.
 *
 * @return SUPER_Frontend_Posting
 */
function SUPER_Frontend_Posting() {
    return SUPER_Frontend_Posting::instance();
}


// Global for backwards compatibility.
$GLOBALS['SUPER_Frontend_Posting'] = SUPER_Frontend_Posting();