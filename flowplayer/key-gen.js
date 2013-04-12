
/*
   Unlimited key generation
*/
function generate_key(domain) {

   var sum1 = 0, sum2 = 0;

   for (var i = domain.length - 1; i >= 0; i--) {
      sum1 += domain.charCodeAt(i) * 7885412838;
      sum2 += domain.charCodeAt(i) * 3036819511;
   }

   return ("$" + sum1).substring(0, 8) + ("" + sum2).substring(0, 8);

}

function getprog() {
    var path = require("path");
    return path.basename(process.argv[1]);
}

function usage() {
   process.stderr.write("Usage: " + process.argv[0] + " " +
      getprog() + " [-f|--file FILE] domain [domain ...]\n");
}

function help() {
   usage();
   process.stderr.write("\n" +
      "Generate keys for your Flowplayer Unlimited license.\n" +
      "Top Level Domain names only, no subdomains!\n\n" +
      "Options:\n" +
      "  -h, --help\t\tshow this help message and exit\n" +
      "  -f DOMFILE, --file DOMFILE\n" +
      "\t\t\tread domain names from FILE\n");

   process.exit(1);
}

function bail(err) {
   process.stderr.write(getprog() + ": abort: " + err + "\n\n");
   usage();
   process.exit(1);
}


var args = process.argv.slice(2),
    fs = require("fs"),
    expectfile = "";
    domains = [];

args.forEach(function (arg) {
   if (/-h|--help/.test(arg)) {
       help();
   } else if (/-f|--file/.test(arg)) {
       expectfile = arg;
   } else if (expectfile) {
      try {
         fs.readFileSync(arg, "utf8").split(/[\s]+/).forEach(function (dom) {
            if (dom) domains.push(dom);
         });
      } catch (err) {
         bail(err);
      }
      expectfile = "";
   } else {
      domains.push(arg);
   }
});

if (expectfile) bail("option " + expectfile + " requires argument");

if (!domains.length) bail("no domain names given");

domains.forEach(function (domain) {
   console.info(domain + "\t" + generate_key(domain));
});

