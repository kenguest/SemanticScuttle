<?php
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

    public function extractFromUrl($url)
    {
        // will obviously need something better than this.
        // It's a start though!
        $tags = array();
        $parsed = parse_url($url);
        // stackexchange sites...
        if (stripos($parsed['host'], 'stackexchange.com') !== false) {
            $tags[] = str_replace(  '.stackexchange.com', '', $parsed['host']);
        }

        $hostArray = explode('.', $parsed['host']);
        $sub = strtolower($hostArray[0]);
        if (($sub == 'help') || ($sub == 'hilfe')) {
            $tags[] = $hostArray[1];
        }

        // check generator referenced in the source, e.g. if mediawiki then be aware of how it
        // links to used categories, determine what they are and suggest them as tags.

        // also check <meta name='keywords' content='foo,bar,qux'>
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
