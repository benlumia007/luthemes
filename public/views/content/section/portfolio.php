<?php
/**
 * Camaraderie ( home-portfolio.php )
 *
 * @package     Camaraderie
 * @copyright   Copyright (C) 2017-2020. Benjamin Lu
 * @license     GNU General Public License v2 or later ( https://www.gnu.org/licenses/gpl-2.0.html )
 * @author      Benjamin Lu ( https://benjlu.com )
 */
?>
<section id="portfolio" class="section-portfolio">
	<div class="content-area">
		<header class="portfolio-header">
			<h1 class="portfolio-title"><?php echo esc_html( get_theme_mod( 'custom_portfolio_title', 'Portfolio' ) ); ?></h1>
			<span class="portfolio-description"><?php echo esc_html( get_theme_mod( 'custom_portfolio_description', 'Some of my recent works!') ); ?></span>
		</header>
		<nav class="portfolio-tabs">
			<ul class="data-items">
				<?php
				$query = new WP_Query( [ 'post_type' => 'portfolio' ] );
				$items = [];

				while ( $query->have_posts() ) : $query->the_post();
					if ( has_post_thumbnail() ) {
						$data = wptexturize( wp_strip_all_tags( get_post( get_post_thumbnail_id() )->post_content ) );
						$words = explode( " ", $data );
						array_splice( $words, -1 );

						$items[] = '<li class="data-item" data-filter="' . strtolower( implode( " ", $words ) )  . '"><span class="title">' . implode( " ", $words ). '</span></li>';
					}
				endwhile;

				wp_reset_postdata();

				$items = array_unique( $items );
				asort( $items );

				echo '<li class="data-item active" data-filter="' . esc_html__( 'all' , 'succotash' ) . '"><span class="title">' . esc_html__( 'All', 'succotash' ) . '</span></li>';
				foreach ( $items as $item ) {
					echo $item;
				}
				?>
			</ul>
		</nav>
		<div class="portfolio-content">
			<ul class="portfolio-items">
				<?php
				$posts_per_page = get_theme_mod( 'custom_portfolio_items', 9 );
				$query          = new WP_Query( array(
					'post_type'      => 'portfolio',
					'posts_per_page' => $posts_per_page,
				) );

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) : $query->the_post();
						if ( has_post_thumbnail() ) {
							?>
							<?php
							$words = strtolower( wptexturize( wp_strip_all_tags( get_post( get_post_thumbnail_id() )->post_content ) ) );
							$type = explode( " ", $words );
							array_splice( $type, -1 );
							?>
							<li class="portfolio-item <?php echo implode( " ", $type );  ?>">
								<a href="<?php echo esc_url( get_permalink() ); ?>">
									<?php the_post_thumbnail( 'camaraderie-large-thumbnails' ); ?>
								</a>
								<div class="wp-caption">
									<h2 class="wp-caption-text"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php the_post_thumbnail_caption(); ?></a></h2>
									<span><?php echo wptexturize( wp_strip_all_tags( get_post( get_post_thumbnail_id() )->post_content ) ); // phpcs:ignore ?></span>
								</div>
							</li>
							<?php
						}
					endwhile;
				endif;
				wp_reset_postdata();
				?>
			</ul>
		</div>
	</div>
</section>
