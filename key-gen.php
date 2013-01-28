<?php
function generate_key($domain) {
  $sum1 = 0;
  $sum2 = 0;
  for ($i = strlen($domain) - 1; $i >= 0; $i--) {
    $sum1 += ord(substr($domain, $i, 1)) * 53856224894;
    $sum2 += ord(substr($domain, $i, 1)) * 42201833587;
  }
  return substr("$" + $sum1, 0, 7) . substr("" + $sum2, 0, 8);
}
?>
