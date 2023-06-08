<?php
/**
 *
 */

namespace Succotash\Portfolio\Widget;

use Succotash\Portfolio\Widget\Themes\Component as Themes;
// use Succotash\Portfolio\Widget\Subjects\Component as Subjects;

use Backdrop\Contracts\Bootable;


class Component implements Bootable{

	public function theme_info() {
		register_widget( Themes::class );
	}

	public function boot() {

		add_action('widgets_init',  [ $this, 'theme_info'] );
		// add_action('widgets_init',  [ $this, 'portfolio_subjects'] );
	}
}
