<div class="col-xs-12 col-sm-4">
<?php
		$categories = get_the_terms(get_the_ID(), 'category');
		$termName = $categories[0]->name;
		if ($categories[0]->term_id && $termName !== 'Uncategorized') {
			$categoryId = $categories[0]->term_id;
			$termName = $categories[0]->name;
			$termlink = get_term_link($categoryId, 'category');
		}
		?>
         <div class="main-article-recent">
        <article class="recent-articles" id="post-<?php echo get_the_ID(); ?>">
        <?php
//$images = vt_image_resize( '', $image, 700, 350, true );
$images = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), array('700','350'), true );
$image_id = get_post_thumbnail_id(get_the_ID());
$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', TRUE);
$image_title = get_the_title($image_id);
		?>
			<div class="cat_loop_img recent-post">
                <div class="blog-feature-img"><img src="<?php echo $images[0]; ?>" alt="<?php echo $image_alt ? $image_alt : $image_title;?>"></div>
             </div>
			<div class="entry-meta my-4">
        <?php
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if (get_the_time('U') !== get_the_modified_time('U')) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
		}
		$time_string = sprintf($time_string,
			esc_attr(get_the_date('c')),
			esc_html(get_the_date()),
			esc_attr(get_the_modified_date('c')),
			esc_html(get_the_modified_date())
		);
if(isset($termName) && !empty($termlink)){
$finalCatLink = '| <a class="post-cat" href="' . $termlink . '">' . $termName . '</a>';
}else{
$finalCatLink = '';
}
echo '<p>' . $posted_on = sprintf(
			esc_html_x('%s', 'post date', 'understrap'),
			'' . $time_string . ''
		) . ' '.$finalCatLink.'</p>';
		?>

			</div>
        <h4 class="entry-title recent"><a href="<?php echo get_permalink(); ?>" rel="bookmark"><?php echo get_the_title() ?></a></h4>
			<div class="entry-content hide-readmore">
            <p><?php echo pragma_excerpt_length(20); ?></p>
        </div>
      </article>
      </div>
</div>