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
        // Check generator referenced in the source, e.g. if mediawiki then
        // be aware of how it links to used categories, determine what they
        // are and suggest them as tags.

        // Also check <meta name='keywords' content='foo,bar,qux'>
        // <a href="foo" rel="tag">category</a>

        // common/favourite keywords.
        if (stripos($url, "ubuntu") !== false) {
            $tags[] = "ubuntu";
        } elseif (stripos($url, "voip") !== false) {
            $tags[] = "voip";
        } elseif (stripos($url, "magento") !== false) {
            $tags[] = "magento";
        }
        return array_unique($tags);
    }

}


?>
