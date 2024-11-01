<?php 
	global $product;
?>
<div class="c4d-wcd__template">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-6">
				<div class="c4d-wcd__template_image">
					<?php echo woocommerce_show_product_loop_sale_flash(); ?>
					<?php woocommerce_template_loop_product_thumbnail(); ?>
					<div class="c4d-wcd__countdown">
						<?php echo do_shortcode('[c4d_wcd_clock id="'.esc_attr(get_the_ID()).'"]'); ?>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="c4d-wcd__template_info">
					<h2 class="title"><?php esc_html_e('Deal of the day', 'c4d-wcd'); ?></h2>
					<a href="<?php echo get_the_permalink(); ?>"><h3 class="product-title"><?php echo get_the_title(); ?></h3></a>
					<div class="rate">
						<?php 
							$rating_html = '';
							$rating = $product->get_average_rating();
							if ( $rating > 0 ) {
								$rating_html  .= '<div class="star-rating" title="' . sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating ) . '">';
								$rating_html .= '<span style="width:' . ( ( $rating / 5 ) * 100 ) . '%"></span>';
								$rating_html .= '</div>';

								$rating_html .= ' <a class="review-count" href="'.get_the_permalink().'#reviews">(';
								$rating_html .= $product->get_review_count() . ' ' . esc_html__('Reviews', 'c4d-woo-carousel');
								$rating_html .= ')</a>';
							}
							echo $rating_html;
						?>
					</div>
					<div class="desc">
						<?php the_excerpt(); ?>
					</div>
					<div class="price-cart clearfix">
						<div class="price-wrap">
							<?php echo woocommerce_template_loop_price(); ?>
						</div>
						<div class="cart">
							<?php echo woocommerce_template_loop_add_to_cart(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>