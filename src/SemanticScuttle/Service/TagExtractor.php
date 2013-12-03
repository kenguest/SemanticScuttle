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
                    // use mediawiki extractor ? :)
                    $class = "TagExtractor_MediaWiki";
                } else {
                    var_dump($metaTags['generator']);
                }
            }
        }
        $class = "SemanticScuttle_Service_" . $class;
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $file = str_replace('_', '/', $class) . '.php';
        include_once $file;
        echo "Extractor: $class<br/>\n";
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
        // will obviously need something better than this.
        // It's a start though!
        $tags = array();
        $parsed = parse_url($url);
        var_dump ($parsed);
        // stackexchange sites...
        if (stripos($parsed['host'], 'stackexchange.com') !== false) {
            $tags[] = str_replace('.stackexchange.com', '', $parsed['host']);
        }

        $hostArray = explode('.', $parsed['host']);
        $sub = strtolower($hostArray[0]);
        if (($sub == 'help') || ($sub == 'hilfe')) {
            $tags[] = $hostArray[1];
        }

        if (strpos($parsed['path'], "/questions/tagged/") === 0) {
            $tags[] = str_replace('+', ', ', substr($parsed['path'], 18));
        }
        if (strpos($parsed['path'], "/unanswered/tagged/") === 0) {
            $tags[] = str_replace('+', ', ', substr($parsed['path'], 19));
        }

        // common/favourite keywords.
        if (stripos($url, "ubuntu") !== false) {
            $tags[] = "ubuntu";
        } elseif (stripos($url, "voip") !== false) {
            $tags[] = "voip";
        } elseif (stripos($url, "magento") !== false) {
            $tags[] = "magento";
        }

        $metaTags = get_meta_tags($url);
        $extractor = $this->_getExtractor($metaTags);
        if (!empty($metaTags)) {
            if (isset($metaTags['keywords'])) {
                $w = explode(",", $metaTags['keywords']);
                var_dump ($w);
                $tags = array_merge($w, $tags);
            }
            if (isset($metaTags['description'])) {
                var_dump($metaTags['description']);
            }
        }
        // Check generator referenced in the source, e.g. if mediawiki then
        // be aware of how it links to used categories, determine what they
        // are and suggest them as tags.
        var_dump($url);
        $content = null;
        $mf2Parsed = null;

        if ($parsed['scheme'] !== 'https') {
            try {
                $content = $this->_getContent($url);
            } catch(Exception $ex) {
                $content = null;
            }
        }

        if ($content !== null) {
            include "php-mf2/Mf2/Parser.php";
            $mf2Parsed = Mf2\parse($content, $url);
            var_dump($mf2Parsed);
        }

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
