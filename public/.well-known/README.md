# Apple Pay domain verification

Apple Pay on the web only works on domains Apple has verified. PayPal handles
the Apple developer account side, but the file has to be served from here.

1. PayPal Dashboard → Payments → Apple Pay → register the domain
   (`everyonesparking.co.uk`, plus any staging domain that needs to take payments).
2. Download the association file PayPal gives you and save it in this directory as:

       apple-developer-merchantid-domain-association

   No file extension. Do not edit the contents.
3. Confirm it serves over HTTPS with a 200:

       curl -I https://<domain>/.well-known/apple-developer-merchantid-domain-association

4. Back in the PayPal dashboard, click Verify.

Until this is done, `paypal.Applepay().config()` returns `isEligible: false` and
the Apple Pay button on /checkout.php stays hidden — the PayPal buttons are
unaffected, so nothing breaks in the meantime.

Note: Apple Pay also requires Advanced Credit and Debit Card Payments to be
enabled on the PayPal merchant account.
