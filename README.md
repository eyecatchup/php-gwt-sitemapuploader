# GWT_SitemapsUpload: Upload XML Sitemaps to Google Webmaster Tools

With GWT_SitemapsUpload you can upload a bunch of XML Sitemaps to your Google Webmaster Tools Account at once.

## Usage

### Interface
For the ease of use, set your account using the class interface `GWT_Client`
```php
<?php
interface GWT_Client
{
    const Email = 'user@gmail.com';
    const Passwd = 'mysecretpassword';
    // Must have a trailing slash!
    const Website = 'http://www.domain.tld/';
}
```

### Example Usage
```php
<?php
try {
  /* Sitemaps to submit to Google Webmaster Tools.
   */
  $sitemaps = Array(
    'http://www.domain.tld/sitemaps/sitemap-1.xml',
    'http://www.domain.tld/sitemaps/sitemap-2.xml',
    'http://www.domain.tld/sitemaps/sitemap-3.xml',
    'http://www.domain.tld/sitemaps/sitemap-4.xml',
    'http://www.domain.tld/sitemaps/sitemap-5.xml',
    'http://www.domain.tld/sitemaps/sitemap-6.xml',
    'http://www.domain.tld/sitemaps/sitemap-7.xml',
    'http://www.domain.tld/sitemaps/sitemap-8.xml',
    'http://www.domain.tld/sitemaps/sitemap-9.xml',
    'http://www.domain.tld/sitemaps/sitemap-10.xml'
  );

  /** Example 1
   *  Just upload (assumes login via interface)
   */
    new GWT_SitemapsUpload($sitemaps); # Boom - that's it! :)


  /** Example 2
   *  Upload and Feedback (assumes login via interface)
   */
    $GWT = new GWT_SitemapsUpload($sitemaps);

    $transactions = $GWT->getTransactions();
    foreach ($transactions AS $request) {
        $sitemap = $request['Sitemap'];
        $response = $request['Response'];
        print "HTTP response for submit request of $sitemap: $response\n";
    }


  /** Example 3
   *  Login on create
   */
    $email   = 'user@gmail.com';
    $passwd  = 'mysecretpassword';
    $website = 'http://www.domain.tld/'; # Must have a trailing slash!

    $GWT = new GWT_SitemapsUpload();

    if ($GWT->login($email, $passwd) === TRUE) :
        foreach ($sitemaps AS $sitemap) {
            // Upload a new standard XML sitemap (Type "WEB")
            $response = $GWT->putSitemap($website, $sitemap);
            print "HTTP response for submit request of $sitemap: $response\n";
        }
    endif;

} catch (Exception $e) {
  die($e->getMessage());
}
```