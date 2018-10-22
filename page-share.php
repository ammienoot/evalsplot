<?php

if ( !is_user_logged_in() ) {
	// already not logged in? go to desk.
  	wp_redirect ( home_url('/') . 'desk' );
  	exit;
  	
} elseif ( !current_user_can( 'edit_others_posts' ) ) {
	// okay user, who are you? we know you are not an admin or editor
		
	// if the collector user not found, we send you to the desk
	if ( !evalsplot_check_user() ) {
		// now go to the desk and check in properly
	  	wp_redirect ( home_url('/') . 'desk'  );
  		exit;
  	}
}

// ------------------------ defaults ------------------------

// default welcome message
$feedback_msg = evalsplot_form_default_prompt() . '. Fields marked  <strong>*</strong> are required.';
$wAuthor = 'Anonymous';
$wTitle = $wURL = $wSource = $wNotes = $wTags = $wText = '';

$wFeatureImageID = 0;			
$wCats = array( evalsplot_option('def_cat')); // preload default category
$wLicense = '--';
$all_licenses = evalsplot_get_licences();

// not yet saved
$is_published = false;
$box_style = '<div class="notify"><span class="symbol icon-info"></span> ';


// ------------------- form processing ------------------------

// verify that a form was submitted and it passes the nonce check
if ( isset( $_POST['evalsplot_form_make_submitted'] ) && wp_verify_nonce( $_POST['evalsplot_form_make_submitted'], 'evalsplot_form_make' ) ) {
 
 		// grab the variables from the form
 		$wTitle = 					sanitize_text_field( stripslashes( $_POST['wTitle'] ) );
 		$wURL = 					sanitize_text_field( stripslashes( $_POST['wURL'] ) );
 		$wAuthor = 					( isset ($_POST['wAuthor'] ) ) ? sanitize_text_field( stripslashes($_POST['wAuthor']) ) : 'Anonymous';		
 		$wTags = 					sanitize_text_field( $_POST['wTags'] );	
 		$wText = 					wp_kses_post( $_POST['wText'] );
 		$wSource = 					sanitize_text_field( $_POST['wSource']  );
 		$wNotes = 					sanitize_text_field( stripslashes( $_POST['wNotes'] ) );
 		$wCats = 					( isset ($_POST['wCats'] ) ) ? $_POST['wCats'] : array();
 		$wLicense = 				$_POST['wLicense'];
 		$wFeatureImageID = 			$_POST['wFeatureImage'];
 		if ( isset ($_POST['post_id'] ) ) $post_id = $_POST['post_id'];
 		
 		// let's do some validation, store an error message for each problem found
 		$errors = array();
 		 				
 		// let's do some validation, store an error message for each problem found
 		$errors = array();
 		
 		if ( $wTitle == '' ) $errors[] = '<strong>Web Resource Title Missing</strong> - enter an interesting title.'; 
 		
 		if ( $wURL == '' ) $errors[] = '<strong>Web Address Missing</strong> - enter the URL of the thing you are reviewing.'; 

 		if (  evalsplot_option('use_caption') == '2' AND $wText == '' ) $errors[] = '<strong>Evaluation Text</strong> - please enter a description for this evaluation.';
 
  		if (  evalsplot_option('use_source') == '2' AND $wSource == '' ) $errors[] = '<strong>Source Missing</strong> - please the name or description for the source of this image.';
  		
  		if (  evalsplot_option('use_license') == '2' AND $wLicense == '--' ) $errors[] = '<strong>License Not Selected</strong> - select an appropriate license for this evaluation.'; 
 		
 		if ( count($errors) > 0 ) {
 			// form errors, build feedback string to display the errors
 			$feedback_msg = 'Sorry, but there are a few errors in your information. Please correct and try again. We really want to add your item.<ul>';
 			
 			// Hah, each one is an oops, get it? 
 			foreach ($errors as $oops) {
 				$feedback_msg .= '<li>' . $oops . '</li>';
 			}
 			
 			$feedback_msg .= '</ul>';
 			
 			$box_style = '<div class="notify notify-red"><span class="symbol icon-error"></span> ';
 			
 		} else {
 			
 			// good enough, let's make a post! 
 			 			
			$w_information = array(
				'post_title' => $wTitle,
				'post_content' => $wText,
				'post_status' => evalsplot_option('new_item_status'),
				'post_category' => $wCats		
			);

			// insert as a new post
			$post_id = wp_insert_post( $w_information );
			
			// store the URL as post meta data
			add_post_meta($post_id, 'URL', $wURL);
			
			// store the author as post meta data
			add_post_meta( $post_id, 'shared_by', $wAuthor );
			
			// store the name of person to credit
			add_post_meta( $post_id, 'credit', $wSource );

			// store the license code
			add_post_meta( $post_id, 'license', $wLicense );

			// store extra notes
			if ( $wNotes ) add_post_meta($post_id, 'extra_notes', $wNotes);
			
			// set featured image
			set_post_thumbnail( $post_id, $wFeatureImageID);
			
			// add the tags
			wp_set_post_tags( $post_id, $wTags);
		

			if ( evalsplot_option('new_item_status') == 'publish' ) {
				// feed back for published item
				$feedback_msg = 'Your evaluation for <strong>' . $wTitle . '</strong> has been published!  You can <a href="'. get_permalink( $post_id ) . '">view it now</a>.  Or you can <a href="' . site_url()  . '">return to ' . get_bloginfo() . '</a>.';

			} else {
				// feed back for item left in draft
				$feedback_msg = 'Your evaluation for <strong>' . $wTitle . '</strong> has been submitted as a draft. Once it has been approved by a moderator, everyone can see it at <a href="' . site_url()  . '">return to ' . get_bloginfo() . '</a>.';	
			
			}		

			// logout the special user
			if ( evalsplot_check_user()=== true ) wp_logout();
			
			
			if ( evalsplot_option( 'notify' ) != '') {
			// Let's do some EMAIL!
		
				// who gets mail? They do.
				$to_recipients = explode( "," ,  evalsplot_option( 'notify' ) );
		
				$subject = 'New Item Dropped into to ' . get_bloginfo();
		
				if ( evalsplot_option('new_item_status') == 'publish' ) {
					$message = 'An evaluation <strong>"' . $wTitle . '"</strong> shared by <strong>' . $wAuthor . '</strong> has been published to ' . get_bloginfo() . '. You can <a href="'. site_url() . '/?p=' . $post_id  . '">see view it now</a>';
				

				} else {
					$message = 'An evaluation <strong>"' . $wTitle . '"</strong> shared by <strong>' . $wAuthor . '</strong> has been submitted to ' . get_bloginfo() . '. You can <a href="'. site_url() . '/?p=' . $post_id . 'preview=true' . '">preview it now</a>.<br /><br /> To  publish it, simply <a href="' . admin_url( 'edit.php?post_status=draft&post_type=post') . '">find it in the drafts</a> and change it\'s status from <strong>Draft</strong> to <strong>Publish</strong>';
				}
				
				if ( $wNotes ) $message .= '<br /><br />There are some extra notes from the author:<blockquote>' . $wNotes . '</blockquote>';
		
				// turn on HTML mail
				add_filter( 'wp_mail_content_type', 'set_html_content_type' );
		
				// mail it!
				wp_mail( $to_recipients, $subject, $message);
		
				// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
				remove_filter( 'wp_mail_content_type', 'set_html_content_type' );	
			
				}
											
			// set the gate	open, we are done.
			
			$is_published = true;
			$box_style = '<div class="notify notify-green"><span class="symbol icon-tick"></span> ';	
			
		} // count errors		
		
} // end form submmitted check
?>

<?php get_header(); ?>

<div class="wrapper">
										
	<div class="wrapper-inner section-inner thin">
	
		<div class="content">
	
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		
				<div class="post">
				
					<?php if ( has_post_thumbnail() ) : ?>
						
						<div class="featured-media">
						
							<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
							
								<?php the_post_thumbnail( 'post-image' ); ?>
								
								<?php if ( ! empty( get_post( get_post_thumbnail_id() )->post_excerpt ) ) : ?>
												
									<div class="media-caption-container">
									
										<p class="media-caption"><?php echo get_post( get_post_thumbnail_id() )->post_excerpt; ?></p>
										
									</div>
									
								<?php endif; ?>
								
							</a>
									
						</div><!-- .featured-media -->
							
					<?php endif; ?>
					
					<div class="post-inner">
					
						<div class="post-header">
													
							<?php the_title( '<h1 class="post-title">', '</h1>' ); ?>
						    				    
					    </div><!-- .post-header -->
					   				        			        		                
						<div class="post-content">
									                                        
			    	<?php the_content(); ?>
	
			    	<?php 
					if ( !is_user_logged_in() ) :?>
						<a href="<?php echo get_bloginfo('url')?>/wp-login.php?autologin=sharer">activate lasers</a>
					<?php endif?>
		    	
		    		<?php echo $box_style . $feedback_msg . '</div>';?>   
		    				
			    	<?php wp_link_pages('before=<div class="clear"></div><p class="page-links">' . __('Pages:','fukasawa') . ' &after=</p>&seperator= <span class="sep">/</span> '); ?>


	<?php if ( is_user_logged_in() and !$is_published ) : // show form if logged in and it has not been published ?>
			
		<form  id="evalsplotform" method="post" action="" enctype="multipart/form-data">
					
				<fieldset>
					<legend>Evaluation</legend>
					<label for="wTitle"><?php evalsplot_form_item_title() ?> <span class="required">*</span></label>
					<p><?php evalsplot_form_item_title_prompt() ?> </p>
					<input type="text" name="wTitle" id="wTitle" value="<?php echo $wTitle; ?>" tabindex="1" />
					
					<label for="wURL"><?php evalsplot_form_item_URL() ?> <span class="required">*</span></label>
					<p><?php evalsplot_form_item_URL_prompt() ?> </p>
					<input type="text" name="wURL" id="wURL" class="required" value="<?php echo $wURL; ?>" tabindex="2" />
				
					<?php if (  evalsplot_option('use_caption') > '0'):	
  						$required = (evalsplot_option('use_caption') == 2) ? '<span class="required">*</span>' : '';
  					?>
  				
					<label for="wText"><?php evalsplot_form_item_description() ?> <?php echo $required?></label>
					
						<p><?php evalsplot_form_item_description_prompt()?> </p>
	
						<?php if (  evalsplot_option('caption_field') == 's'):?>	
							<textarea name="wText" id="wText" rows="15"  tabindex="4"><?php echo stripslashes( $wText );?></textarea>
							
						<?php else:?>
							
						<?php
						// set up for inserting the WP post editor
						$settings = array( 'textarea_name' => 'wText', 'editor_height' => '300',  'tabindex'  => "3", 'media_buttons' => true);
						wp_editor(  stripslashes( $wText ), 'wtext', $settings );
						
						?>	
						<?php endif?>

					<?php endif?>
					
					</fieldset>
					
					<fieldset>
				    <legend>Featured Image</legend>
					<label for="headerImage"><?php evalsplot_form_item_upload() ?> <span class="required">*</span></label>
					
					<div class="uploader">
						<input id="wFeatureImage" name="wFeatureImage" type="hidden" value="<?php echo $wFeatureImageID?>" />

						<?php if ( $wFeatureImageID ):
							 echo wp_get_attachment_image( $wFeatureImageID, 'thumbnail' );
						?>
						
						<?php else:?>
						
						<img src="https://placehold.it/150x150" alt="uploaded image" id="featurethumb" />
						
						<?php endif?>
						
						<br />
					
						<input type="button" id="wFeatureImage_button"  class="btn btn-success btn-medium  upload_image_button" name="_wImage_button"  data-uploader_title="Add a New Image" data-uploader_button_text="Select Image" value="Select Image" tabindex="4" />
						
					</div>
						
						<p><?php evalsplot_form_item_upload_prompt() ?><br clear="left"></p>
 
      				<?php if (  evalsplot_option('use_source') > '0'):	
      					$required = (evalsplot_option('use_source') == 2) ? '<strong>*</strong>' : '';
      				?>
    						<label for="wSource"><?php evalsplot_form_item_image_source() ?> <?php echo $required?></label> 
    						<p><?php evalsplot_form_item_image_source_prompt() ?></p>
    						<input type="text" name="wSource" id="wSource" class="required" value="<?php echo $wSource; ?>" tabindex="5" />
    				
    				<?php endif?>
					
				</fieldset>								
					
					
					<fieldset>
					<legend>Get Organised </legend>
					<label for="wCats"><?php evalsplot_form_item_categories() ?></label>
					<p><?php evalsplot_form_item_categories_prompt() ?></p>
					<?php 
					
					// set up arguments to get all categories 
					$args = array(
						'hide_empty'               => 0,
					); 
					
					$article_cats = get_categories( $args );

					foreach ( $article_cats as $acat ) {
					
						$checked = ( in_array( $acat->term_id, $wCats) ) ? ' checked="checked"' : '';
						
						echo '<br /><input type="checkbox" name="wCats[]" tabindex="6" value="' . $acat->term_id . '"' . $checked . '> ' . $acat->name;
					}
					
					?>
					
					<label for="wTags"><?php  evalsplot_form_item_tags() ?></label>
					<p><?php  evalsplot_form_item_tags_prompt() ?></p>
					
					<input type="text" name="wTags" id="wTags" value="<?php echo $wTags; ?>" tabindex="7"  />
				</fieldset>
				

				<?php if (evalsplot_option('use_license')):?>

				<fieldset>
					<legend>Attribution / License</legend>

					<?php if (  evalsplot_option('use_license') > '0'):	
  						$required = (evalsplot_option('use_license') == 2) ? '<span class="required">*</span>' : '';
  					?>
  					
					<label for="wLicense"><?php evalsplot_form_item_license() ?> <?php echo $required?></label>
					<p><?php evalsplot_form_item_license_prompt() ?></p>
					<select name="wLicense" id="wLicense" tabindex="8" />
					<option value="--">Select a License</option>
					<?php
						foreach ($all_licenses as $key => $value) {
							$selected = ( $key == $wLicense ) ? ' selected' : '';
							echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
						}
					?>
					
					</select>
					
					<?php endif?>
					
				</fieldset>	
				<?php endif?>				

				<fieldset>
					<legend>Your Info</legend>
					<label for="wAuthor"><?php evalsplot_form_item_author()?> <span class="required">*</span></label>
					<p><?php evalsplot_form_item_author_prompt()?></p>
					<input type="text" name="wAuthor" class="required" id="wAuthor"  value="<?php echo $wAuthor; ?>" tabindex="9" />
					
					
					<label for="wNotes"><?php evalsplot_form_item_editor_notes() ?></label>						
						<p><?php evalsplot_form_item_editor_notes_prompt() ?></p>
						<textarea name="wNotes" id="wNotes" rows="10"  tabindex="10"><?php echo stripslashes($wNotes);?></textarea>

				</fieldset>	

			
				<fieldset>	
				<legend>Share This Item</legend>
				<p>Review your information, because once you click the button below, it is sent to the site.</p>
				<?php  wp_nonce_field( 'evalsplot_form_make', 'evalsplot_form_make_submitted' ); ?>
				
				<input type="submit" value="Submit Evaluation" id="makeit" name="makeit" tabindex="11">
				</fieldset>
			
						
		</form>
	<?php endif?>
																            			                        
						</div><!-- .post-content -->
						
						<div class="clear"></div>
					
					</div><!-- .post-inner -->
										
					<?php get_sidebar(); ?>
									
				</div><!-- .post -->
			
			<?php endwhile; else: ?>
			
				<p><?php _e( "We couldn't find any posts that matched your query. Please try again.", "garfunkel" ); ?></p>
		
			<?php endif; ?>
		
			<div class="clear"></div>
			
		</div><!-- .content -->
		
	</div><!-- .section-inner -->

</div><!-- .wrapper -->
								
<?php get_footer(); ?>