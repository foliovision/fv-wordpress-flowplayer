<?php
/*  FV Wordpress Flowplayer - HTML5 video player with Flash fallback    
    Copyright (C) 2013  Foliovision

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/ 

global $fv_wp_flowplayer_ver;

if( is_admin() ) : ?>
<script type="text/javascript" src="<?php echo FV_FP_RELATIVE_PATH ?>/flowplayer/fv-flowplayer.min.js?ver=<?php echo $fv_wp_flowplayer_ver; ?>"></script>
<?php endif; ?>

<?php echo $this->css_get(); ?>

<?php if( current_user_can('manage_options') ) : ?>
	<link rel="stylesheet" href="<?php echo FV_FP_RELATIVE_PATH; ?>/css/admin.css?ver=<?php echo $fv_wp_flowplayer_ver; ?>" type="text/css" media="screen" />
	<style type="text/css">
	.fv-wp-flowplayer-notice-small { color: <?php echo trim($this->conf['timeColor']); ?> !important; }
	</style>
<?php endif;