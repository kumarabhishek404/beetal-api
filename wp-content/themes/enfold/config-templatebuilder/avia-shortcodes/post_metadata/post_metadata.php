<?php
/**
 * Shortcode for Post Metadata
 *
 * Creates an adjustable text line containing metadata for a given page, post, custom post type
 *
 * @since 4.7.6.3
 * @added_by Günter
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'avia_sc_post_metadata' ) )
{
	class avia_sc_post_metadata extends aviaShortcodeTemplate
	{
		/**
		 * @since 4.7.6.3
		 * @var int
		 */
		static protected $instance = 0;

		/**
		 *
		 * @since 4.7.6.3
		 * @var array
		 */
		protected $atts;

		/**
		 * @since 4.7.6.3
		 * @var int
		 */
		protected $count;

		/**
		 * Needed in backend: when ajax callback for preview we have no direct context to post.
		 *
		 * @since 4.7.6.3
		 * @var int
		 */
		protected $post_id;

		/**
		 * Needed in backend: when ajax callback for preview we have no direct context to post.
		 *
		 * @since 4.7.6.3
		 * @var WP_Post
		 */
		protected $post;

		/**
		 *
		 * @since 4.7.6.3
		 * @param AviaBuilder $builder
		 */
		public function __construct( AviaBuilder $builder )
		{
			$this->atts = array();

			$this->post_id = 0;
			$this->post = null;

			parent::__construct( $builder );
		}

		/**
		 * @since 4.7.6.3
		 */
		public function __destruct()
		{
			unset( $this->atts );
			unset( $this->post );

			parent::__destruct();
		}

		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['version']		= '1.0';
			$this->config['self_closing']	= 'no';
			$this->config['base_element']	= 'yes';

			$this->config['name']			= __( 'Post Metadata', 'avia_framework' );
			$this->config['tab']			= __( 'Content Elements', 'avia_framework' );
			$this->config['icon']			= AviaBuilder::$path['imagesURL'] . 'sc-post-metadata.png';
			$this->config['order']			= 5;
			$this->config['target']			= 'avia-target-insert';
			$this->config['shortcode']		= 'av_post_metadata';
			$this->config['shortcode_nested'] = array( 'av_metadata_item' );
			$this->config['tooltip']		= __( 'Add selected page/post metadata in a textline', 'avia_framework' );
			$this->config['preview']		= 'large';
			$this->config['disabling_allowed'] = true;
			$this->config['id_name']		= 'id';
			$this->config['id_show']		= 'yes';
			$this->config['alb_desc_id']	= 'alb_description';
		}

		/**
		 * @since 4.7.6.3
		 */
		protected function extra_assets()
		{
			$ver = Avia_Builder()->get_theme_version();
			$min_css = avia_minify_extension( 'css' );

			//load css
			wp_enqueue_style( 'avia-module-post-metadata', AviaBuilder::$path['pluginUrlRoot'] . "avia-shortcodes/post_metadata/post_metadata{$min_css}.css", array( 'avia-layout' ), $ver );
		}

		/**
		 * Popup Elements
		 *
		 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
		 * opens a modal window that allows to edit the element properties
		 *
		 * @since 4.7.6.3
		 * @return void
		 */
		protected function popup_elements()
		{

			$this->elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'content_metadata' ),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Styling', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array(
													$this->popup_key( 'styling_spacing' ),
													$this->popup_key( 'styling_alignment' ),
													$this->popup_key( 'styling_color' ),
													$this->popup_key( 'styling_font' )
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type' 	=> 'toggle_container',
							'nodescription' => true
						),

						array(
								'type'			=> 'template',
								'template_id'	=> 'screen_options_toggle',
								'lockable'		=> true
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'developer_options_toggle',
								'args'			=> array( 'sc' => $this )
							),

					array(
							'type' 	=> 'toggle_container_close',
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array( 'sc' => $this )
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);

		}

		/**
		 * Create and register templates for easier maintainance
		 *
		 * @since 4.7.6.3
		 */
		protected function register_dynamic_templates()
		{

			$this->register_modal_group_templates();

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Select Post', 'avia_framework' ),
							'desc'			=> __( 'Select the post to show metadata.', 'avia_framework' ),
							'id'			=> 'post_selected',
							'std'			=> '',
							'lockable'		=> true,
							'subtype'		=> array(
													__( 'Current post', 'avia_framework' )		=> '',
													__( 'Select a category', 'avia_framework' ) => 'single'
												),
							'no_toggle'		=> true,
							'no_target'		=> true
						),

						array(
							'name'			=> __( 'Add/Edit Metadata', 'avia_framework' ),
							'desc'			=> __( 'Here you can add, remove and edit the metadata you want to display.', 'avia_framework' ),
							'type'			=> 'modal_group',
							'id'			=> 'content',
							'modal_title'	=> __( 'Edit Metadata Element', 'avia_framework' ),
							'std'			=> array(
													array(
															'metadata'		=> 'author',
															'before_meta'	=> 'by'
														),
													array(
															'metadata'		=> 'modified'
														),
													array(
															'metadata'		=> 'categories',
															'before_meta'	=> 'in'
														)
												),
							'editable_item'	=> true,
							'lockable'		=> true,
							'tmpl_set_default'	=> false,
							'subelements'	=> $this->create_modal()
						),

						array(
							'name' 	=> __( 'Seperator', 'avia_framework' ),
							'desc' 	=> __( 'Will be used to seperate multiple metadata', 'avia_framework' ) ,
							'id' 	=> 'seperator',
							'type' 	=> 'input',
							'std' 	=> '/',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Prepend a text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a text that should be displayed before the selected metadata infos', 'avia_framework' ) ,
							'id' 	=> 'before_meta_content',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true
						),

						array(
							'name' 	=> __( 'Append a text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a text that should be displayed after the selected metadata infos', 'avia_framework' ) ,
							'id' 	=> 'after_meta_content',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true
						)

				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_metadata' ), $c );

			/**
			 * Styling Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'margin_padding',
							'name'			=> '',
							'desc'			=> '',
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Margin And Padding', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_spacing' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Content Alignment', 'avia_framework' ),
							'desc' 	=> __( 'Alignment of the metadata content', 'avia_framework' ),
							'id' 	=> 'align',
							'type' 	=> 'select',
							'std' 	=> 'left',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Center', 'avia_framework' )	=> 'center',
												__( 'Left', 'avia_framework' )		=> 'left',
												__( 'Right', 'avia_framework' )		=> 'right',
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Alignment', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_alignment' ), $template );

			$c = array(
						array(
							'name' 	=> __( 'Custom Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_title',
							'type' 	=> 'colorpicker',
							'std' 	=> '',
							'lockable'	=> true,
						),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Color', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_color' ), $template );

			$c = array(
						array(
							'name'			=> __( 'Font Sizes', 'avia_framework' ),
							'desc'			=> __( 'Select a custom font size for the metadata in pixel.', 'avia_framework' ),
							'type'			=> 'template',
							'template_id'	=> 'font_sizes_icon_switcher',
							'lockable'		=> true,
							'textfield'		=> true,
							'subtype'		=> array(
												'default'	=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'desktop'	=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '' ), 'px' ),
												'medium'	=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'small'		=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' ),
												'mini'		=> AviaHtmlHelper::number_array( 8, 40, 1, array( __( 'Use Default', 'avia_framework' ) => '', __( 'Hidden', 'avia_framework' ) => 'hidden' ), 'px' )
											),
							'id_sizes'		=> array(
												'default'	=> 'size',
												'desktop'	=> 'av-desktop-font-size',
												'medium'	=> 'av-medium-font-size',
												'small'		=> 'av-small-font-size',
												'mini'		=> 'av-mini-font-size'
											)
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Font Size', 'avia_framework' ),
								'content'		=> $c
							),
					);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_font' ), $template );
		}

		/**
		 * Creates the modal popup for a single entry
		 *
		 * @since 4.7.6.3
		 * @return array
		 */
		protected function create_modal()
		{
			$elements = array(

				array(
						'type' 	=> 'tab_container',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'modal_content_metadata' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Advanced', 'avia_framework' ),
						'nodescription' => true
					),

					array(
							'type'			=> 'template',
							'template_id'	=> $this->popup_key( 'modal_advanced_link' )
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),

				array(
						'type'			=> 'template',
						'template_id'	=> 'element_template_selection_tab',
						'args'			=> array(
												'sc'			=> $this,
												'modal_group'	=> true
											)
					),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)

				);

			return $elements;
		}


		/**
		 * Register all templates for the modal group popup
		 *
		 * @since 4.7.6.3
		 */
		protected function register_modal_group_templates()
		{
			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'name'		=> __( 'Metadata', 'avia_framework' ),
							'desc'		=> __( 'Select the metadata you want to display. If empty (or not available) your selection will be skipped.', 'avia_framework' ),
							'id'		=> 'metadata',
							'type'		=> 'select',
							'std'		=> 'author',
							'lockable'	=> true,
							'subtype'	=> array(
												__( 'Author' ,'avia_framework' )						=> 'author',
												__( 'Categories' ,'avia_framework' )					=> 'categories',
												__( 'Tags' ,'avia_framework' )							=> 'tags',
												__( 'Comments Count' ,'avia_framework' )				=> 'comments',
												__( 'Revisions Count' ,'avia_framework' )				=> 'revisions',
												__( 'Publishing Date' ,'avia_framework' )				=> 'published',
												__( 'Publishing Date and Time' ,'avia_framework' )		=> 'published time',
												__( 'Last Modified Date' ,'avia_framework' )			=> 'modified',
												__( 'Last Modified Date and Time' ,'avia_framework' )	=> 'modified time',
											)
						),

						array(
							'name' 	=> __( 'Prepend a text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a text that should be displayed before the metadata info', 'avia_framework' ) ,
							'id' 	=> 'before_meta',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true,
						),

						array(
							'name' 	=> __( 'Append a text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a text that should be displayed after the metadata info', 'avia_framework' ) ,
							'id' 	=> 'after_meta',
							'type' 	=> 'input',
							'std' 	=> '',
							'lockable'	=> true,
						),


				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_content_metadata' ), $c );

			/**
			 * Content Tab
			 * ===========
			 */

			$c = array(
						array(
							'type'			=> 'template',
							'template_id'	=> 'linkpicker_toggle',
							'name'			=> __( 'Add a link', 'avia_framework' ),
							'desc'			=> __( 'Use a default generated link depending on the meta element (if a link exists), set a custom link or display only as text.', 'avia_framework' ),
							'id'			=> 'link_meta',
							'std'			=> 'default',
							'target_id'		=> 'link_target',
							'lockable'		=> true,
							'subtypes'		=> array( 'default', 'no', 'manually', 'single', 'taxonomy' ),
							'no_toggle'		=> true
						)
				);

			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'modal_advanced_link' ), $c );
		}

		/**
		 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
		 * Works in the same way as Editor Element
		 *
		 * @since 4.7.6.3
		 * @param array $params				this array holds the default values for $content and $args.
		 * @return array					usually holds an innerHtml key that holds item specific markup.
		 */
		public function editor_sub_element( array $params )
		{
			$default = array();
			$locked = array();
			$attr = $params['args'];
			Avia_Element_Templates()->set_locked_attributes( $attr, $this, $this->config['shortcode_nested'][0], $default, $locked );

			$element = $this->get_popup_element_by_id( 'metadata' );

			$data_array = array_flip( $element['subtype'] );
			$val = isset( $data_array[ $attr['metadata'] ] ) ? $data_array[ $attr['metadata'] ] : $attr['metadata'];

			$template = $this->update_template_lockable( 'metadata', '{{metadata}}', $locked, $data_array );

			$params['innerHtml']  = '';
			$params['innerHtml'] .= "<div class='avia_title_container' data-update_element_template='yes'>";
			$params['innerHtml'] .=		"<span {$template} >{$val}</span>";
			$params['innerHtml'] .=	'</div>';

			return $params;
		}

		/**
		 * Returns false by default.
		 * Override in a child class if you need to change this behaviour.
		 *
		 * @since 4.7.6.3
		 * @param string $shortcode
		 * @return boolean
		 */
		public function is_nested_self_closing( $shortcode )
		{
			if( in_array( $shortcode, $this->config['shortcode_nested'] ) )
			{
				return true;
			}

			return false;
		}

		/**
		 * Create custom stylings
		 *
		 * @since 4.8.4
		 * @param array $args
		 * @return array
		 */
		protected function get_element_styles( array $args )
		{
			$result = parent::get_element_styles( $args );

			extract( $result );

			$default = array(
						'post_selected'			=> '',
						'seperator'				=> '/',
						'before_meta_content'	=> '',
						'after_meta_content'	=> '',
						'margin'				=> '',
						'padding'				=> '',
						'align'					=> 'left',
						'custom_title'			=> '',
						'size'					=> '',
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$locked = array();
			Avia_Element_Templates()->set_locked_attributes( $atts, $this, $shortcodename, $default, $locked, $content );
			Avia_Element_Templates()->add_template_class( $meta, $atts, $default );

			$metadata = ShortcodeHelper::shortcode2array( $content, 1 );

			$group_defaults = $this->sync_sc_defaults_array( $this->get_group_defaults(), 'modal_item', 'no_content' );

			foreach( $metadata as &$meta_values )
			{
				Avia_Element_Templates()->set_locked_attributes( $meta_values['attr'], $this, $this->config['shortcode_nested'][0], $group_defaults, $locked, $meta_values['content'] );
			}

			unset( $meta_values );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );

			$atts['metadata'] = $metadata;


			if( is_numeric( $atts['size'] ) )
			{
				$atts['size'] .= 'px';
			}

			$element_styling->create_callback_styles( $atts );

			$classes = array(
						'av-post-metadata-container',
						$element_id,
						'av-metadata-container-align-' . $atts['align'],
					);

			$element_styling->add_classes( 'container', $classes );
			$element_styling->add_classes_from_array( 'container', $meta, 'el_class' );
			$element_styling->add_responsive_classes( 'container', 'hide_element', $atts );
			$element_styling->add_responsive_font_sizes( 'container-inner', 'size', $atts, $this );

			$element_styling->add_styles( 'container', array(
												'color'		=> $atts['custom_title'],
												'font-size'	=> 'hidden' != $atts['size'] ? $atts['size'] : ''
											) );

			$element_styling->add_responsive_styles( 'container', 'margin', $atts, $this );
			$element_styling->add_responsive_styles( 'container', 'padding', $atts, $this );


			$selectors = array(
							'container'			=> "#top .av-post-metadata-container.{$element_id}",
							'container-inner'	=> "#top .av-post-metadata-container.{$element_id} .av-post-metadata-container-inner"
				);

			$element_styling->add_selectors( $selectors );

			$result['default'] = $default;
			$result['atts'] = $atts;
			$result['content'] = $content;
			$result['element_styling'] = $element_styling;

			return $result;
		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @since 4.7.6.3
		 * @param array $atts				array of attributes
		 * @param string $content			text within enclosing form of shortcode element
		 * @param string $shortcodename		the shortcode found, when == callback name
		 * @param array $meta
		 * @return string					the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			$result = $this->get_element_styles( compact( array( 'atts', 'content', 'shortcodename', 'meta' ) ) );

			extract( $result );
			$this->atts = $atts;
			extract( $this->atts );

			avia_sc_post_metadata::$instance ++;

			global $post;

			$this->post_id = 0;
			$this->post = null;

			/**
			 * Initialise, as on ajax preview callback there is no connection to post
			 */
			if( isset( $_REQUEST['text_to_preview_post_id'] ) )
			{
				$this->post_id = (int) $_REQUEST['text_to_preview_post_id'];
			}
			else if( $post instanceof WP_Post )
			{
				$this->post_id = $post->ID;
			}

			if( $this->atts['post_selected'] != '' )
			{
				$link = explode( ',', $this->atts['post_selected'], 2 );

				if( isset( $link[1] ) && is_numeric( $link[1] ) )
				{
					$this->post_id = (int) $link[1];
				}
			}

			$this->post = get_post( $this->post_id );

			if( ! $this->post instanceof WP_Post )
			{
				$this->post = null;
				return '';
			}


			$metadata_content = $this->get_metadata_content( $metadata );

			if( empty( $metadata_content ) )
			{
				return '';
			}


			$element_styling->add_classes( 'container', 'av-metadata-container-' . avia_sc_post_metadata::$instance );

			$style_tag = $element_styling->get_style_tag( $element_id );
			$container_class = $element_styling->get_class_string( 'container' );

			$output	 = '';
			$output .= $style_tag;
			$output .= "<div {$meta['custom_el_id']} class='{$container_class}'>";
			$output .=		"<div class='av-post-metadata-container-inner'>";

			if( ! empty( $before_meta_content ) )
			{
				$output .=		'<span class="av-post-metadata-content av-post-metadata-before-meta">';
				$output .=			esc_html( $before_meta_content );
				$output .=		'</span>';
			}

			$output .=			'<span class="av-post-metadata-content av-post-metadata-meta-content">';
			$output .=				$metadata_content;
			$output .=			'</span>';

			if( ! empty( $after_meta_content ) )
			{
				$output .=		'<span class="av-post-metadata-content av-post-metadata-after-meta">';
				$output .=			esc_html( $after_meta_content );
				$output .=		'</span>';
			}

			$output .=		'</div>';
			$output .= '</div>';

			return $output;
		}

		/**
		 * Returns the default array
		 *
		 * @since 4.8
		 * @return array
		 */
		protected function get_group_defaults()
		{
			$default = array(
						'metadata'		=> 'author',
						'before_meta'	=> '',
						'after_meta'	=> '',
						'link_meta'		=> 'default',
						'link_target'	=> '',
						'url'			=> '',		//	save url if link_meta != 'default'
						'attributes'	=> ''		//	target="_blank" rel="nofollow"
					);

			return $default;
		}

		/**
		 * Returns all selected metadata in a HTML string
		 *
		 * @since 4.7.6.3
		 * @param array $content
		 * @return string
		 */
		protected function get_metadata_content( $content = array() )
		{
			$sep = '<span class="av-post-metadata-content av-post-metadata-separator">' . $this->atts['seperator'] . '</span>';
			$output	= array();

			foreach( $content as $meta_values )
			{
				$meta = shortcode_atts( $this->get_group_defaults(), $meta_values['attr'], $this->config['shortcode_nested'][0] );

				$rel_attr = array();

				switch( $meta['metadata'] )
				{
					case 'author':
						$rel_attr[] = 'author';
						break;
				}

				$meta['attributes'] = AviaHelper::get_link_target( $meta['link_target'], $meta['link_meta'], $rel_attr );
				$link = ( 'default' == $meta['link_meta'] ) ? '' : trim( AviaHelper::get_url( $meta['link_meta'] ) );
				$meta['url'] = ( in_array( $link, array( 'http://', 'https://', 'manually' ) ) ) ? '' : $link;

				$out = '';

				switch( $meta['metadata'] )
				{
					case 'author':
						$out = $this->author( $meta );
						break;
					case 'categories':
						$out = $this->taxonomies( $meta, 'categories' );
						break;
					case 'tags':
						$out = $this->taxonomies( $meta, 'tags' );
						break;
					case 'comments':
						$out = $this->comments( $meta );
						break;
					case 'revisions':
						$out = $this->revisions( $meta );
						break;
					case 'published':
					case 'published time':
					case 'modified':
					case 'modified time':
						$out = $this->date_time( $meta );
						break;
				}

				if( ! empty( $out ) )
				{
					$output[] = $out;
				}
			}

			$output = ! empty( $output ) ? implode( $sep, $output ) : '';

			return $output;
		}

		/**
		 * Return Metadata for author
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @return string
		 */
		protected function author( array $meta )
		{

			$output	 = '';

			$output	.= '<span class="av-post-metadata-content av-post-metadata-author" ' . avia_markup_helper( array( 'context' => 'author_name', 'echo' => false ) ) . '>';
			$output	.=		$this->html_before( $meta );

			if( 'default' == $meta['link_meta'] )
			{
				$output	.=	'<span class="av-post-metadata-author-link" >';
				$output	.=		'<a href="' . get_author_posts_url( $this->post->post_author ) . '" ' . $meta['attributes'] . '>' . get_the_author_meta( 'display_name', $this->post->post_author ) . '</a>';
				$output	.=	'</span>';
			}
			else if( $meta['url'] != '' )
			{
				$output	.=	'<span class="av-post-metadata-author-link" >';
				$output	.=		'<a href="' . $meta['url'] . '" ' . $meta['attributes'] . '>' . get_the_author_meta( 'display_name', $this->post->post_author ) . '</a>';
				$output	.=	'</span>';
			}
			else
			{
				$output	.=	'<span class="av-post-metadata-author-name" >';
				$output	.=		get_the_author_meta( 'display_name', $this->post->post_author );
				$output	.=	'</span>';
			}

			$output	.=		$this->html_after( $meta );
			$output .= '</span>';

			return $output;
		}

		/**
		 * Get taxonomies terms for post
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @param string $which				'categories' | 'tags'
		 * @return string
		 */
		protected function taxonomies( array $meta, $which )
		{

			// Get post type taxonomies.
			$taxonomies = get_object_taxonomies( $this->post->post_type, 'objects' );
			$names = array();
			$links = array();

			/**
			 * Filter excluded taxonomies
			 *
			 * @since 4.8.9.1
			 * @param array $taxonomies
			 * @return array
			 */
			$excluded_taxonomies = apply_filters( 'avf_post_metadata_excluded_taxonomies', array( 'post_tag', 'post_format' ), $taxonomies );

			foreach ( $taxonomies as $taxonomy_slug => $taxonomy )
			{
				if( $which == 'tags' )
				{
					if( $taxonomy_slug != 'post_tag' )
					{
						continue;
					}
				}
				else if( in_array( $taxonomy_slug, $excluded_taxonomies ) )
				{
					continue;
				}

				// Get the terms related to post.
				$terms = get_the_terms( $this->post->ID, $taxonomy_slug );

				if( ! is_array( $terms ) )
				{
					continue;
				}

				foreach ( $terms as $term )
				{
					$names[ $term->slug ] = $term->name;

					if( 'no_link' != $meta['link_meta'] )
					{
						$links[ $term->slug ] = esc_url( get_term_link( $term->slug, $taxonomy_slug ) );
					}
				}
			}

			if( empty( $names ) )
			{
				return '';
			}

			asort( $names );

			$output	 = '';

			$output	.= '<span class="av-post-metadata-content av-post-metadata-category">';
			$output	.=		$this->html_before( $meta );

			$sep = '';

			foreach( $names as $slug => $name )
			{
				if( $sep != '' )
				{
					$output	.= $sep;
				}

				if( 'default' == $meta['link_meta'] )
				{
					$output	.=	'<span class="av-post-metadata-category-link" >';
					$output	.=		'<a href="' . $links[ $slug ] . '" ' . $meta['attributes'] . '>' . $name . '</a>';
					$output	.=	'</span>';
				}
				else if( $meta['url'] != '' )
				{
					$output	.=	'<span class="av-post-metadata-category-link" >';
					$output	.=		'<a href="' . $meta['url'] . '" ' . $meta['attributes'] . '>' . $name . '</a>';
					$output	.=	'</span>';
				}
				else
				{
					$output	.=	'<span class="av-post-metadata-category-name" ' . $meta['attributes'] . '>';
					$output	.=		$name;
					$output	.=	'</span>';
				}

				$sep = ', ';
			}

			$output	.=		$this->html_after( $meta );
			$output .= '</span>';

			return $output;
		}

		/**
		 * Get comments count for post
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @return string
		 */
		protected function comments( array $meta )
		{

			$count = get_comments_number( $this->post->ID );
			$force_no_link = false;

			if ( 0 == $count && ! comments_open( $this->post->ID ) )
			{
				$result = __( 'Comments Off', 'avia_framework' );
				$force_no_link = true;
			}
			else if( post_password_required( $this->post->ID ) && ! is_admin() )
			{
				$result = __( 'Enter your password to view comments', 'avia_framework' );
				$force_no_link = true;
			}
			else if( post_password_required( $this->post->ID ) && is_admin() )
			{
				$result = sprintf( __( 'Password protected - %s comment(s)', 'avia_framework' ), $count );
			}
			else
			{
				if( $count == 0 )
				{
					$result = __( 'No comments', 'avia_framework' );
				}
				if( $count == 1 )
				{
					$result = sprintf( __( '%s Comment', 'avia_framework' ), $count );
				}
				else
				{
					$result = sprintf( __( '%s Comments', 'avia_framework' ), $count );
				}
			}

			$result .= sprintf( __( '<span class="av-screen-reader-only"> on %s</span>', 'avia_framework' ), esc_html( $this->post->post_title ) );


			$output	 = '';

			$output	.= '<span class="av-post-metadata-content av-post-metadata-comments">';
			$output	.=		$this->html_before( $meta );

			if( 'default' == $meta['link_meta'] && ! $force_no_link )
			{
				$output	.=	'<span class="av-post-metadata-comments-count-link" >';
				$output	.=		'<a href="' . get_permalink( $this->post->ID ) . '" ' . $meta['attributes'] . '>' . $result . '</a>';
				$output	.=	'</span>';
			}
			else if( $meta['url'] != '' && ! $force_no_link )
			{
				$output	.=	'<span class="av-post-metadata-omments-count-link" >';
				$output	.=		'<a href="' . $meta['url'] . '" ' . $meta['attributes'] . '>' . $result . '</a>';
				$output	.=	'</span>';
			}
			else
			{
				$output	.=	'<span class="av-post-metadata-comments-count" >';
				$output	.=		$result;
				$output	.=	'</span>';
			}

			$output	.=		$this->html_after( $meta );
			$output .= '</span>';

			return $output;
		}

		/**
		 * Get revisions count for post
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @return string
		 */
		protected function revisions( array $meta )
		{

			$revisions = wp_get_post_revisions( $this->post->ID );
			$count = count( $revisions );

			switch( $count )
			{
				case 0:
					$result = __( 'No revisions', 'avia_framework' );
					break;
				case 1:
					$result = sprintf( __( '%s revision', 'avia_framework' ), $count );
					break;
				default:
					$result = sprintf( __( '%s revisions', 'avia_framework' ), $count );
					break;
			}

			$result .= sprintf( __( '<span class="av-screen-reader-only"> on %s</span>', 'avia_framework' ), esc_html( $this->post->post_title ) );

			$output	 = '';

			$output	.= '<span class="av-post-metadata-content av-post-metadata-revisions">';
			$output	.=		$this->html_before( $meta );

			if( $meta['url'] != '' )
			{
				$output	.=	'<span class="av-post-metadata-revisions-count" >';
				$output	.=		'<a href="' . $meta['url'] . '" ' . $meta['attributes'] . '>' . $result . '</a>';
				$output	.=	'</span>';
			}
			else
			{
				$output	.=	'<span class="av-post-metadata-revisions-count" >';
				$output	.=		$result;
				$output	.=	'</span>';
			}

			$output	.=		$this->html_after( $meta );
			$output .= '</span>';

			return $output;
		}

		/**
		 * Get published/modified date for post
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @return string
		 */
		protected function date_time( array $meta )
		{
			$what = explode( ' ', $meta['metadata'] );

			$format = get_option( 'date_format' );
			if( isset( $what[1] ) )
			{
				$format .= ' ' . get_option( 'time_format' );
			}

			$time = 'published' == $what[0] ? get_post_time( $format, false, $this->post->ID, true ) : get_post_modified_time( $format, false, $this->post->ID, true );

			$output	 = '';

			$output	.= '<span class="av-post-metadata-content av-post-metadata-' . $meta['metadata'] . '">';
			$output	.=		$this->html_before( $meta );

			if( $meta['url'] != '' )
			{
				$output	.=	'<span class="av-post-metadata-' . $meta['metadata'] . '-date" >';
				$output	.=		'<a href="' . $meta['url'] . '" ' . $meta['attributes'] . '>' . $time . '</a>';
				$output	.=	'</span>';
			}
			else
			{
				$output	.=	'<span class="av-post-metadata-' . $meta['metadata'] . '-date" >';
				$output	.=		$time;
				$output	.=	'</span>';
			}

			$output	.=		$this->html_after( $meta );
			$output .= '</span>';

			return $output;

		}


		/**
		 * Returns the "before" single meta string
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @return string
		 */
		protected function html_before( array $meta )
		{
			if( empty( $meta['before_meta'] ) )
			{
				return '';
			}

			return '<span class="av-metadata-before av-metadata-before-' . $meta['metadata'] . '">' . esc_html( $meta['before_meta'] ) . '</span>';
		}

		/**
		 * Returns the "after" single meta string
		 *
		 * @since 4.7.6.3
		 * @param array $meta
		 * @return string
		 */
		protected function html_after( array $meta )
		{
			if( empty( $meta['after_meta'] ) )
			{
				return '';
			}

			return '<span class="av-metadata-after av-metadata-after-' . $meta['metadata'] . '">' . esc_html( $meta['after_meta'] ) . '</span>';
		}

	}

}

