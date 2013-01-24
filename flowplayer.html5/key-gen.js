
/*
   Unlimited key generation
*/
function generate_key(domain) {

   var sum1 = 0, sum2 = 0;

   for (var i = domain.length - 1; i >= 0; i--) {
      sum1 += domain.charCodeAt(i) * 53856224894;
      sum2 += domain.charCodeAt(i) * 42201833587;
   }

   return ("$" + sum1).substring(0, 8) + ("" + sum2).substring(0, 8);

}


/***** TEST: node key-gen.js *****/

// no subdomains or invalid TLDs
var domains = ["localhost", "fritzimages.com", "moot.co.uk"];

domains.forEach(function(domain) {
   console.info(domain + "\t" + generate_key(domain));
});

