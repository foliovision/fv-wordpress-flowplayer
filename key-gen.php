<?php
function generate_key($domain) {
  $sum1 = 0;
  $sum2 = 0;
  for ($i = strlen($domain) - 1; $i >= 0; $i--) {
    $sum1 += ord(substr($domain, $i, 1)) * 7885412838;
    $sum2 += ord(substr($domain, $i, 1)) * 3036819511;
  }
  
  $sum1 = sprintf('%.2f', $sum1);
  $sum2 = sprintf('%.0f', $sum2);  
  return "$" . substr($sum1, 0, 7) . substr($sum2, 0, 8);
}

echo generate_key(str_replace('www.', '', $_SERVER['SERVER_NAME']));

?>
