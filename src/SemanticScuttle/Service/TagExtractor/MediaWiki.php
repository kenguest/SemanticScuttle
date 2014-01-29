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
 * SemanticScuttle_Service_TagExtractor_MediaWiki
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Ken Guest <kguest@php.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_TagExtractor_MediaWiki
extends SemanticScuttle_Service_TagExtractor_Basic
{
    const MAX_WORDS = 2;

    /**
     * getTags
     *
     * @return void
     */
    public function getTags()
    {
        $content = $this->content;
        $tags = parent::getTags();
        $start = 0;
        $categories = array();
        do {
            if ($sPos = strpos($content, '"Category:', $start)) {
                $ePos = strpos($content, '"', $sPos + 1);
                $cat = substr($content, $sPos + 1, $ePos - $sPos - 1);
                $temp = explode(':', $cat);
                $category = strtolower($temp[1]);
                $para = strpos($category, ' (');
                if ($para !== false) {
                    $category = substr($category, 0, $para - 1);
                }
                $category = trim($category);
                // Ignore category if there are too many words in its name.
                if (substr_count($category, ' ') <= (self::MAX_WORDS - 1)) {
                    $categories[] = $category;
                }
                $start = $ePos;
            }
        } while ($sPos !== false);
        return array_merge($tags, $categories);
    }
}

?>
