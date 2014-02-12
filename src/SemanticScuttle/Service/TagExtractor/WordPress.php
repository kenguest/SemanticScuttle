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
 * SemanticScuttle_Service_TagExtractor_WordPress
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Ken Guest <kguest@php.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_TagExtractor_WordPress
extends SemanticScuttle_Service_TagExtractor_Basic
{
    /**
     * getTags
     *
     * @return array
     */
    public function getTags()
    {
        $content = $this->content;
        $tags = parent::getTags();

        $meta = $this->metaTags;
        if (isset($meta['wp-parsely_version'])
            && $meta['wp-parsely_version'] === "1.5"
            && isset($meta['parsely-page'])
        ) {
            $parsely = json_decode($meta['parsely-page']);
            if (isset($parsely->tags)) {
                $tags = array_merge($tags, $parsely->tags);
            }
        }
        return $tags;
    }
}

?>
