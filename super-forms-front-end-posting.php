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
                add_action( 'super_before_email_success_msg_action', array( $this, 'before_email_success_msg' ) );

            }
            
        }

        /**
         * Hook into before sending email and check if we need to create or update a post or taxonomy
         *
         *  @since      1.0.0
        */
        public static function before_email_success_msg( $atts ) {

            $data = get_post_meta( $atts['entry_id'], '_super_contact_entry_data', true );
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
                    if( (!isset( $settings['frontend_posting_post_cat_taxonomy'] )) || ($settings['frontend_posting_post_cat_taxonomy']=='') ) {
                        $msg = __( 'You have a field called <strong>tax_input</strong> but you haven\'t set a valid taxonomy name. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' );
                        SUPER_Common::output_error(
                            $error = true,
                            $msg = $msg,
                            $redirect = null
                        );
                    }else{
                        if ( !taxonomy_exists( $settings['frontend_posting_post_cat_taxonomy'] ) ) {
                            $msg = sprintf( __( 'The taxonomy <strong>%s</strong> doesn\'t seem to exist. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' ), $settings['frontend_posting_post_cat_taxonomy'] );
                            SUPER_Common::output_error(
                                $error = true,
                                $msg = $msg,
                                $redirect = null
                            );
                        }
                    }
                }

                // Lets check if tax_input field exists
                // If so, let's check if the post_taxonomy exists, because this is required in order to connect the categories accordingly to the post.
                if( isset( $data['tax_input'] ) ) {
                    if( (!isset( $settings['frontend_posting_post_tag_taxonomy'] )) || ($settings['frontend_posting_post_tag_taxonomy']=='') ) {
                        $msg = __( 'You have a field called <strong>tag_input</strong> but you haven\'t set a valid taxonomy name. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' );
                        SUPER_Common::output_error(
                            $error = true,
                            $msg = $msg,
                            $redirect = null
                        );
                    }else{
                        if ( !taxonomy_exists( $settings['frontend_posting_post_tag_taxonomy'] ) ) {
                            $msg = sprintf( __( 'The taxonomy <strong>%s</strong> doesn\'t seem to exist. Please <a href="' . get_admin_url() . 'admin.php?page=super_create_form&id=' . absint( $atts['post']['form_id'] ) . '">edit</a> your form and try again ', 'super' ), $settings['frontend_posting_post_tag_taxonomy'] );
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
                if($settings['frontend_posting_author']!='') {
                    $postarr['post_author'] = absint( $settings['frontend_posting_author'] );
                }
                $post_format = sanitize_text_field( $settings['frontend_posting_post_format'] );
                $tax_input = sanitize_text_field( $settings['frontend_posting_tax_input'] );
                $tags_input = sanitize_text_field( $settings['frontend_posting_tags_input'] );

                // Override default values for form field values
                $postarr['post_title'] = $data['post_title']['value'];
                if( isset( $data['post_content'] ) ) $postarr['post_content'] = $data['post_content']['value'];
                if( isset( $data['post_excerpt'] ) ) $postarr['post_excerpt'] = $data['post_excerpt']['value'];
                if( isset( $data['post_type'] ) ) $postarr['post_type'] = sanitize_text_field( $data['post_type']['value'] );
                if( isset( $data['post_format'] ) ) $post_format = sanitize_text_field( $data['post_format']['value'] );
                if( isset( $data['tax_input'] ) ) $tax_input = sanitize_text_field( $data['tax_input']['value'] );
                if( isset( $data['tags_input'] ) ) $tags_input = sanitize_text_field( $data['tags_input']['value'] );


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

                    $post_id = $result;

                    // Collect categories from the field tax_input
                    if( $tax_input!='' ) {
                        $tax_input_array = array();
                        $categories = explode( ",", $tax_input );
                        foreach( $categories as $slug ) {
                            $slug = trim($slug);
                            if( !empty( $slug ) ) {
                                $tax_input_array[] = $slug;
                            }
                        }
                        wp_set_object_terms($post_id, $tax_input_array, $settings['frontend_posting_post_cat_taxonomy'] );
                    }

                    // Collect tags from the field tags_input
                    if( $tags_input!='' ) {
                        $tags_input_array = array();
                        $categories = explode( ",", $tags_input );
                        foreach( $categories as $slug ) {
                            $slug = trim($slug);
                            if( !empty( $slug ) ) {
                                $tags_input_array[] = $slug;
                            }
                        }
                        wp_set_object_terms($post_id, $tags_input_array, $settings['frontend_posting_post_tag_taxonomy'] );
                    }

                    // Check if we are saving a WooCommerce product
                    if( $postarr['post_type']=='product' ) {

                        // Set the product type (default = simple)
                        $product_type = sanitize_text_field( $settings['frontend_posting_product_type'] );
                        if( isset( $data['product_type'] ) ) $product_type = sanitize_text_field( $data['product_type']['value'] );
                        if($product_type=='') $product_type = 'simple';
                        wp_set_object_terms($post_id, $product_type, 'product_type');

                        // Save all the product meta data
                        $fields = array(
                            'product_downloadable' => '_downloadable',
                            'product_virtual' => '_virtual',
                            'product_visibility' => '_visibility',
                            'product_featured' => '_featured',
                            'product_stock_status' => '_stock_status',
                            'product_manage_stock' => '_manage_stock',
                            'product_stock' => '_stock',
                            'product_backorders' => '_backorders',
                            'product_sold_individually' => '_sold_individually',
                            'product_regular_price' => '_regular_price',
                            'product_sale_price' => '_sale_price',
                            'product_purchase_note' => '_purchase_note',
                            'product_weight' => '_weight',
                            'product_length' => '_length',
                            'product_width' => '_width',
                            'product_height' => '_height',
                            'product_sku' => '_sku',
                            'product_attributes' => '_product_attributes',
                            'product_sale_price_dates_from' => '_sale_price_dates_from',
                            'product_sale_price_dates_to' => '_sale_price_dates_to',
                            'product_price' => '_price',
                            
                            
                            'product_downloadable_files' => '_downloadable_files',
                            'product_download_limit' => '_download_limit',
                            'product_download_expiry' => '_download_expiry',
                            'product_download_type' => '_download_type',


                            // Do we really need this? I don't think so, if a client requests this we will add it
                            // For now we will just comment it
                            //'product_total_sales' => 'total_sales',

                            // file paths will be stored in an array keyed off md5(file path)
                            //$downdloadArray =array('name'=>"Test", 'file' => $uploadDIR['baseurl']."/video/".$video);
                            //$file_path =md5($uploadDIR['baseurl']."/video/".$video);
                            //$_file_paths[  $file_path  ] = $downdloadArray;
                            // grant permission to any newly added files on any existing orders for this product
                            // do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $downdloadArray );
                            //update_post_meta( $post_id, '_downloadable_files', $_file_paths);
                            //update_post_meta( $post_id, '_download_limit', '');
                            //update_post_meta( $post_id, '_download_expiry', '');
                            //update_post_meta( $post_id, '_download_type', '');

                        );
                        foreach( $fields as $k => $v ) {
                            if( $k!='product_downloadable_files' ) {
                                $field_value = '';
                                if( isset( $settings['frontend_posting_'.$k] ) ) $field_value = sanitize_text_field( $settings['frontend_posting_'.$k] );
                                if( isset( $data[$k] ) ) $field_value = sanitize_text_field( $data[$k]['value'] );
                                update_post_meta( $post_id, $v, $field_value );
                            }else{
                                if( isset( $data['downloadable_files'] ) ) {
                                    $files = array();
                                    $_file_paths = array();
                                    foreach( $data['downloadable_files']['files'] as $v ) {
                                        $name = get_the_title( $v['attachment'] );
                                        $url = $v['url'];
                                        $array = array( 'name'=>$name, 'file' => $url );
                                        $url = md5( $url );
                                        $_file_paths[$url] = $array;
                                    }
                                    update_post_meta( $post_id, '_downloadable_files', $_file_paths);
                                }
                            }
                        }

                        // If we are saving a WooCommerce product check if we need to add images to the gallery
                        if( isset( $data['image_gallery'] ) ) {
                            $files = array();
                            foreach( $data['image_gallery']['files'] as $v ) {
                                $files[] = $v['attachment'];
                            }
                            $files = implode( ',', $files );
                            update_post_meta( $post_id, '_product_image_gallery', $files );
                        }

                    }


                    // Save custom post meta
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

                    // Set post format for the post if theme supports it and if it was set by the form settings or by one of the form fields
                    if ( current_theme_supports( 'post-formats' ) ) {
                        $post_formats = get_theme_support( 'post-formats' );
                        if ( is_array( $post_formats[0] ) ) {
                            if ( in_array( $post_format, $post_formats[0] ) ) {
                                set_post_format( $post_id , $post_format);
                            }
                        }
                    }

                    // Set the featured image if a file upload field with the name featured_image was found
                    if( isset( $data['featured_image'] ) ) {
                        set_post_thumbnail( $post_id, $data['featured_image']['files'][0]['attachment'] );
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
                        'name' => __( 'Allow pings', 'super' ),
                        'desc' => __( 'Whether the post can accept pings', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_ping_status', $settings['settings'], '' ),
                        'type' => 'select',
                        'values' => array(
                            '' => __( 'Default (use the default_ping_status option)', 'super' ),
                            'open' => __( 'Open (allow pings)', 'super' ),
                            'closed' => __( 'Closed (disallow pings)', 'super' ),
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
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_author', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_post_cat_taxonomy' => array(
                        'name' => __( 'The cat taxonomy name (e.g: category or product_cat)', 'super' ),
                        'desc' => __( 'Required to connect the post to categories (if found)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_cat_taxonomy', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_tax_input' => array(
                        'name' => __( 'The post categories slug(s) (e.g: books, cars)', 'super' ),
                        'desc' => __( 'Category slug separated by comma', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_tax_input', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_post_tag_taxonomy' => array(
                        'name' => __( 'The tag taxonomy name (e.g: post_tag or product_tag)', 'super' ),
                        'desc' => __( 'Required to connect the post to categories (if found)', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_tag_taxonomy', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_tags_input' => array(
                        'name' => __( 'The post tags', 'super' ),
                        'desc' => __( 'Post tags separated by comma', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_tags_input', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_action',
                        'filter_value' => 'create_post',
                    ),
                    'frontend_posting_post_format' => array(
                        'name' => __( 'The post format (e.g: quote, gallery, audio etc.', 'super' ),
                        'desc' => __( 'Leave blank for no post format', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_post_format', $settings['settings'], '' ),
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
                    'frontend_posting_product_type' => array(
                        'name' => __( 'Product Type (e.g: simple, grouped, external, variable)', 'super' ),
                        'desc' => __( 'Leave blank to use the default product type: simple', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_type', $settings['settings'], '' ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_featured' => array(
                        'name' => __( 'Featured product', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_featured', $settings['settings'], 'no' ),
                        'type' => 'select',
                        'values' => array(
                            'no' => __( 'No (default)', 'super' ),
                            'yes' => __( 'Yes', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_stock_status' => array(
                        'name' => __( 'In stock?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_stock_status', $settings['settings'], 'yes' ),
                        'type' => 'select',
                        'values' => array(
                            'instock' => __( 'In stock (default)', 'super' ),
                            'outofstock' => __( 'Out of stock', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_manage_stock' => array(
                        'name' => __( 'Manage stock?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_manage_stock', $settings['settings'], 'no' ),
                        'type' => 'select',
                        'values' => array(
                            'no' => __( 'No (default)', 'super' ),
                            'yes' => __( 'Yes', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_stock' => array(
                        'name' => __( 'Stock Qty', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_stock', $settings['settings'], '' ),
                        'type' => 'slider',
                        'min' => 0,
                        'max' => 100,
                        'steps' => 1,
                        'filter' => true,
                        'parent' => 'frontend_posting_product_manage_stock',
                        'filter_value' => 'yes',
                    ),
                    'frontend_posting_product_backorders' => array(
                        'name' => __( 'Allow Backorders?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_backorders', $settings['settings'], 'no' ),
                        'type' => 'select',
                        'values' => array(
                            'no' => __( 'Do not allow (default)', 'super' ),
                            'notify' => __( 'Allow, but notify customer', 'super' ),
                            'yes' => __( 'Allow', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_product_manage_stock',
                        'filter_value' => 'yes',
                    ),
                    'frontend_posting_product_sold_individually' => array(
                        'name' => __( 'Sold individually?', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_sold_individually', $settings['settings'], 'no' ),
                        'type' => 'select',
                        'values' => array(
                            'no' => __( 'No (default)', 'super' ),
                            'yes' => __( 'Yes', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_downloadable' => array(
                        'name' => __( 'Downloadable product', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_downloadable', $settings['settings'], 'no' ),
                        'type' => 'select',
                        'values' => array(
                            'no' => __( 'No (default)', 'super' ),
                            'yes' => __( 'Yes', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_virtual' => array(
                        'name' => __( 'Virtual product', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_virtual', $settings['settings'], 'no' ),
                        'type' => 'select',
                        'values' => array(
                            'no' => __( 'No (default)', 'super' ),
                            'yes' => __( 'Yes', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
                    ),
                    'frontend_posting_product_visibility' => array(
                        'name' => __( 'Product visibility', 'super' ),
                        'default' => SUPER_Settings::get_value( 0, 'frontend_posting_product_visibility', $settings['settings'], 'visible' ),
                        'type' => 'select',
                        'values' => array(
                            'visible' => __( 'Catalog & search (default)', 'super' ),
                            'catalog' => __( 'Catalog', 'super' ),
                            'search' => __( 'Search', 'super' ),
                            'hidden' => __( 'Hidden', 'super' ),
                        ),
                        'filter' => true,
                        'parent' => 'frontend_posting_post_type',
                        'filter_value' => 'product',
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