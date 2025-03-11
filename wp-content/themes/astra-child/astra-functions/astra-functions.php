<?php 
function pragma_excerpt_length($limit) {
      $excerpt = explode(' ', get_the_excerpt(), $limit);

      if (count($excerpt) >= $limit) {
          array_pop($excerpt);
          $excerpt = implode(" ", $excerpt) . '...';
      } else {
          $excerpt = implode(" ", $excerpt);
      }

      $excerpt = preg_replace('`\[[^\]]*\]`', '', $excerpt);

      return $excerpt;
}
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
/**
 * Change number of related products output
 */ 
function woo_related_products_limit() {
  global $product;
	
	$args['posts_per_page'] = 3;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args', 20 );
  function jk_related_products_args( $args ) {
	$args['posts_per_page'] = 3; // 4 related products
	$args['columns'] = 3; // arranged in 2 columns
	return $args;
}
if ( ! function_exists( 'astra_post_author' ) ) {

	/**
	 * Function to get Author of Post
	 *
	 * @param  string $output_filter Filter string.
	 * @return html                Markup.
	 */
	function astra_post_author( $output_filter = '' ) {

		ob_start();

		echo '<span ';
			echo astra_attr(
				'post-meta-author',
				array(
					'class'     => 'posted-by vcard author',
					'itemtype'  => 'https://schema.org/Person', 
					'itemscope' => 'itemscope',
					'itemprop'  => 'author',
				)
			);
		echo '>';
			// Translators: Author Name. ?>
			<a class="url fn n" title="PragmaApps" 
				href="#" rel="author" itemprop="url">
				<span class="author-avatar"><?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?></span>
				<span class="author-name" itemprop="name">PragmaApps</span>
			</a>
		</span>

		<?php

		$output = ob_get_clean();

		return apply_filters( 'astra_post_author', $output, $output_filter );
	}
}

add_filter( 'astra_default_strings', 'wpd_astra_default_strings' );
function wpd_astra_default_strings( $strings ) {
	$strings['string-blog-meta-author-by'] = ''; // removing "by" text
	
	return $strings;
}
# Move Related Posts Below Comments
/* Move related posts below comments */
add_action( 'astra_entry_before', function() {
add_filter( 'astra_get_option_enable-related-posts', '__return_false' );
} );
add_action( 'astra_primary_content_bottom', function() {
add_filter( 'astra_get_option_enable-related-posts', '__return_true' );
} );
add_action( 'astra_template_parts_content_top', 'action_to_update_related_post_markup', 1 );
function action_to_update_related_post_markup() {
$related_post_markup_instance = new Astra_Related_Posts_Markup();
if(is_singular('post')){
    add_action( 'astra_content_after', array( $related_post_markup_instance, 'astra_get_related_posts' ), 3 );
}
    
}
add_action( 'astra_content_before', 'astra_child_theme_image_before_content' );

function astra_child_theme_image_before_content() {
   if(is_singular('case_studies')){
       get_template_part( 'casestudy', 'banner' );
   }
}
add_shortcode('client_testimonials','client_testimonials');
function client_testimonials(){
	 wp_enqueue_script('testimonial-object-script');
     wp_enqueue_script('jquery-owl-carousel');
    require_once 'client-testimonials.php';
}
add_action( 'init', 'load_elementor_css');
function load_elementor_css(){
	wp_enqueue_style('elementor-icons-fa-solid');
}
?>