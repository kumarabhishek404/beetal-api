<?php
/**
 * Encapsulate breadcrumb functions in class
 *
 * @since 4.8.2
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'avia_breadcrumb_trail' ) )
{

	class avia_breadcrumb_trail
	{
		/**
		 *
		 * @since 4.8.2
		 * @var avia_breadcrumb_trail
		 */
		static protected $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8.2
		 * @return avia_breadcrumb_trail
		 */
		static public function instance()
		{
			if( is_null( avia_breadcrumb_trail::$_instance ) )
			{
				avia_breadcrumb_trail::$_instance = new avia_breadcrumb_trail();
			}

			return avia_breadcrumb_trail::$_instance;
		}


		/**
		 * Custom breadcrumb generator function
		 * ====================================
		 * The code below is an inspired/modified version by woothemes breadcrumb function which in turn is inspired by Justin Tadlock's Hybrid Core :)
		 *
		 * Arguments Array:
		 * ================
		 *
		 * 'separator' 			- The character to display between the breadcrumbs.
		 * 'before' 			- HTML to display before the breadcrumbs.
		 * 'after' 				- HTML to display after the breadcrumbs.
		 * 'front_page' 		- Include the front page at the beginning of the breadcrumbs.
		 * 'show_home' 			- If $show_home is set and we're not on the front page of the site, link to the home page.
		 * 'echo' 				- Specify whether or not to echo the breadcrumbs. Alternative is 'return'.
		 * 'show_posts_page'	- If a static front page is set and there is a posts page, toggle whether or not to display that page's tree.
		 *
		 * @param array $args
		 * @return string
		 */
		public function get_trail( $args = array() )
		{
			global $wp_query, $wp_rewrite;

			/**
			 * Allow to shortcut breadcrumb trail. Return anything then false to shortcut.
			 *
			 * @since 4.8
			 * @param boolean $value
			 * @param array $args
			 * @return string|false
			 */
			$breadcrumb_external = apply_filters( 'avf_breadcrumbs_external', false, $args );
			if( false !== $breadcrumb_external )
			{
				return $breadcrumb_external;
			}

			/* Create an empty variable for the breadcrumb. */
			$breadcrumb = '';

			/* Create an empty array for the trail. */
			$trail = array();
			$path = '';

			/* Set up the default arguments for the breadcrumb. */
			$defaults = array(
							'separator'			=> '&raquo;',
							'before'			=> '<span class="breadcrumb-title">' . __( 'You are here:', 'avia_framework' ) . '</span>',
							'after'				=> false,
							'front_page'		=> true,
							'show_home'			=> __( 'Home', 'avia_framework' ),
							'echo'				=> false,
							'show_categories'	=> true,
							'show_posts_page'	=> true,
							'truncate'			=> 70,
							'richsnippet'		=> false
						);


			/* Allow singular post views to have a taxonomy's terms prefixing the trail. */
			if( is_singular() )
			{
				$defaults[ "singular_{$wp_query->post->post_type}_taxonomy" ] = false;
			}

			/* Apply filters to the arguments. */
			$args = apply_filters( 'avia_breadcrumbs_args', $args );

			/* Parse the arguments and extract them for easy variable naming. */
			extract( wp_parse_args( $args, $defaults ) );

			/* If $show_home is set and we're not on the front page of the site, link to the home page. */
			if( ! is_front_page() && $show_home )
			{
				$trail[] = '<a href="' . home_url() . '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" rel="home" class="trail-begin">' . $show_home . '</a>';
			}

			/* If viewing the front page of the site. */
			if( is_front_page() )
			{
				if( ! $front_page )
				{
					$trail = false;
				}
				else if( $show_home )
				{
					$trail['trail_end'] = "{$show_home}";
				}
			}

			/* If viewing the "home"/posts page. */
			else if( is_home() )
			{
				$home_page = get_post( $wp_query->get_queried_object_id() );
				$trail = array_merge( $trail, $this->get_parents( $home_page->post_parent, '' ) );
				$trail['trail_end'] = get_the_title( $home_page->ID );
			}

			/* If viewing a singular post (page, attachment, etc.). */
			else if( is_singular() )
			{
				/* Get singular post variables needed. */
				$post = $wp_query->get_queried_object();
				$post_id = absint( $wp_query->get_queried_object_id() );
				$post_type = $post->post_type;
				$parent = $post->post_parent;

				/* If a custom post type, check if there are any pages in its hierarchy based on the slug. */
				if( 'page' !== $post_type && 'post' !== $post_type )
				{
					$post_type_object = get_post_type_object( $post_type );

					/* If $front has been set, add it to the $path. */
					if( 'post' == $post_type || 'attachment' == $post_type || ( is_array( $post_type_object->rewrite ) && isset( $post_type_object->rewrite['with_front'] ) && $post_type_object->rewrite['with_front'] && $wp_rewrite->front ) )
					{
						$path .= trailingslashit( $wp_rewrite->front );
					}

					/* If there's a slug, add it to the $path. */
					if( ! empty( $post_type_object->rewrite['slug'] ) )
					{
						$path .= $post_type_object->rewrite['slug'];
					}

					/* If there's a path, check for parents. */
					if( ! empty( $path ) )
					{
						$trail = array_merge( $trail, $this->get_parents( '', $path ) );
					}

					/* If there's an archive page, add it to the trail. */
					if( ! empty( $post_type_object->has_archive ) && function_exists( 'get_post_type_archive_link' ) )
					{
						$trail[] = '<a href="' . get_post_type_archive_link( $post_type ) . '" title="' . esc_attr( $post_type_object->labels->name ) . '">' . $post_type_object->labels->name . '</a>';
					}
				}

				/* try to build a generic taxonomy trail no matter the post type and taxonomy and terms
				$currentTax = '';
				foreach( get_taxonomies() as $tax )
				{
					$terms = get_the_term_list( $post_id, $tax, '', '$$$', '' );
					echo '<pre>';
					print_r( $tax.$terms );
					echo '</pre>';
				}
				*/

				/* If the post type path returns nothing and there is a parent, get its parents. */
				if( empty( $path ) && 0 !== $parent || 'attachment' == $post_type )
				{
					$trail = array_merge( $trail, $this->get_parents( $parent, '' ) );
				}

				/* Toggle the display of the posts page on single blog posts. */
				if( 'post' == $post_type && $show_posts_page == true && 'page' == get_option( 'show_on_front' ) )
				{
					$posts_page = get_option( 'page_for_posts' );
					if( $posts_page != '' && is_numeric( $posts_page ) )
					{
						$trail = array_merge( $trail, $this->get_parents( $posts_page, '' ) );
					}
				}

				if( 'post' == $post_type && $show_categories )
				{
						$category = get_the_category();

						foreach( $category as $cat )
						{
							if( ! empty( $cat->parent ) )
							{
								$parents = get_category_parents( $cat->cat_ID, true, '$$$', false );
								$parents = explode( '$$$', $parents );

								foreach( $parents as $parent_item )
								{
									if( $parent_item )
									{
										$trail[] = $parent_item;
									}
								}
								break;
							}
						}

						if( isset( $category[0] ) && empty( $parents ) )
						{
							$trail[] = '<a href="' . get_category_link( $category[0]->term_id ) . '">' . $category[0]->cat_name . '</a>';
						}
				}

				if( $post_type == 'portfolio' )
				{
					$parents = get_the_term_list( $post_id, 'portfolio_entries', '', '$$$', '' );
					$parents = explode( '$$$', $parents );

					foreach( $parents as $parent_item )
					{
						if( $parent_item )
						{
							$trail[] = $parent_item;
						}
					}
				}

				/* Display terms for specific post type taxonomy if requested. */
				if( isset( $args["singular_{$post_type}_taxonomy"] ) && $terms = get_the_term_list( $post_id, $args["singular_{$post_type}_taxonomy"], '', ', ', '' ) )
				{
					$trail[] = $terms;
				}

				/* End with the post title. */
				$post_title = get_the_title( $post_id ); // Force the post_id to make sure we get the correct page title.
				if( ! empty( $post_title ) )
				{
					$trail['trail_end'] = $post_title;
				}
			}

			/* If we're viewing any type of archive. */
			else if( is_archive() )
			{
				/* If viewing a taxonomy term archive. */
				if( is_tax() || is_category() || is_tag() )
				{
					/* Get some taxonomy and term variables. */
					$term = $wp_query->get_queried_object();
					$taxonomy = get_taxonomy( $term->taxonomy );

					/* Get the path to the term archive. Use this to determine if a page is present with it. */
					if( is_category() )
					{
						$path = get_option( 'category_base' );
					}
					else if( is_tag() )
					{
						$path = get_option( 'tag_base' );
					}
					else
					{
						if( $taxonomy->rewrite['with_front'] && $wp_rewrite->front )
						{
							$path = trailingslashit( $wp_rewrite->front );
						}
						$path .= $taxonomy->rewrite['slug'];
					}

					/* Get parent pages by path if they exist. */
					if( $path )
					{
						$trail = array_merge( $trail, $this->get_parents( '', $path ) );
					}

					/* If the taxonomy is hierarchical, list its parent terms. */
					if( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent )
					{
						$trail = array_merge( $trail, $this->get_term_parents( $term->parent, $term->taxonomy ) );
					}

					/* Add the term name to the trail end. */
					$trail['trail_end'] = $term->name;
				}

				/* If viewing a post type archive. */
				else if( function_exists( 'is_post_type_archive' ) && is_post_type_archive() )
				{
					/* Get the post type object. */
					$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );

					/* If $front has been set, add it to the $path. */
					if( $post_type_object->rewrite['with_front'] && $wp_rewrite->front )
					{
						$path .= trailingslashit( $wp_rewrite->front );
					}

					/* If there's a slug, add it to the $path. */
					if( !empty( $post_type_object->rewrite['archive'] ) )
					{
						$path .= $post_type_object->rewrite['archive'];
					}

					/* If there's a path, check for parents. */
					if( !empty( $path ) )
					{
						$trail = array_merge( $trail, $this->get_parents( '', $path ) );
					}

					/* Add the post type [plural] name to the trail end. */
					$trail['trail_end'] = $post_type_object->labels->name;
				}

				/* If viewing an author archive. */
				else if( is_author() )
				{
					/* If $front has been set, add it to $path. */
					if( ! empty( $wp_rewrite->front ) )
					{
						$path .= trailingslashit( $wp_rewrite->front );
					}

					/* If an $author_base exists, add it to $path. */
					if( ! empty( $wp_rewrite->author_base ) )
					{
						$path .= $wp_rewrite->author_base;
					}

					/* If $path exists, check for parent pages. */
					if( ! empty( $path ) )
					{
						$trail = array_merge( $trail, $this->get_parents( '', $path ) );
					}

					/* Add the author's display name to the trail end. */
					$trail['trail_end'] = apply_filters( 'avf_author_name', get_the_author_meta( 'display_name', get_query_var('author') ), get_query_var('author') );
				}

				/* If viewing a time-based archive. */
				else if( is_time() )
				{
					if( get_query_var( 'minute' ) && get_query_var( 'hour' ) )
					{
						$trail['trail_end'] = get_the_time( __( 'g:i a', 'avia_framework' ) );
					}
					elseif( get_query_var( 'minute' ) )
					{
						$trail['trail_end'] = sprintf( __( 'Minute %1$s', 'avia_framework' ), get_the_time( __( 'i', 'avia_framework' ) ) );
					}
					elseif( get_query_var( 'hour' ) )
					{
						$trail['trail_end'] = get_the_time( __( 'g a', 'avia_framework' ) );
					}
				}

				/* If viewing a date-based archive. */
				else if( is_date() )
				{
					/* If $front has been set, check for parent pages. */
					if( $wp_rewrite->front )
					{
						$trail = array_merge( $trail, $this->get_parents( '', $wp_rewrite->front ) );
					}

					if( is_day() )
					{
						$trail[] = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'avia_framework' ) ) . '">' . get_the_time( __( 'Y', 'avia_framework' ) ) . '</a>';
						$trail[] = '<a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '" title="' . get_the_time( esc_attr__( 'F', 'avia_framework' ) ) . '">' . get_the_time( __( 'F', 'avia_framework' ) ) . '</a>';
						$trail['trail_end'] = get_the_time( __( 'j', 'avia_framework' ) );
					}
					else if( get_query_var( 'w' ) )
					{
						$trail[] = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'avia_framework' ) ) . '">' . get_the_time( __( 'Y', 'avia_framework' ) ) . '</a>';
						$trail['trail_end'] = sprintf( __( 'Week %1$s', 'avia_framework' ), get_the_time( esc_attr__( 'W', 'avia_framework' ) ) );
					}
					else if( is_month() )
					{
						$trail[] = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'avia_framework' ) ) . '">' . get_the_time( __( 'Y', 'avia_framework' ) ) . '</a>';
						$trail['trail_end'] = get_the_time( __( 'F', 'avia_framework' ) );
					}
					else if( is_year() )
					{
						$trail['trail_end'] = get_the_time( __( 'Y', 'avia_framework' ) );
					}
				}
			}

			/* If viewing search results. */
			else if( is_search() )
			{
				$trail['trail_end'] = sprintf( __( 'Search results for &quot;%1$s&quot;', 'avia_framework' ), esc_attr( get_search_query() ) );
			}

			/* If viewing a 404 error page. */
			else if( is_404() )
			{
				$trail['trail_end'] = __( '404 Not Found', 'avia_framework' );
			}

			/* Allow child themes/plugins to filter the trail array. */
			$trail = apply_filters( 'avia_breadcrumbs_trail', $trail, $args );

			/**
			 * Allow to filter trail to return unique links only (href and text)
			 *
			 * @since 4.3.2
			 * @param boolean
			 * @param mixed|array $trail
			 * @return mixed|true
			 */
			if( true === apply_filters( 'avf_breadcrumb_trail_unique', true, $trail ) )
			{
				$trail = $this->make_unique_breadcrumbs( $trail );
			}

			/* Connect the breadcrumb trail if there are items in the trail. */
			if( is_array( $trail ) )
			{
				$el_tag = 'span';

				$markup_list = ' itemscope="itemscope" itemtype="https://schema.org/BreadcrumbList" ';
				$markup_item = ' itemscope="itemscope" itemtype="https://schema.org/ListItem" itemprop="itemListElement" ';

				$vocabulary = '';		//	can be removed ( - see below )

				//google rich snippets
				if( $richsnippet === true )
				{
//					removed 4.7.2.1 as google will deprecate data-vocabulary
//					https://meetanshi.com/blog/fix-data-vocabulary-org-schema-deprecated-error/
//					https://github.com/KriesiMedia/wp-themes/issues/2650
//
//					if( is_ssl() )
//					{
//						$vocabulary = 'xmlns:v="https://rdf.data-vocabulary.org/#"';
//					}
//					else
//					{
//						$vocabulary = 'xmlns:v="http://rdf.data-vocabulary.org/#"';
//					}
				}

				/* Open the breadcrumb trail containers. */
				$breadcrumb = '<div class="breadcrumb breadcrumbs avia-breadcrumbs"><div class="breadcrumb-trail" ' . $vocabulary . '>';

				/* If $before was set, wrap it in a container. */
				if( ! empty( $before ) )
				{
					$breadcrumb .= '<' . $el_tag . ' class="trail-before">' . $before . '</' . $el_tag . '> ';
				}

				/* Wrap the $trail['trail_end'] value in a container. */
				if( ! empty( $trail['trail_end'] ) )
				{
					if( ! is_search() )
					{
						$trail['trail_end'] = avia_backend_truncate( $trail['trail_end'], $truncate, ' ', $pad = '...', false, '<strong><em><span>', true );
					}

					$trail['trail_end'] = '<' . $el_tag . ' class="trail-end">' . $trail['trail_end'] . '</' . $el_tag . '>';
				}

				if( $richsnippet === true )
				{
					$position = 0;
					foreach( $trail as $key => &$link )
					{
						if( 'trail_end' === $key )
						{
							continue;
						}

//						$link = preg_replace( '!rel=".+?"|rel=\'.+?\'|!', '', $link );
//						$link = str_replace( '<a ', '<a rel="v:url" property="v:title" ', $link );	// removed 4.7.2.1
//						$link = '<span typeof="v:Breadcrumb">'.$link.'</span>'; //removed due to data testing error

						$position ++;

						$matches = array();
						preg_match( "/<a ?.*>(.*)<\/a>/", $link, $matches );

						$link_text = ! empty( $matches[1] ) ? $matches[1] : '';
						$anchor = str_replace( $link_text . '</a>', '', $link );
						$anchor = str_replace( '<a ', '<a itemprop="url" ', $anchor );

						$new_link  = '<span ' . $markup_list . '>';
						$new_link .=	'<span ' . $markup_item . '>';
						$new_link .=		$anchor;
						$new_link .=			'<span itemprop="name">' . $link_text . '</span>';
						$new_link .=		'</a>';
						$new_link .=		'<span itemprop="position" class="hidden">' . $position . '</span>';
						$new_link .=	'</span>';
						$new_link .= '</span>';

						$link = $new_link;
					}
				}


				/* Format the separator. */
				if( ! empty( $separator ) )
				{
					$separator = '<span class="sep">' . $separator . '</span>';
				}

				/* Join the individual trail items into a single string. */
				$breadcrumb .= join( " {$separator} ", $trail );

				/* If $after was set, wrap it in a container. */
				if( ! empty( $after ) )
				{
					$breadcrumb .= ' <span class="trail-after">' . $after . '</span>';
				}

				/* Close the breadcrumb trail containers. */
				$breadcrumb .= '</div></div>';
			}

			/* Allow developers to filter the breadcrumb trail HTML. */
			$breadcrumb = apply_filters( 'avia_breadcrumbs', $breadcrumb );

			/* Output the breadcrumb. */
			if( $echo )
			{
				echo $breadcrumb;
			}
			else
			{
				return $breadcrumb;
			}

		} // End avia_breadcrumbs()

		/**
		 * Gets parent pages of any post type or taxonomy by the ID or Path.  The goal of this function is to create
		 * a clear path back to home given what would normally be a "ghost" directory.  If any page matches the given
		 * path, it'll be added.  But, it's also just a way to check for a hierarchy with hierarchical post types.
		 *
		 * @since 3.7.0			(deprecated 4.8.2  avia_breadcrumbs_get_parents() )
		 * @param int $post_id ID of the post whose parents we want.
		 * @param string $path Path of a potential parent page.
		 * @return array $trail Array of parent page links.
		 */
		public function get_parents( $post_id = '', $path = '' )
		{

			/* Set up an empty trail array. */
			$trail = array();

			/* If neither a post ID nor path set, return an empty array. */
			if( empty( $post_id ) && empty( $path ) )
			{
				return $trail;
			}

			/* If the post ID is empty, use the path to get the ID. */
			if( empty( $post_id ) )
			{

				/* Get parent post by the path. */
				$parent_page = get_page_by_path( $path );

				/* ********************************************************************
				Modification: The above line won't get the parent page if
				the post type slug or parent page path is not the full path as required
				by get_page_by_path. By using get_page_with_title, the full parent
				trail can be obtained. This may still be buggy for page names that use
				characters or long concatenated names.
				Author: Byron Rode
				Date: 06 June 2011
				******************************************************************* */

				if( empty( $parent_page ) )
				{
						// search on page name (single word)
					$qa = [
							'post_type'					=> 'page',
							'title'						=> $path,
							'post_status'				=> 'all',
							'posts_per_page'			=> 1,
							'no_found_rows'				=> true,
							'update_post_term_cache'	=> false,
							'update_post_meta_cache'	=> false,
							'orderby'					=> 'post_date ID',
							'order'						=> 'ASC'
						];

					$query = new WP_Query( $qa );

					if( ! empty( $query->post ) )
					{
						$parent_page = $query->post;
					}
				}

				if( empty( $parent_page ) )
				{
					// search on page title (multiple words)
					$qa = [
							'post_type'					=> 'page',
							'title'						=> str_replace( array( '-', '_' ), ' ', $path ),
							'post_status'				=> 'all',
							'posts_per_page'			=> 1,
							'no_found_rows'				=> true,
							'update_post_term_cache'	=> false,
							'update_post_meta_cache'	=> false,
							'orderby'					=> 'post_date ID',
							'order'						=> 'ASC'
						];

					$query = new WP_Query( $qa );

					if( ! empty( $query->post ) )
					{
						$parent_page = $query->post;
					}
				}
				/* End Modification */

				/* If a parent post is found, set the $post_id variable to it. */
				if( ! empty( $parent_page ) )
				{
					$post_id = $parent_page->ID;
				}
			}

			/* If a post ID and path is set, search for a post by the given path. */
			if( $post_id == 0 && !empty( $path ) )
			{

				/* Separate post names into separate paths by '/'. */
				$path = trim( $path, '/' );
				$matches = array();
				preg_match_all( "/\/.*?\z/", $path, $matches );

				/* If matches are found for the path. */
				if( isset( $matches ) )
				{

					/* Reverse the array of matches to search for posts in the proper order. */
					$matches = array_reverse( $matches );

					/* Loop through each of the path matches. */
					foreach( $matches as $match )
					{
						/* If a match is found. */
						if( isset( $match[0] ) )
						{
							/* Get the parent post by the given path. */
							$path = str_replace( $match[0], '', $path );
							$parent_page = get_page_by_path( trim( $path, '/' ) );

							/* If a parent post is found, set the $post_id and break out of the loop. */
							if( ! empty( $parent_page ) && $parent_page->ID > 0 )
							{
								$post_id = $parent_page->ID;
								break;
							}
						}
					}
				}
			}

			$parents = array();

			/* While there's a post ID, add the post link to the $parents array. */
			while( $post_id )
			{

				/* Get the post by ID. */
				$page = get_post( $post_id );

				/**
				 * Allow to translate breadcrumb trail - fixes a problem with parent page for portfolio
				 * https://kriesi.at/support/topic/parent-page-link-works-correct-but-translation-doesnt/
				 *
				 * @used_by				config-wpml\config.php						10
				 * @since 4.5.1
				 * @param int $post_id
				 * @return int
				 */
				$translated_id = apply_filters( 'avf_breadcrumbs_get_parents', $post_id );

				/* Add the formatted post link to the array of parents. */
				$parents[] = '<a href="' . get_permalink( $translated_id ) . '" title="' . esc_attr( get_the_title( $translated_id ) ) . '">' . get_the_title( $translated_id ) . '</a>';

				/* Set the parent post's parent to the post ID. */
				if( is_object( $page ) )
				{
					$post_id = $page->post_parent;
				}
				else
				{
					$post_id = '';
				}
			}

			/* If we have parent posts, reverse the array to put them in the proper order for the trail. */
			if( ! empty( $parents ) )
			{
				$trail = array_reverse( $parents );
			}

			/* Return the trail of parent posts. */
			return $trail;

		}

		/**
		 * Searches for term parents of hierarchical taxonomies.  This function is similar to the WordPress
		 * function get_category_parents() but handles any type of taxonomy.
		 *
		 * @since 3.7.0			(deprecated 4.8.2: avia_breadcrumbs_get_term_parents )
		 * @since 4.8.2
		 * @param int $parent_id The ID of the first parent.
		 * @param object|string $taxonomy The taxonomy of the term whose parents we want.
		 * @return array $trail Array of links to parent terms.
		 */
		public function get_term_parents( $parent_id = '', $taxonomy = '' )
		{
			/* Set up some default arrays. */
			$trail = array();
			$parents = array();

			/* If no term parent ID or taxonomy is given, return an empty array. */
			if( empty( $parent_id ) || empty( $taxonomy ) )
			{
				return $trail;
			}

			/* While there is a parent ID, add the parent term link to the $parents array. */
			while ( $parent_id )
			{
				/* Get the parent term. */
				$parent = get_term( $parent_id, $taxonomy );

				/* Add the formatted term link to the array of parent terms. */
				$parents[] = '<a href="' . get_term_link( $parent, $taxonomy ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a>';

				/* Set the parent term's parent as the parent ID. */
				$parent_id = $parent->parent;
			}

			/* If we have parent terms, reverse the array to put them in the proper order for the trail. */
			if( ! empty( $parents ) )
			{
				$trail = array_reverse( $parents );
			}

			/* Return the trail of parent terms. */
			return $trail;
		}

		/**
		 * Filters the trail and removes the first entries that have the same href's and link text
		 * Trail must be an array
		 *
		 * @since 4.3.2			( deprecated 4.8.2: avia_make_unique_breadcrumbs )
		 * @since 4.8.2
		 * @param mixed|array $trail
		 * @return mixed|array
		 */
		protected function make_unique_breadcrumbs( $trail )
		{
			if( ! is_array( $trail ) || empty( $trail ) )
			{
				return $trail;
			}

			$splitted = array();

			foreach( $trail as $key => $link )
			{
				$url = array();
				$text = array();
				preg_match( '/href=["\']?([^"\'>]+)["\']?/', $link, $url );
				preg_match( '/<\s*a[^>]*>([^<]*)<\s*\/\s*a\s*>/', $link, $text );

				$splitted[] = array(
								'url'	=> isset( $url[1] ) ? untrailingslashit( $url[1] ) : '',
								'text'	=> isset( $text[1] ) ? $text[1] : $link
						);
			}

			$last_index = count( $trail );
			foreach( $splitted as $key => $current )
			{
				for( $i = $key + 1; $i < $last_index; $i++ )
				{
					$check = $splitted[ $i ];

					//	entry without url we do not remove - normally the last entry
					if( empty( $check['url'] ) )
					{
						continue;
					}

					if( ( strcasecmp( $current['url'], $check['url'] ) == 0 ) && ( strcasecmp( $current['text'], $check['text'] ) == 0 ) )
					{
						$splitted[ $key ]['delete'] = true;
						break;
					}
				}
			}

			$deleted = false;
			foreach( $splitted as $key => $current )
			{
				if( ! empty( $current['delete'] ) && ( true === $current['delete'] ) )
				{
					unset( $trail[ $key ] );
					$deleted = true;
				}
			}

			if( $deleted )
			{
				$trail = array_merge( $trail );
			}

			return $trail;
		}

	}

	/**
	 * @since 4.8.2
	 * @return avia_breadcrumb_trail
	 */
	function Avia_Breadcrumb_Trail()
	{
		return avia_breadcrumb_trail::instance();
	}

}
