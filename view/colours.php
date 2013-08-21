<?php
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
							<td colspan="2" rowspan="10"  style="padding-left: 30px; vertical-align: top;">
								<div id="content">
									<div class="flowplayer is-splash"
									<?php if ($fv_fp->conf['engine'] == 'flash') echo 'data-engine="flash"'; ?>
									data-swf="<?php echo RELATIVE_PATH ?>/flowplayer/flowplayer.swf"
									data-ratio="0.417" 
									style="width:<?php echo $fv_fp->conf['width']; ?>px; max-height:<?php echo $fv_fp->conf['height']; ?>px;"
									<?php if ($fv_fp->conf['allowfullscreen'] == 'false') echo 'data-fullscreen="false"'; ?>
									<?php if (isset($fv_fp->conf['key']) && $fv_fp->conf['key'] != 'false' && strlen($fv_fp->conf['key']) > 0) {echo 'data-key="' . $fv_fp->conf['key'] . '"'; $commercial_key = true;} ?>
									<?php if ( isset($commercial_key) && isset($fv_fp->conf['logo']) && $fv_fp->conf['logo'] != 'false' && strlen($fv_fp->conf['logo']) > 0) echo ' data-logo="' . $fv_fp->conf['logo'] . '"'; ?>
									<?php if ($fv_fp->conf['scaling'] == "fit") echo 'data-flashfit="true"';; ?>
									>
										<video poster="http://foliovision.com/videos/example.jpg"<?php if (isset($fv_fp->conf['autoplay']) && $fv_fp->conf['autoplay'] == 'true') echo ' autoplay'; ?><?php if (isset($fv_fp->conf['auto_buffer']) && $fv_fp->conf['auto_buffer'] == 'true') echo ' preload'; ?>>
											<source src="http://foliovision.com/videos/example.mp4" type="video/mp4" />
										</video>
									</div>    
								</div>
							</td>			
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