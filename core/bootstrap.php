<?php

namespace PCo\Base\Core;
use PCo\Base\Admin;
use PCo\Base\Frontend;

class Bootstrap {
	public function bootstrap() {
		if ( is_admin() ) {
			$admin = new Admin;
			$admin->main();
		}
	}
}
