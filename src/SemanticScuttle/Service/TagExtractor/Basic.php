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
 * SemanticScuttle_Service_TagExtractor_Basic
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Ken Guest <kguest@php.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_TagExtractor_Basic
{
    /**
     * @var content string content retrieved
     */
    protected $content = null;
    /**
     * @var metaTags Associated meta tags.
     */
    protected $metaTags = null;
    /**
     * @var url string URL to work with
     */
    protected $url = null;

    /**
     * Set the content of this resource.
     *
     * @param string $content Content of this resource.
     *
     * @return SemanticScuttle_Service_TagExtractor_Basic
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * setMetaTags
     *
     * @param mixed $metaTags Metatags for this resource
     *
     * @return SemanticScuttle_Service_TagExtractor_Basic
     */
    public function setMetaTags($metaTags)
    {
        $this->metaTags = $metaTags;
        return $this;
    }


    /**
     * Set the URL/Resource to work with.
     *
     * @param string $url URL
     *
     * @return SemanticScuttle_Service_TagExtractor_Basic
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Determine tags associated with given resource
     *
     * @return array
     */
    public function getTags()
    {

        $url = $this->url;
        $metaTags = $this->metaTags;
        $content = $this->content;

        $tags = array();

        $parsed = parse_url($url);
        /*
        // stackexchange sites...
        if (isset($parsed['host'])) {
            if (stripos($parsed['host'], 'stackexchange.com') !== false) {
                $tags[] = str_replace('.stackexchange.com', '', $parsed['host']);
            }

            $hostArray = explode('.', $parsed['host']);
            $sub = strtolower($hostArray[0]);
            if (($sub == 'help') || ($sub == 'hilfe')) {
                $tags[] = $hostArray[1];
            }
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
        */

        if ($metaTags !== false && !empty($metaTags)) {
            if (isset($metaTags['keywords'])) {
                $w = explode(",", $metaTags['keywords']);
                $tags = array_merge($w, $tags);
            }
        }
        if ($content !== null) {
            include 'php-mf2/Mf2/Parser.php';
            $mf2Parsed = Mf2\parse($content, $url);
            $rels = $mf2Parsed['rels'];
            if (is_array($rels) && isset($rels['tag'])) {
                foreach ($rels['tag'] as $tag) {
                    $tag = trim($tag, '/');
                    $temp = explode('/', $tag);
                    $tags[] = urldecode(array_pop($temp));
                }
            }
        }

        return $tags;
    }
}

?>
