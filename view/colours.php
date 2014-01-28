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
 * Displays input elements for color settings form.
 */
?>
		<tr>
			<td></td>
			<td><input type="hidden" name="tgt" id="tgt" value="backgroundColor" /></td>
		</tr>		
		<tr>
			<td><label for="backgroundColor">controlbar</label></td>
			<td style="text-align:right"><input class="color" type="text"  size="6" name="backgroundColor" id="backgroundColor" value="<?php echo $fv_fp->conf['backgroundColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="timeline">timeline</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="timelineColor" id="timelineColor" value="<?php echo $fv_fp->conf['timelineColor']; ?>" /></td>      
		</tr>		
		<tr>
			<td><label for="canvas">canvas</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="canvas" id="canvas" value="<?php echo $fv_fp->conf['canvas']; ?>" /></td>
			<td style="padding-left:20px;"><label for="progressColor">progress</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="progressColor" id="progressColor" value="<?php echo $fv_fp->conf['progressColor']; ?>" /></td>
      
		</tr>
		<tr>
			<td><label for="sliderColor">sliders</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="sliderColor" id="sliderColor" value="<?php echo $fv_fp->conf['sliderColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="bufferColor">buffer</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="bufferColor" id="bufferColor" value="<?php echo $fv_fp->conf['bufferColor']; ?>" /></td>
                  
		</tr>
		<tr>
			<td><label for="buttonColor">buttons</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="buttonColor" id="buttonColor" value="<?php echo $fv_fp->conf['buttonColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="timeColor">time</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="timeColor" id="timeColor" value="<?php echo $fv_fp->conf['timeColor']; ?>" /></td>            
		</tr>
		<tr>
			<td><label for="buttonOverColor">mouseover</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="buttonOverColor" id="buttonOverColor" value="<?php echo $fv_fp->conf['buttonOverColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="durationColor">total time</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="durationColor" id="durationColor" value="<?php echo $fv_fp->conf['durationColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="hasBorder">border</label></td>
			<td style="text-align:right"><?php fv_flowplayer_admin_checkbox('hasBorder'); ?></td>
			<td style="padding-left:20px;"><label for="durationColor">border color</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="borderColor" id="borderColor" value="<?php echo $fv_fp->conf['borderColor']; ?>" /></td>
		</tr>
		<tr>
			<td><label for="buttonOverColor">ad text</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="adTextColor" id="adTextColor" value="<?php echo $fv_fp->conf['adTextColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="durationColor">ad links</label></td>
			<td style="text-align:right"><input class="color" type="text" size="6" name="adLinksColor" id="adLinksColor" value="<?php echo $fv_fp->conf['adLinksColor']; ?>" /></td>
		</tr>		
