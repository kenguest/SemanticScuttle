<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * Extract recommended tags for new URL not yet saved as a bookmark.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Ken Guest <kguest@php.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

class SemanticScuttle_Service_TagExtractor
{

    /**
     * Client used for making HTTP requests
     *
     * @var HTTP_Request2
     */
    protected $httpClient;

    /**
     * Create simple HTTP_Request2 client/instance
     *
     * @return HTTP_Request2
     */
    private function _createClient()
    {
        include "HTTP/Request2.php";
        $client = new HTTP_Request2();
        return $client;
    }

    /**
     * Get content from some URL
     *
     * @param string $url URL to retrieve content from.
     *
     * @return string
     */
    private function _getContent($url)
    {
        if (is_file($url)) {
            return file_get_contents($url);
        }
        $content = null;
        if ($this->httpClient === null) {
            $client = $this->_createClient();
            $this->httpClient = $client;
        } else {
            $client = $this->httpClient;
        }
        $client->setUrl($url);
        $res = $client->send();
        $code = $res->getStatus();
        if ($code == 200) {
            $content = $res->getBody();
        }
        return $content;
    }

    private function _getExtractor($metaTags)
    {
        $class = "TagExtractor_Basic";
        if (!empty ($metaTags)) {
            if (isset($metaTags['generator'])) {
                if (stripos($metaTags['generator'], 'MediaWiki') !== false) {
                    $class = "TagExtractor_MediaWiki";
                } elseif (stripos($metaTags['generator'], 'WordPress') !== false) {
                    $class = "TagExtractor_WordPress";
                } else {
                    var_dump($metaTags['generator']);
                }
            }
        }
        $class = "SemanticScuttle_Service_" . $class;
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $file = str_replace('_', '/', $class) . '.php';
        include_once "SemanticScuttle/Service/TagExtractor/Basic.php";
        include_once $file;
        return new $class();
    }

    /**
     * getInstance
     *
     * @param mixed $db ...
     *
     * @return SemanticScuttle_Service_TagExtractor
     */
    public static function getInstance($db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($db);
        }
        return $instance;
    }

    /**
     * Extract/return tags based on passed URL.
     *
     * @param string $url URL being saved/bookmarked.
     *
     * @return array
     */
    public function extractFromUrl($url)
    {
        $tags = array();
        $parsed = parse_url($url);
        $mUrl = $url;
        if ($parsed['scheme'] === 'file') {
            $mUrl = $parsed['path'];
        }
        $metaTags = get_meta_tags($mUrl);

        $extractor = null;
        if ($metaTags !== false) {
            $extractor = $this->_getExtractor($metaTags);
        } else {
            $extractor = $this->_getExtractor('');
        }

        try {
            $content = $this->_getContent($mUrl);
        } catch(Exception $ex) {
            $content = null;
        }

        $extractor->setUrl($mUrl)->setContent($content)->setMetaTags($metaTags);
        $extracted = $extractor->getTags();
        $tags = array_merge($extracted, $tags);

        // Also check <meta name='keywords' content='foo,bar,qux'>
        // <a href="foo" rel="tag">category</a>

        return array_unique($tags);
    }

    /**
     * set HttpClient
     *
     * @param HTTP_Request2 $client Custom HTTP_Request2 instance
     *
     * @return SemanticScuttle_Service_TagExtractor Provide Fluent interface
     */
    public function setHttpClient(HTTP_Request2 $client)
    {
        $this->httpClient = $client;
        return $this;
    }

}
?>
