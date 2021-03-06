<?php

// run when this theme is activated
add_action('after_switch_theme', 'evalsplot_setup');

function evalsplot_setup () {
  // make sure our categories are present
  
  // create pages if they do not exist
  
  if (! get_page_by_path( 'share' ) ) {
  
  	// create the Share page if it does not exist
  	$page_data = array(
  		'post_title' 	=> 'Share',
  		'post_content'	=> 'Share your evaluation, review, or reflection.',
  		'post_name'		=> 'share',
  		'post_status'	=> 'publish',
  		'post_type'		=> 'page',
  		'post_author' 	=> 1,
  		'post_date' 	=> date('Y-m-d H:i:s'),
  		'page_template'	=> 'page-collect.php',
  	);
  	
  	wp_insert_post( $page_data );
  
  }

  if (! get_page_by_path( 'desk' ) ) {

  	// create the Write page if it does not exist
  	$page_data = array(
  		'post_title' 	=> 'Welcome Desk',
  		'post_content'	=> 'Welcome to the place to add your evaluations to this collection.',
  		'post_name'		=> 'desk',
  		'post_status'	=> 'publish',
  		'post_type'		=> 'page',
  		'post_author' 	=> 1,
  		'post_date' 	=> date('Y-m-d H:i:s'),
  		'page_template'	=> 'page-desk.php',
  	);
  	
  	wp_insert_post( $page_data );
  
  }

  if (! get_page_by_path( 'random' ) ) {

  	// create the Write page if it does not exist
  	$page_data = array(
  		'post_title' 	=> 'Random',
  		'post_content'	=> '(Place holder for random page)',
  		'post_name'		=> 'random',
  		'post_status'	=> 'publish',
  		'post_type'		=> 'page',
  		'post_author' 	=> 1,
  		'post_date' 	=> date('Y-m-d H:i:s'),
  		'page_template'	=> 'page-random.php',
  	);
  	
  	wp_insert_post( $page_data );
  
  }

  if (! get_page_by_path( 'licensed' ) ) {
  
  	// create index page and archive for licenses.
  	
  	$page_data = array(
  		'post_title' 	=> 'Items by License',
  		'post_content'	=> 'Browse the items in this Evaluation SPLOT by license for reuse',
  		'post_name'		=> 'licensed',
  		'post_status'	=> 'publish',
  		'post_type'		=> 'page',
  		'post_author' 	=> 1,
  		'post_date' 	=> date('Y-m-d H:i:s', time() - 172800),
  		'page_template'	=> 'page-licensed.php',
  	);
  	
  	wp_insert_post( $page_data );
  
  }
   
}


# -----------------------------------------------------------------
# Set up the table and put the napkins out
# -----------------------------------------------------------------

// we need to load the options this before the auto login so we can use the pass
add_action( 'after_setup_theme', 'evalsplot_load_theme_options', 9 );

// change the name of admin menu items from "New Posts"
// -- h/t http://wordpress.stackexchange.com/questions/8427/change-order-of-custom-columns-for-edit-panels
// and of course the Codex http://codex.wordpress.org/Function_Reference/add_submenu_page

add_action( 'admin_menu', 'evalsplot_change_post_label' );
add_action( 'init', 'evalsplot_change_post_object' );

function evalsplot_change_post_label() {
    global $menu;
    global $submenu;
    
    $thing_name = 'Evaluation';
    
    $menu[5][0] = $thing_name . 's';
    $submenu['edit.php'][5][0] = 'All ' . $thing_name . 's';
    $submenu['edit.php'][10][0] = 'Add ' . $thing_name;
    $submenu['edit.php'][15][0] = $thing_name .' Categories';
    $submenu['edit.php'][16][0] = $thing_name .' Tags';
    echo '';
}
function evalsplot_change_post_object() {

    $thing_name = 'Evaluation';

    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name =  $thing_name . 's';;
    $labels->singular_name =  $thing_name;
    $labels->add_new = 'Add ' . $thing_name;
    $labels->add_new_item = 'Add ' . $thing_name;
    $labels->edit_item = 'Edit ' . $thing_name;
    $labels->new_item =  $thing_name;
    $labels->view_item = 'View ' . $thing_name;
    $labels->search_items = 'Search ' . $thing_name;
    $labels->not_found = 'No ' . $thing_name . ' found';
    $labels->not_found_in_trash = 'No ' .  $thing_name . ' found in Trash';
    $labels->all_items = 'All ' . $thing_name;
    $labels->menu_name =  $thing_name;
    $labels->name_admin_bar =  $thing_name;
}

add_filter('comment_form_defaults', 'evalsplot_comment_mod');

function evalsplot_comment_mod( $defaults ) {
	$defaults['title_reply'] = 'Provide Feedback';
	$defaults['logged_in_as'] = '';
	$defaults['title_reply_to'] = 'Provide Feedback for %s';
	return $defaults;
}


/* add audio post format to the mix */

add_action( 'after_setup_theme', 'evalsplot_formats', 11 );

function evalsplot_formats(){
     add_theme_support( 'post-formats', array( 'audio', 'video', 'aside', 'gallery', 'image', 'link', 'quote' ) );
}


// options for post order on front page
add_action( 'pre_get_posts', 'evalsplot_order_items' );

function evalsplot_order_items( $query ) {

	// just the main, please
	if ( $query->is_main_query() ) {

		// change sort order on home, archives, or search results
		if (  $query->is_home()  OR $query->is_archive() OR $query->is_search() ) {
	
			$query->set( 'orderby', evalsplot_option('sort_by')  );
			$query->set( 'order', evalsplot_option('sort_direction') );
		
		}
	}
}

// -----  add allowable url parameters
add_filter('query_vars', 'evalsplot_queryvars' );

function evalsplot_queryvars( $qvars ) {
	$qvars[] = 'flavor'; // flag for type of license
	
	return $qvars;
}  

 
// -----  rewrite rules for licensed pretty urls
add_action('init', 'evalsplot_rewrite_rules', 10, 0); 
      
function evalsplot_rewrite_rules() {
	$license_page = get_page_by_path('licensed');
	
	if ( $license_page ) {
		add_rewrite_rule( '^licensed/([^/]*)/?',  'index.php?page_id=' . $license_page->ID . '&flavor=$matches[1]','top');	
	}	
}


# -----------------------------------------------------------------
# Options Panel for Admin
# -----------------------------------------------------------------

// -----  Add admin menu link for Theme Options
add_action( 'wp_before_admin_bar_render', 'evalsplot_options_to_admin' );

function evalsplot_options_to_admin() {
    global $wp_admin_bar;
    
    // we can add a submenu item too
    $wp_admin_bar->add_menu( array(
        'parent' => '',
        'id' => 'evalsplot-options',
        'title' => __('Eval-SPLOT Options'),
        'href' => admin_url( 'themes.php?page=evalsplot-options')
    ) );
}


function evalsplot_enqueue_options_scripts() {
	// Set up javascript for the theme options interface
	
	// media scripts needed for wordpress media uploaders
	// wp_enqueue_media();
	
	// custom jquery for the options admin screen
	wp_register_script( 'evalsplot_options_js' , get_stylesheet_directory_uri() . '/js/jquery.evalsplot-options.js', null , '1.0', TRUE );
	wp_enqueue_script( 'evalsplot_options_js' );
	
}

function evalsplot_load_theme_options() {
	// load theme options Settings

	if ( file_exists( get_stylesheet_directory()  . '/class.evalsplot-theme-options.php' ) ) {
		include_once( get_stylesheet_directory()  . '/class.evalsplot-theme-options.php' );		
	}
}


# -----------------------------------------------------------------
# login stuff
# -----------------------------------------------------------------

// Add custom logo to entry screen... because we can
// While we are at it, use CSS to hide the back to blog and retried password links
add_action( 'login_enqueue_scripts', 'my_login_logo' );

function my_login_logo() { ?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/site-login-logo.png);
            padding-bottom: 30px;
        }    
	#backtoblog {display:none;}
	#nav {display:none;}
    </style>
<?php }


// Make logo link points to blog, not Wordpress.org Change Dat
// -- h/t http://www.sitepoint.com/design-a-stylized-custom-wordpress-login-screen/

add_filter( 'login_headerurl', 'login_link' );

function login_link( $url ) {
	return get_bloginfo( 'url' );
}
 
 
// Auto Login

function splot_redirect_url() {
	// where to send them after login ok
	return ( site_url('/') . 'share' );
}

function splot_user_login( $user_login = 'sharer' ) {
	// login the special user account to allow authoring
	
	// check for the correct user
	$autologin_user = get_user_by( 'login', $user_login ); 
	
	if ( $autologin_user ) {
	
		// just in case we have old cookies
		wp_clear_auth_cookie(); 
		
		// set the user directly
		wp_set_current_user( $autologin_user->id, $autologin_user->user_login );
		
		// new cookie
		wp_set_auth_cookie( $autologin_user->id);
		
		// do the login
		do_action( 'wp_login', $autologin_user->user_login );
		
		// send 'em on their way
		wp_redirect( splot_redirect_url() );
		
	} else {
		// uh on, problem
		die ('Bad news. Looks like there is a missing account for "' . $user_login . '".');
	}
}

// remove admin tool bar for non-admins, remove access to dashboard
// -- h/t http://www.wpbeginner.com/wp-tutorials/how-to-disable-wordpress-admin-bar-for-all-users-except-administrators/

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
	if ( !current_user_can('edit_others_posts')  ) {
	  show_admin_bar(false);
	}

}

# -----------------------------------------------------------------
# Customizer Stuff
# -----------------------------------------------------------------

add_action( 'customize_register', 'evalsplot_register_theme_customizer' );


function evalsplot_register_theme_customizer( $wp_customize ) {
	// Create custom panel.
	$wp_customize->add_panel( 'customize_collector', array(
		'priority'       => 500,
		'theme_supports' => '',
		'title'          => __( 'Eval-SPLOT', 'garfunkel'),
		'description'    => __( 'Customizer Stuff', 'garfunkel'),
	) );

	// Add section for the Share form
	$wp_customize->add_section( 'share_form' , array(
		'title'    => __('Share Form','garfunkel'),
		'panel'    => 'customize_collector',
		'priority' => 10
	) );
	
	// Add setting for default prompt
	$wp_customize->add_setting( 'default_prompt', array(
		 'default'           => __( 'Complete the form below to add a new evaluation to this collection', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Add control for default prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'default_prompt',
		    array(
		        'label'    => __( 'Default Prompt', 'garfunkel'),
		        'priority' => 10,
		        'description' => __( 'The opening message above the form.' ),
		        'section'  => 'share_form',
		        'settings' => 'default_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);
	
	// setting for title label
	$wp_customize->add_setting( 'item_title', array(
		 'default'           => __( 'Web Resource Title', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for title label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_title',
		    array(
		        'label'    => __( 'Title Label', 'garfunkel'),
		        'priority' => 11,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_title',
		        'type'     => 'text'
		    )
	    )
	);
	
	// setting for title description
	$wp_customize->add_setting( 'item_title_prompt', array(
		 'default'           => __( 'Enter the title of the web resource you are evaluating. This could be the name of the website for example.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for title description
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_title_prompt',
		    array(
		        'label'    => __( 'Title Prompt', 'garfunkel'),
		        'priority' => 12,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_title_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);
	
	// setting for URL label
	$wp_customize->add_setting( 'item_URL', array(
		 'default'           => __( 'Web Address', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for URL label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_URL',
		    array(
		        'label'    => __( 'Evaluation URL Label', 'garfunkel'),
		        'priority' => 13,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_URL',
		        'type'     => 'text'
		    )
	    )
	);
	
	// setting for URL description
	$wp_customize->add_setting( 'item_URL_prompt', array(
		 'default'           => __( 'Enter the web address of the resource that you are evaluating.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for URL description
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_URL_prompt',
		    array(
		        'label'    => __( 'Evaluation URL Prompt', 'garfunkel'),
		        'priority' => 14,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_URL_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);	
	
	// setting for description  label
	$wp_customize->add_setting( 'item_description', array(
		 'default'           => __( 'Evaluation Text', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for description  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_description', 
		    array(
		        'label'    => __( 'Evaluation Text Label', 'garfunkel'),
		        'priority' => 15,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_description',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for description label prompt
	$wp_customize->add_setting( 'item_description_prompt', array(
		 'default'           => __( 'Write your evaluation, review, or reflection of the web resource here.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for description  label prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_description_prompt',
		    array(
		        'label'    => __( 'Item Description Prompt', 'garfunkel'),
		        'priority' => 16,
		        'description' => __( 'Directions for the description entry field' ),
		        'section'  => 'share_form',
		        'settings' => 'item_description_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);	
		
	// setting for image upload label
	$wp_customize->add_setting( 'item_upload', array(
		 'default'           => __( 'Upload Image', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for image upload  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_upload',
		    array(
		        'label'    => __( 'Image Upload Label', 'garfunkel'),
		        'priority' => 17,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_upload',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for image upload prompt
	$wp_customize->add_setting( 'item_upload_prompt', array(
		 'default'           => __( 'You can upload any image file to be used in the header or choose from ones that have already been added to the site. Any uploaded image should either be your own or one licensed for re-use; provide an attribution credit for the image in the caption field below.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for image upload prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_upload_prompt',
		    array(
		        'label'    => __( 'Image Upload Prompt', 'garfunkel'),
		        'priority' => 18,
		        'description' => __( 'Directions for image uploads' ),
		        'section'  => 'share_form',
		        'settings' => 'item_upload_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);

	// setting for image source  label
	$wp_customize->add_setting( 'item_image_source', array(
		 'default'           => __( 'Source of Image', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for image source  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_media_source',
		    array(
		        'label'    => __( 'Image Source Label', 'garfunkel'),
		        'priority' => 19,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_image_source',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for image source  prompt
	$wp_customize->add_setting( 'item_image_source_prompt', array(
		 'default'           => __( 'Enter a name of a person, publisher, organization, web site, etc to give credit for this item. Include info about licensing too.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for image source prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_media_source_prompt',
		    array(
		        'label'    => __( 'Image Source Prompt', 'garfunkel'),
		        'priority' => 20,
		        'description' => __( 'Directions for the image source field' ),
		        'section'  => 'share_form',
		        'settings' => 'item_image_source_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);

	// setting for license  label
	$wp_customize->add_setting( 'item_license', array(
		 'default'           => __( 'Choose a License', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for license  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_license',
		    array(
		        'label'    => __( 'License Label', 'garfunkel'),
		        'priority' => 21,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_license',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for license  prompt
	$wp_customize->add_setting( 'item_license_prompt', array(
		 'default'           => __( 'Choose your preferred license. If this is your original piece of content, then select a license you wish to attach to it.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for license prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_license_prompt',
		    array(
		        'label'    => __( 'License Prompt', 'garfunkel'),
		        'priority' => 22,
		        'description' => __( 'Directions for the license selection' ),
		        'section'  => 'share_form',
		        'settings' => 'item_license_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);

	// setting for categories  label
	$wp_customize->add_setting( 'item_categories', array(
		 'default'           => __( 'Categories', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for categories  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_categories',
		    array(
		        'label'    => __( 'Categories Label', 'garfunkel'),
		        'priority' => 23,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_categories',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for categories  prompt
	$wp_customize->add_setting( 'item_categories_prompt', array(
		 'default'           => __( 'Check all categories that will help organize this item.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for categories prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_categories_prompt',
		    array(
		        'label'    => __( 'Categories Prompt', 'garfunkel'),
		        'priority' => 24,
		        'description' => __( 'Directions for the categories selection' ),
		        'section'  => 'share_form',
		        'settings' => 'item_categories_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);
	
	// setting for tags  label
	$wp_customize->add_setting( 'item_tags', array(
		 'default'           => __( 'Tags', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for tags  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_tags',
		    array(
		        'label'    => __( 'Tags Label', 'garfunkel'),
		        'priority' => 25,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_tags',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for tags  prompt
	$wp_customize->add_setting( 'item_tags_prompt', array(
		 'default'           => __( 'Descriptive tags, separate multiple ones with commas', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for tags prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_tags_prompt',
		    array(
		        'label'    => __( 'Tags Prompt', 'garfunkel'),
		        'priority' => 26,
		        'description' => __( 'Directions for tags entry' ),
		        'section'  => 'share_form',
		        'settings' => 'item_tags_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);

	// setting for author  label
	$wp_customize->add_setting( 'item_author', array(
		 'default'           => __( 'Your Info', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for author  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_author',
		    array(
		        'label'    => __( 'Author Label', 'garfunkel'),
		        'priority' => 27,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_author',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for author  label prompt
	$wp_customize->add_setting( 'item_author_prompt', array(
		 'default'           => __( 'Take credit for sharing this item by entering your name, twitter handle, secret agent name, or remain "Anonymous"', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for author  label prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_author_prompt',
		    array(
		        'label'    => __( 'Item Author Prompt', 'garfunkel'),
		        'priority' => 28,
		        'description' => __( 'Directions for the author/uploader credit' ),
		        'section'  => 'share_form',
		        'settings' => 'item_author_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);

	// setting for editor notes  label
	$wp_customize->add_setting( 'item_editor_notes', array(
		 'default'           => __( 'Notes to the Editor', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for editor notes  label
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_editor_notes',
		    array(
		        'label'    => __( 'Editor Notes Label', 'garfunkel'),
		        'priority' => 29,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_editor_notes',
		        'type'     => 'text'
		    )
	    )
	);

	// setting for editor notes  prompt
	$wp_customize->add_setting( 'item_editor_notes_prompt', array(
		 'default'           => __( 'Add any notes or messages to the site manager; this will not be part of what is published. If you wish to be contacted, leave an email address or twitter handle below. Otherwise you are completely anonymous.', 'garfunkel'),
		 'type' => 'theme_mod',
		 'sanitize_callback' => 'sanitize_text'
	) );
	
	// Control for editor notes prompt
	$wp_customize->add_control( new WP_Customize_Control(
	    $wp_customize,
		'item_editor_notes_prompt',
		    array(
		        'label'    => __( 'Editor Notes Prompt', 'garfunkel'),
		        'priority' => 30,
		        'description' => __( '' ),
		        'section'  => 'share_form',
		        'settings' => 'item_editor_notes_prompt',
		        'type'     => 'textarea'
		    )
	    )
	);
			
 	// Sanitize text
	function sanitize_text( $text ) {
	    return sanitize_text_field( $text );
	}
}

function evalsplot_form_default_prompt() {
	 if ( get_theme_mod( 'default_prompt') != "" ) {
	 	return get_theme_mod( 'default_prompt');
	 }	else {
	 	return 'Complete the form below to add a new evaluation to this collection';
	 }
}

function evalsplot_form_item_title() {
	 if ( get_theme_mod( 'item_title') != "" ) {
	 	echo get_theme_mod( 'item_title');
	 }	else {
	 	echo 'Web Resource Title';
	 }
}

function evalsplot_form_item_title_prompt() {
	 if ( get_theme_mod( 'item_title_prompt') != "" ) {
	 	echo get_theme_mod( 'item_title_prompt');
	 }	else {
	 	echo 'Enter the title of the web resource you are evaluating. This could be the name of the website for example.';
	 }
}

function evalsplot_form_item_URL() {
	 if ( get_theme_mod( 'item_URL') != "" ) {
	 	echo get_theme_mod( 'item_URL');
	 }	else {
	 	echo 'Web Address';
	 }
}

function evalsplot_form_item_URL_prompt() {
	 if ( get_theme_mod( 'item_URL_prompt') != "" ) {
	 	echo get_theme_mod( 'item_URL_prompt');
	 }	else {
	 	echo 'Enter the URL of the web resource that you are evaluating.';
	 }
}

function evalsplot_form_item_description() {
	 if ( get_theme_mod( 'item_description') != "" ) {
	 	echo get_theme_mod( 'item_description');
	 }	else {
	 	echo 'Evaluation Text';
	 }
}

function evalsplot_form_item_description_prompt() {
	 if ( get_theme_mod( 'item_description_prompt') != "" ) {
	 	echo get_theme_mod( 'item_description_prompt');
	 }	else {
	 	echo 'Write your evaluation, review, or reflection of the web resource here.';
	 }
}

function evalsplot_form_item_upload() {
	 if ( get_theme_mod( 'item_upload') != "" ) {
	 	echo get_theme_mod( 'item_upload');
	 }	else {
	 	echo 'Upload Image';
	 }
}

function evalsplot_form_item_upload_prompt() {
	 if ( get_theme_mod( 'item_upload_prompt') != "" ) {
	 	echo get_theme_mod( 'item_upload_prompt');
	 }	else {
	 	echo 'You can upload any image file to be used in the header or choose from ones that have already been added to the site. Any uploaded image should either be your own or one licensed for re-use; provide an attribution credit for the image in the caption field below.';
	 }
}

function evalsplot_form_item_image_source() {
	 if ( get_theme_mod( 'item_image_source') != "" ) {
	 	echo get_theme_mod( 'item_image_source');
	 }	else {
	 	echo 'Source of Featured Image';
	 }
}

function evalsplot_form_item_image_source_prompt() {
	 if ( get_theme_mod( 'item_image_source_prompt') != "" ) {
	 	echo get_theme_mod( 'item_image_source_prompt');
	 }	else {
	 	echo 'Enter name of a person, web site, etc to give credit for the image submitted above. Include info about licensing too.';
	 }
}

function evalsplot_form_item_categories() {
	 if ( get_theme_mod( 'item_categories') != "" ) {
	 	echo get_theme_mod( 'item_categories');
	 }	else {
	 	echo 'Categories';
	 }
}

function evalsplot_form_item_categories_prompt() {
	 if ( get_theme_mod( 'item_categories_prompt') != "" ) {
	 	echo get_theme_mod( 'item_categories_prompt');
	 }	else {
	 	echo 'Check all categories that will help organize this evaluation.';
	 }
}

function evalsplot_form_item_tags() {
	 if ( get_theme_mod( 'item_tags') != "" ) {
	 	echo get_theme_mod( 'item_tags');
	 }	else {
	 	echo 'Tags';
	 }
}

function evalsplot_form_item_tags_prompt() {
	 if ( get_theme_mod( 'item_tags_prompt') != "" ) {
	 	echo get_theme_mod( 'item_tags_prompt');
	 }	else {
	 	echo 'Descriptive tags, separate multiple ones with commas';
	 }
}

function evalsplot_form_item_license() {
	 if ( get_theme_mod( 'item_license') != "" ) {
	 	echo get_theme_mod( 'item_license');
	 }	else {
	 	echo 'Reuse License';
	 }
}

function evalsplot_form_item_license_prompt() {
	 if ( get_theme_mod( 'item_license_prompt') != "" ) {
	 	echo get_theme_mod( 'item_license_prompt');
	 }	else {
	 	echo 'Choose your preferred license. If this is your original piece of content, then select a license you wish to attach to it.';
	 }
}

function evalsplot_form_item_author() {
	 if ( get_theme_mod( 'item_author') != "" ) {
	 	echo get_theme_mod( 'item_author');
	 }	else {
	 	echo 'Your Info';
	 }
}

function evalsplot_form_item_author_prompt() {
	 if ( get_theme_mod( 'item_author_prompt') != "" ) {
	 	echo get_theme_mod( 'item_author_prompt');
	 }	else {
	 	echo 'Take credit for sharing this item by entering your name, twitter handle, secret agent name, or remain "Anonymous"';
	 }
}

function evalsplot_form_item_editor_notes() {
	 if ( get_theme_mod( 'item_editor_notes') != "" ) {
	 	echo get_theme_mod( 'item_editor_notes');
	 }	else {
	 	echo 'Notes to the Editor';
	 }
}

function evalsplot_form_item_editor_notes_prompt() {
	 if ( get_theme_mod( 'item_editor_notes_prompt') != "" ) {
	 	echo get_theme_mod( 'item_editor_notes_prompt');
	 }	else {
	 	echo 'Add any notes or messages to send to the site manager; this will not be part of what is published. If you wish to be contacted, leave an email address or twitter handle.';
	 }
}

# -----------------------------------------------------------------
# Licensed to License
# -----------------------------------------------------------------

function evalsplot_license_html ($license, $author='', $yr='') {
	// outputs the proper license
	// $license is abbreviation. author is from post metadata, yr is from post date
	
	if ( !isset( $license ) or $license == '' ) return '';
	
	if ($license == 'c') {
		// boo copyrighted! sigh, slap on the copyright text. Blarg.
		return 'This work by ' . $author . ' is &copy;' . $yr . ' All Rights Reserved';
	} 
	
	if ($license == 'u') {
		// Unspecified / Unknown license.
		return 'This work by ' . $author . ' has no license specified.';
	} 
	
	if ($license == 'pd') {
		// Public Domain license - slap the CC PD logo on.
		return '<img alt="public domain license" style="border-width:0" src="https://licensebuttons.net/l/publicdomain/88x31.png"> This work by ' . $author . ' is in the Public Domain.';	
	} 
	
	// names of all licenses
	$commons = array (
				'zero'	=> 'CC0 No Rights Reserved',
				'by' => 'Attribution',
				'by-sa' => 'Attribution-ShareAlike',
				'by-nd' => 'Attribution-NoDerivs',
				'by-nc' => 'Attribution-NonCommercial',
				'by-nc-sa' => 'Attribution-NonCommercial-ShareAlike',
				'by-nc-nd' => 'Attribution-NonCommercial-NoDerivs',
	);
		
	if ($license == 'zero') {
		// CC0 has a different image path. Aaaargh.
		return '<a rel="license" href="http://creativecommons.org/licenses/' . $license . '/1.0/"><img alt="Creative Commons License" style="border-width:0" src="https://licensebuttons.net/l/' . $license . '/1.0/88x31.png" /></a><br />This work' . $credit . ' is licensed under a <a rel="license" href="http://creativecommons.org/licenses/' . $license . '/1.0/">Creative Commons ' . $commons[$license] . ' 1.0 International License</a>.';	
	} 
	
	// do we have an author?
	$credit = ($author == '') ? '' : ' by ' . $author;
	
	return '<a rel="license" href="http://creativecommons.org/licenses/' . $license . '/4.0/"><img alt="Creative Commons License" style="border-width:0" src="https://licensebuttons.net/l/' . $license . '/4.0/88x31.png" /></a><br />This work' . $credit . ' is licensed under a <a rel="license" href="http://creativecommons.org/licenses/' . $license . '/4.0/">Creative Commons ' . $commons[$license] . ' 4.0 International License</a>.';            
}


function evalsplot_get_licences() {
	// return as an array the types of licenses available 
	
	return ( array (
				'c' => 'All Rights Reserved (copyrighted)',
				'u' => 'Unknown / Not Specified',
				'pd'	=> 'Public Domain',
				'zero'	=> 'CC0 No Rights Reserved',
				'by' => 'CC BY Creative Commons By Attribution',
				'by-sa' => 'CC BY SA Creative Commons Attribution-ShareAlike',
				'by-nd' => 'CC BY ND Creative Commons Attribution-NoDerivs',
				'by-nc' => 'CC BY NC Creative Commons Attribution-NonCommercial',
				'by-nc-sa' => 'CC BY NC SA Creative Commons Attribution-NonCommercial-ShareAlike',
				'by-nc-nd' => 'CC By NC ND Creative Commons Attribution-NonCommercial-NoDerivs',
			)
		);
}


function evalsplot_the_license( $lcode ) {
	// output the title of a license
	$all_licenses = evalsplot_get_licences();
	
	echo $all_licenses[$lcode];
}


function evalsplot_attributor( $license, $work_title, $work_link, $work_creator='') {

	$all_licenses = evalsplot_get_licences();
		
	$work_str = ( $work_creator == '') ? '"' . $work_title . '"' : '"' . $work_title . '" by ' . $work_creator;
	
	$work_str_html = ( $work_creator == '') ? '<a href="' . $work_link .'">"' . $work_title . '"</a>' : '<a href="' . $work_link .'">"' . $work_title . '"</a> by ' . $work_creator;
	
	switch ( $license ) {

		case 'c': 	
			return ( array( 
						$work_str .  ' is &copy; All Rights Reserved.', 
						$work_str_html . ' is &copy; All Rights Reserved.'
					)
			 );
			break;


		case 'u': 	
			return ( array( 
						'The rights of ' . $work_str .  ' is unknown or not specified.', 
						'The rights of ' . $work_str_html . ' is unknown or not specified.'
					)
			 );
			break;

		case 'yt': 	
			return ( array( 
						$work_str .  ' is covered by a YouTube Standard License.', 
						$work_str_html . ' is covered by a <a href="https://www.youtube.com/t/terms">YouTube Standard License</a>.' 
					)
			 );
			break;
		
		case 'cc0':
			return ( array( 
						$work_str . ' is made available under the Creative Commons CC0 1.0 Universal Public Domain Dedication.',
						$work_str_html .  ' is made available under the <a href="https://creativecommons.org/publicdomain/zero/1.0/">Creative Commons CC0 1.0 Universal Public Domain Dedication</a>.'	
					)
			 );
		
			break;
	
		case 'pd':
			return ( array( 
				$work_str . ' has been explicitly released into the public domain.',
				$work_str_html . ' has been explicitly released into the public domain.'
				)
			 );
			break;
		
		default:
			//find position in license where name of license starts
			$lstrx = strpos( $all_licenses[$license] , 'Creative Commons');
		
			return ( array( 
					$work_str . ' is licensed under a ' .  substr( $all_licenses[$license] , $lstrx)  . ' 4.0 International license.',
					$work_str_html . ' is licensed under a <a href="https://creativecommons.org/licenses/' . $license . '/4.0/">' .  substr( $all_licenses[$license] , $lstrx)  . ' 4.0 International</a> license.'		
				)
			 );
	}
}

function evalsplot_get_license_count( $the_license ) {


	$lic_query = new WP_Query( array( 'post_status' => 'publish', 'meta_key' => 'license', 'meta_value' =>  $the_license ) );

   return $lic_query->found_posts;

}

# -----------------------------------------------------------------
# Enqueue Scipts and Styles
# -----------------------------------------------------------------


add_action('wp_enqueue_scripts', 'add_evalsplot_scripts');

function add_evalsplot_scripts() {	 
    $parent_style = 'garfunkel_style'; 
    
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );

 	// use these scripts just our form page
 	if ( is_page('share') ) { 
    
		 // add media scripts if we are on our maker page and not an admin
		 // after http://wordpress.stackexchange.com/a/116489/14945
    	 
		if (! is_admin() ) wp_enqueue_media();
		
		// Build in tag auto complete script
   		wp_enqueue_script( 'suggest' );

		// custom jquery for the uploader on the form
		wp_register_script( 'jquery.evalsplot' , get_stylesheet_directory_uri() . '/js/jquery.evalsplot.js', null , '1.0', TRUE );
		wp_enqueue_script( 'jquery.evalsplot' );
		
	}

}

# -----------------------------------------------------------------
# Menu Setup
# -----------------------------------------------------------------

// checks to see if a menu location is used.
function splot_is_menu_location_used( $location = 'primary' ) {	

	// get locations of all menus
	$menulocations = get_nav_menu_locations();
	
	// get all nav menus
	$navmenus = wp_get_nav_menus();
	
	
	// if either is empty we have no menus to use
	if ( empty( $menulocations ) OR empty( $navmenus ) ) return false;
	
	// othewise look for the menu location in the list
	return in_array( $location , $menulocations);
}

// create a basic menu if one has not been define for primary
function splot_default_menu() {

	// site home with trailing slash
	$splot_home = site_url('/');
  
 	return ( '<li><a href="' . $splot_home . '">Home</a></li><li><a href="' . $splot_home . 'share' . '">Share</a></li><li><a href="' . $splot_home . 'random' . '">Random</a></li>' );
  
}


# -----------------------------------------------------------------
# Audio and Video management
# -----------------------------------------------------------------

function evalsplot_get_audioplayer( $url ) {
	// output the  audio player
	
	$audioplayer = '
<audio controls="controls" class="audio-player">
	<source src="' . $url . '" />
</audio>' . "\n";
	return ($audioplayer);
}

function evalsplot_get_videoplayer( $url ) {
	// output the  video player
	
	if ( is_in_url( 'archive.org', $url ) ) {
	
		$archiveorg_url = str_replace ( 'details' , 'embed' , $url );
	
		$videoplayer = '<iframe src="' . $archiveorg_url . '" width="640" height="480" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen></iframe>';
	
	} else {
	
		$videoplayer = '
<video controls="controls" class="video-player">
	<source src="' . $url . '" type="video/mp4" />
</video>' . "\n";

	}
	
	return ($videoplayer);
}


function url_is_media_type ( $url ) {

	// check for video
 	if ( url_is_video ( $url ) ) return 'video';
	// check  for audio
	if ( url_is_audio ( $url ) ) return 'audio';

}



function url_is_audio ( $url ) {
// tests urls to see if they point to an audio type

	if ( is_in_url( 'soundcloud.com', $url ) or url_is_audio_link( $url ) ) return true;
	
}

function url_is_audio_link ( $url ) {

	$fileExtention 	= pathinfo ( $url, PATHINFO_EXTENSION ); 	// get file extension for url	
	$allowables 	= 	array( 'mp3', 'm4a', 'ogg'); 	// allowable file extensions
	
	// check the url file extension to ones we will allow
	return ( in_array( strtolower( $fileExtention) ,  $allowables  ) );
}

function url_is_video_link ( $url ) {

	$fileExtention 	= pathinfo ( $url, PATHINFO_EXTENSION ); 	// get file extension for url	
	$allowables 	= 	array( 'mp4'); 	// allowable file extensions
	
	// check the url file extension to ones we will allow
	return ( in_array( strtolower( $fileExtention) ,  $allowables  ) );
}



// check if $url contacts a string (like domain name) 
function is_in_url ( $pattern, $url ) {

	if ( strpos( $url, $pattern) === false ) {
		return (false);
	} else {
		return (true);
	}
}

function url_is_video ( $url ) {

	$allowables = array(
					'youtube.com/watch?',
					'youtu.be',
					'vimeo.com',
					'archive.org'
	);

	// walk the array til we get a match
	foreach( $allowables as $fragment ) {
  		if  (strpos( $url, $fragment ) !== false ) {
			return ( true );
		}
	}	
	
	// see if it is a link to a valid video format
	if  ( url_is_video_link ( $url ) ) return true;
	
	// no matches, not a video for you
	return ( false );
}

function is_url_embeddable( $url ) {
// test if URL matches the ones that Wordpress can do oembed on
// test by by string matching
	
	$allowed_embeds = array(
					'youtube.com/watch?',
					'youtu.be',
					'vimeo.com', 
					'soundcloud.com',
	);
	
	// walk the array til we get a match
	foreach( $allowed_embeds as $fragment ) {
  		if  (strpos( $url, $fragment ) !== false ) {
			return ( true );
		}
	}	
	
	// no matches, no embeds for you
	return ( false );
}

# -----------------------------------------------------------------
# Useful spanners and wrenches
# -----------------------------------------------------------------



// return the maxium upload file size in omething more useful than bytes
function evalsplot_max_upload() {

	$maxupload = wp_max_upload_size() / 1000000;
	
	return ( round( $maxupload ) . ' Mb');


}

								
// function to get the caption for an attachment (stored as post_excerpt)
// -- h/t http://wordpress.stackexchange.com/a/73894/14945
function get_attachment_caption_by_id( $post_id ) {
    $the_attachment = get_post( $post_id );
    return ( $the_attachment->post_excerpt ); 
}


function evalsplot_author_user_check( $expected_user = 'sharer' ) {
// checks for the proper authoring account set up

	$auser = get_user_by( 'login', $expected_user );
		
	
	if ( !$auser) {
		return ('Authoring account not set up. You need to <a href="' . admin_url( 'user-new.php') . '">create a user account</a> with login name <strong>' . $expected_user . '</strong> with a role of <strong>Author</strong>. Make a killer strong password; no one uses it.');
	} elseif ( $auser->roles[0] != 'author') {
	
		// for multisite lets check if user is not member of blog
		if ( is_multisite() AND !is_user_member_of_blog( $auser->ID, get_current_blog_id() ) )  {
			return ('The user account <strong>' . $expected_user . '</strong> is set up but has not been added as a user to this site (and needs to have a role of <strong>Author</strong>). You can <a href="' . admin_url( 'user-edit.php?user_id=' . $auser->ID ) . '">edit it now</a>'); 
			
		} else {
		
			return ('The user account <strong>' . $expected_user . '</strong> is set up but needs to have it\'s role set to <strong>Author</strong>. You can <a href="' . admin_url( 'user-edit.php?user_id=' . $auser->ID ) . '">edit it now</a>'); 
		}
		
		
		
	} else {
		return ('The authoring account <strong>' . $expected_user . '</strong> is correctly set up.');
	}
}


function evalsplot_check_user( $allowed='sharer' ) {
	// checks if the current logged in user is who we expect
    
   $current_user = wp_get_current_user();
	
	// return check of match
	return ( $current_user->user_login == $allowed );
}

function splot_the_author() {
	// utility to put in template to show status of special logins
	// nothing is printed if there is not current user, 
	//   echos (1) if logged in user is the special account
	//   echos (0) if logged in user is the another account
	//   in both cases the code is linked to a logout script

	if ( is_user_logged_in() and !current_user_can( 'edit_others_posts' ) ) {
		$user_code = ( evalsplot_check_user() ) ? 1 : 0;
		echo '<a href="' . wp_logout_url( site_url() ). '">(' . $user_code  .')</a>';
	}

}

function set_html_content_type() {
	// from http://codex.wordpress.org/Function_Reference/wp_mail
	return 'text/html';
}

function br2nl ( $string )
// from http://php.net/manual/en/function.nl2br.php#115182
{
    return preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $string);
}

function make_links_clickable( $text ) {
//----	h/t http://stackoverflow.com/a/5341330/2418186
    return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text);
}

?>