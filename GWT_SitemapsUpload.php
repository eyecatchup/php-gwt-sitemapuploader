<?php
/*-- INTERFACE -*/

interface GWT_Client
{
    const Email = 'user@gmail.com';
    const Passwd = 'mysecretpassword';
    // Must have a trailing slash!
    const Website = 'http://www.domain.tld/';
}

/*-- CLASS -*/

class GWT_SitemapsUpload implements GWT_Client
{
  protected $GWT_Server, $Transactions, $Client_Auth;

  public function __construct ($sitemapArray=NULL) {
    $this->Client_Auth = FALSE;
    $this->GWT_Server = Array(
        'host' => 'https://www.google.com/',
        'path' => Array(
            'login' => 'accounts/ClientLogin',
            'feedSitemaps' => 'webmasters/tools/feeds/%s/sitemaps/')
    );
    $this->Transactions = Array();

    if (strlen(GWT_Client::Email) > 0 AND strlen(GWT_Client::Passwd) > 0) :
        self::login(GWT_Client::Email, GWT_Client::Passwd);
    endif;

    // If sitemaps array given to the constructor, post them.
    if (is_array($sitemapArray) AND sizeof($sitemapArray) > 0) :
        foreach ($sitemapArray AS $sitemap) {
            self::putSitemap(GWT_Client::Website, $sitemap);
        }
    endif;
  }

  public function login ($clientEmail, $clientPasswd) {
    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) :
        throw new Exception("Check your login email!");
        exit(0);
    elseif (!is_string($clientPasswd) OR strlen($clientPasswd)==0) :
        throw new Exception("Check your login password!");
        exit(0);
    else :
        if (TRUE !== self::_login($clientEmail, $clientPasswd) ) :
            throw new Exception("Login faild! Please check your login email/password.");
            exit(0);
        else :
            return TRUE;
        endif;
    endif;
  }

  public function putSitemap ($gwtWebsite, $sitemapUrl) {
    $clientAuth = $this->Client_Auth;
    if(FALSE === $clientAuth) :
        throw new Exception("You must login first!");
        exit(0);
    else :
        $response = self::_putSitemap ($gwtWebsite, $sitemapUrl);
        $this->Transactions[] = Array(
            'Sitemap' => $sitemapUrl,
            'Response' => $response
        );
        return $response;
    endif;
  }

  public function getTransactions() {
    return $this->Transactions;
  }

  public function urlencoding ($str) {
    return str_replace(".", "%2E", urlencode($str));
  }

  private function _login ($clientEmail, $clientPasswd) {
    $postData = Array(
        'accountType' => 'HOSTED_OR_GOOGLE',
        'Email' => $clientEmail,
        'Passwd' => $clientPasswd,
        'service' => 'sitemaps',
        'source' => 'GWT_SitemapUpload-0.1-php');
    // Before PHP version 5.2.0 and when the first char of $pass is an @ symbol, 
    // send data in CURLOPT_POSTFIELDS as urlencoded string.
    if ('@' === (string)$clientPasswd[0] || version_compare(PHP_VERSION, '5.2.0') < 0) {
        $postData = http_build_query($postData);
    }
    $requestPath = $this->GWT_Server['path']['login'];
    $response = self::CurlPost($requestPath, $postData);
    @preg_match('/Auth=(.*)/', $response, $match);
    if (FALSE !== $response AND isset($match[1])) :
        $this->Client_Auth = $match[1];
        return TRUE;
    else :
        return FALSE;
    endif;
  }

  private function _putSitemap ($gwtWebsite, $sitemapUrl) {
    $clientAuth = $this->Client_Auth;
    $httpHeader = Array(
        "Authorization: GoogleLogin auth=$clientAuth",
        'GData-Version: 2',
        'Content-type: application/atom+xml');
    $postData  = "<atom:entry xmlns:atom='http://www.w3.org/2005/Atom'>";
    $postData .=   "<atom:id>$sitemapUrl</atom:id>";
    $postData .=   "<atom:category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/webmasters/tools/2007#sitemap-regular'/>";
    $postData .=   "<wt:sitemap-type xmlns:wt='http://schemas.google.com/webmasters/tools/2007'>WEB</wt:sitemap-type>";
    $postData .= "</atom:entry>";
    $requestPath = sprintf($this->GWT_Server['path']['feedSitemaps'],
        self::urlencoding($gwtWebsite));
    return self::CurlPost($requestPath, $postData, $httpHeader);
  }

  private function CurlPost ($requestPath, $postData, $httpHeader=FALSE) {
    $postUrl = $this->GWT_Server['host'] . $requestPath;
    $ch = curl_init($postUrl);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    if (FALSE !== $httpHeader)
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return ($info['http_code'] > 201) ? FALSE : $response;
  }
}//eof
