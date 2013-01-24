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
			<td style="text-align:right"><input class="color" type="text"  size="5" name="backgroundColor" id="backgroundColor" value="<?php echo $fp->conf['backgroundColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="timeline">timeline</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="timelineColor" id="timelineColor" value="<?php echo $fp->conf['timelineColor']; ?>" /></td>      
		</tr>		
		<tr>
			<td><label for="canvas">canvas</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="canvas" id="canvas" value="<?php echo $fp->conf['canvas']; ?>" /></td>
			<td style="padding-left:20px;"><label for="progressColor">progress</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="progressColor" id="progressColor" value="<?php echo $fp->conf['progressColor']; ?>" /></td>
      
		</tr>
		<tr>
			<td><label for="sliderColor">sliders</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="sliderColor" id="sliderColor" value="<?php echo $fp->conf['sliderColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="bufferColor">buffer</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="bufferColor" id="bufferColor" value="<?php echo $fp->conf['bufferColor']; ?>" /></td>
                  
		</tr>
		<tr>
			<td><label for="buttonColor">buttons</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="buttonColor" id="buttonColor" value="<?php echo $fp->conf['buttonColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="timeColor">time</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="timeColor" id="timeColor" value="<?php echo $fp->conf['timeColor']; ?>" /></td>            
		</tr>
		<tr>
			<td><label for="buttonOverColor">mouseover</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="buttonOverColor" id="buttonOverColor" value="<?php echo $fp->conf['buttonOverColor']; ?>" /></td>
			<td style="padding-left:20px;"><label for="durationColor">total time</label></td>
			<td style="text-align:right"><input class="color" type="text" size="5" name="durationColor" id="durationColor" value="<?php echo $fp->conf['durationColor']; ?>" /></td>
		</tr>