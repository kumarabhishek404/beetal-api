<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>
<div class="sidebar-main">
    <h4>Table of content</h4>
    <ul id="toc"></ul>
</div>
<div id="primary" class="content-area primary">
    <main id="main" class="site-main">
        <?php 
        if ( have_posts() ):
  // Yep, we have posts, so let's loop through them.
  while ( have_posts() ) : the_post();
  the_content();
  endwhile;
else :
  // No, we don't have any posts, so maybe we display a nice message
  echo "<p class='no-posts'>" . __( "Sorry, there are no posts at this time." ) . "</p>";
endif;
        ?>
    </main>
</div>
<script type="text/javascript">
jQuery( document ).ready(function() {
    jQuery("#toc").toc({content: "#main", headings: "h1,h2,h3,h4"});
});
</script>
<?php get_footer(); ?>
