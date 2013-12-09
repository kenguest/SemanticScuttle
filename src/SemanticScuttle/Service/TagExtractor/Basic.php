<?php
class SemanticScuttle_Service_TagExtractor_Basic
{
    protected $content = null;
    protected $metaTags = null;
    protected $url = null;

    public function setContent($content) 
    {
        $this->content = $content;
        return $this;
    }

    public function setMetaTags($metaTags)
    {
        $this->metaTags = $metaTags;
        return $this;
    }


    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getTags()
    {

        $url = $this->url;
        $metaTags = $this->metaTags;
        $content = $this->content;

        $tags = array();

        $parsed = parse_url($url);
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

        if ($metaTags !== false && !empty($metaTags)) {
            if (isset($metaTags['keywords'])) {
                $w = explode(",", $metaTags['keywords']);
                $tags = array_merge($w, $tags);
            }
            if (isset($metaTags['description'])) {
                var_dump($metaTags['description']);
            }
        }
        if ($content !== null) {
            include "php-mf2/Mf2/Parser.php";
            $mf2Parsed = Mf2\parse($content, $url);
            var_dump ($mf2Parsed);
            $rels = $mf2Parsed['rels'];
            if (is_array($rels) && isset($rels['tag'])) {
                foreach($rels['tag'] as $tag) {
                    $tag = trim($tag, '/');
                    $temp = explode("/", $tag);
                    $tag = array_pop($temp);
                    $tags[] = $tag;
                }
            }
        }

        return $tags;
    }
}

?>
