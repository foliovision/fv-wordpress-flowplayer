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

/**
 * Displays metatags for administrator backend.
 */
 
global $fv_wp_flowplayer_ver;
?>

<script type="text/javascript" src="<?php echo FV_FP_RELATIVE_PATH; ?>/js/jscolor/jscolor.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo flowplayer::get_plugin_url().'/css/license.css'; ?>?ver=<?php echo $fv_wp_flowplayer_ver; ?>" />