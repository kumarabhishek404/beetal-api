<?php
/**
 * Slider Template 6
 *
 * This template can be overridden by copying it to yourtheme/post-slider-and-carousel-pro/slider/design-6.php
 * 
 * @package Post Slider and Carousel Pro
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

// Post Meta Data
$meta_data = array(
				'author'	=> $show_author,
				'post_date'	=> $show_date,
				'comments'	=> $show_comments,
				'sharing'	=> $sharing
			);
?>
<div class="psacp-post-slide <?php echo esc_attr( $wrp_cls ); ?> psacp-clearfix" <?php if( $url_hash_listener ) { echo 'data-hash="'.esc_attr( $count.'-'.$unique ).'"'; } ?>>
	<div class="psacp-post-slider-content psacp-clearfix">
		<div class="psacp-col-left psacp-col-xl-6 psacp-columns psacp-clearfix">	
			<div class="psacp-featured-meta">
				<?php if( $show_category && $cate_name ) { ?>
				<div class="psacp-post-cats"><?php echo wp_kses_post( $cate_name ); ?></div>
				<?php } ?>
                <span>CASE STUDY</span>
				<h2 class="psacp-post-title">
					<a href="<?php echo esc_url( $post_link ); ?>" target="<?php echo esc_attr( $link_behaviour ); ?>"><?php the_title(); ?></a>
				</h2>

				<?php if( $show_sub_title && $sub_title ) { ?>
					<div class="psacp-post-sub-title"><?php echo wp_kses_post( $sub_title ); ?></div>
				<?php }

				if( $show_date || $show_author || $show_comments || $sharing ) { ?>
				<div class="psacp-post-meta psacp-post-meta-up">
					<?php echo psacp_post_meta_data( $meta_data, array( 'sharing_trigger' => 'hover' ) ); // WPCS: XSS ok. ?>
				</div>
				<?php }

				if( $show_content ) { ?>
				<div class="psacp-post-content">
					<div class="psacp-post-desc"><?php 
									 $excerpt = get_the_excerpt();
    $excerpt = wp_trim_words( $excerpt, $num_words = 40 , $more );
									 echo str_replace('Previous Next','', $excerpt); // WPCS: XSS ok. ?></div>
					<?php if( $show_read_more ) { ?>
					<a href="<?php echo esc_url( $post_link ); ?>" target="<?php echo esc_attr( $link_behaviour ); ?>" class="psacp-rdmr-btn"><?php echo wp_kses_post( $read_more_text ); ?></a>
					<?php } ?>
                    <?php if( have_rows('case_study_toggle') ): ?>
                    <div class="slide-toggle">
                        <div class="elementor-container elementor-column-gap-default">
                            <div class="elementor-column elementor-col-33 elementor-top-column elementor-element elementor-element-8389e0b" data-id="8389e0b" data-element_type="column">
                                <div class="elementor-widget-wrap elementor-element-populated">
                                    <div class="elementor-element elementor-element-8fe7c6d elementor-widget elementor-widget-heading" data-id="8fe7c6d" data-element_type="widget" data-widget_type="heading.default">
                                        <div class="elementor-widget-container mt10">
                                            <h6 class="elementor-heading-title elementor-size-default">How we helped?</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="elementor-column elementor-col-66 elementor-top-column elementor-element elementor-element-a3da18e" data-id="a3da18e" data-element_type="column">
                                <div class="elementor-widget-wrap elementor-element-populated">
                                    <div class="elementor-element elementor-element-a2d4788 elementor-widget elementor-widget-toggle" data-id="a2d4788" data-element_type="widget" data-widget_type="toggle.default">
                                        <div class="elementor-widget-container">
 <style>/*! elementor - v3.11.2 - 22-02-2023 */
.elementor-toggle{text-align:left}.elementor-toggle .elementor-tab-title{font-weight:700;line-height:1;margin:0;padding:15px;border-bottom:1px solid #d4d4d4;cursor:pointer;outline:none}.elementor-toggle .elementor-tab-title .elementor-toggle-icon{display:inline-block;width:1em}.elementor-toggle .elementor-tab-title .elementor-toggle-icon svg{-webkit-margin-start:-5px;margin-inline-start:-5px;width:1em;height:1em}.elementor-toggle .elementor-tab-title .elementor-toggle-icon.elementor-toggle-icon-right{float:right;text-align:right}.elementor-toggle .elementor-tab-title .elementor-toggle-icon.elementor-toggle-icon-left{float:left;text-align:left}.elementor-toggle .elementor-tab-title .elementor-toggle-icon .elementor-toggle-icon-closed{display:block}.elementor-toggle .elementor-tab-title .elementor-toggle-icon .elementor-toggle-icon-opened{display:none}.elementor-toggle .elementor-tab-title.elementor-active{border-bottom:none}.elementor-toggle .elementor-tab-title.elementor-active .elementor-toggle-icon-closed{display:none}.elementor-toggle .elementor-tab-title.elementor-active .elementor-toggle-icon-opened{display:block}.elementor-toggle .elementor-tab-content{padding:15px;border-bottom:1px solid #d4d4d4;display:none}@media (max-width:767px){.elementor-toggle .elementor-tab-title{padding:12px}.elementor-toggle .elementor-tab-content{padding:12px 10px}}.e-con-inner>.elementor-widget-toggle,.e-con>.elementor-widget-toggle{width:var(--container-widget-width);--flex-grow:var(--container-widget-flex-grow)}</style>
                                            <div class="elementor-toggle" role="tablist">
                                               <?php
                                               $inc = get_the_ID();
                                               while( have_rows('case_study_toggle') ): the_row();
                                               $toggleTitle = get_sub_field('title');
                                               $toggleDescription = get_sub_field('description');
												$slugTitle = sanitize_title($toggleTitle);
                                               ?>
                                                <div class="elementor-toggle-item">
                                                    <div id="elementor-tab-title-<?php echo $slugTitle.'-'.$inc;?>" class="elementor-tab-title" data-tab="<?php echo $inc;?>" role="tab" aria-controls="elementor-tab-content-<?php echo $inc;?>" aria-expanded="false" tabindex="-1" aria-selected="false"> <span class="elementor-toggle-icon elementor-toggle-icon-left" aria-hidden="true"> <span class="elementor-toggle-icon-closed"><i class="fas fa-chevron-right"></i></span> <span class="elementor-toggle-icon-opened"><i class="elementor-toggle-icon-opened fa fa-chevron-down"></i></span> </span> <div class="elementor-toggle-title"><?php echo $toggleTitle;?></div> </div>
                                                    <div id="elementor-tab-content-<?php echo $slugTitle.'-'.$inc;?>" class="elementor-tab-content elementor-clearfix" data-tab="<?php echo $inc;?>" role="tabpanel" aria-labelledby="elementor-tab-title-<?php echo $inc;?>" style="display: none;" hidden="hidden"><?php echo $toggleDescription;?></div>
                                                </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="read-more"><a href="<?php echo get_the_permalink();?>">Read more<i class="fas fa-chevron-right"></i></a></div>
				</div>
				<?php }

				if( $show_tags && $tags ) { ?>
				<div class="psacp-post-meta psacp-post-meta-down"><?php echo wp_kses_post( $tags ); ?></div>
				<?php } ?>
			</div>
		</div>

		<div class="psacp-col-right psacp-col-xl-6 psacp-columns psacp-col-right-img">	
			<a class="psacp-post-linkoverlay" href="<?php echo esc_url( $post_link ); ?>" target="<?php echo esc_attr( $link_behaviour ); ?>">Read more</a>
			<div class="psacp-post-img-bg <?php echo esc_attr( $lazy_cls ); ?>" style="<?php //echo esc_attr( $image_style ); ?>" <?php if( $lazyload && $feat_img ) { echo 'data-src="'.esc_url( $feat_img ).'"'; } ?>>
				<?php if( $format == 'video' ) { echo psacp_post_format_html( $format ); } // WPCS: XSS ok. ?>
				<?php $featured_img_url = get_the_post_thumbnail_url($post->ID,'full'); ?>
				<img alt="" src="<?php echo $featured_img_url;?>">
			</div>
		</div>
	</div>
</div>