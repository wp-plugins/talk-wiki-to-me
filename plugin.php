<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////

class talkwikitome {

    /*
     * When talkwikitome is created, down below in this file, the constructor registers the function:
     * talkwikitomeTranslate to the content rendering, allowing for the plugin
     * to perform the translation of the mundane bracket/tag to the url
     */
    function talkwikitome() {
        add_filter('the_content', array(&$this, 'talkwikitomeTranslate'));
    }

    /*
     * Utilizes the wordpress database global: $wpdb
     * http://codex.wordpress.org/Function_Reference/wpdb_Class
     *
     * The format: [BRACKETSTYLE]TAG|TERM|TEXT[BRACKETSTYLE]
     *
     * Based on the bracket style specified, the system looks through
     * the content to find the formated text.  Then attempts to look up
     * the link information based on the TAG used.
     *
     * Then appends the TERM to end of the url in the database.  Utilizes
     * all the other specified settings.
     *
     * The link will appear with the TEXT specified in the anchor:
     * <a href=""...>TEXT</a>
     *
     */
    function talkwikitomeTranslate($content) {
        global $wpdb;

        // TODO: This should simply be a global variable.
        $db_linktable = $wpdb->prefix . "talkwikitome_links";

        // Load the style of bracketing from the database
        $talkwikitome_brackets = get_option("talkwikitome_brackets");

        switch ($talkwikitome_brackets) {
            case "1":
                $pattern = "!\[\[([^\|]+)\|([^\|]+)\|([^\]]+)\]\]!isU";
                break;
            case "2":
                $pattern = "!\(\(([^\)]*)\|(.*)\|(.*)\)\)!isU";
                break;
            case "3":
                $pattern = "!\{\{([^\}]*)\|(.*)\|(.*)\}\}!isU";
                break;
            default:
                $pattern = "!\[\[([^\]]*)\|(.*)\|(.*)\]\]!isU";
                break;
        }

        // Look to see if the pattern is a match: (url)|(term)|(text)

        $match = array();
        $position = 0;

        // Continue to look through the content for all the matchnes, moving forward
        //  and keeping track of the position...
        while (($position = preg_match($pattern, $content, $match, 0, $position))) {

            $tag = $wpdb->escape( $match[1] );
            $term = $match[2];
            $text = $match[3];

            $link = $wpdb->get_row ( "SELECT * FROM $db_linktable WHERE tag='$tag' " );
            
            // TODO: Allow a way to designate the search term within the url
            $url = $link->url . $term;

            $anchor = "<a href=\"$url\" ";
            
            if ( $link->follow == 1 )  {
                $anchor .= " nofollow ";
            }

            if ( $link->window == 0 )  {
                $anchor .= "target=\"_blank\" ";
            } else {
                $anchor .= "target=\"_top\" ";
            }

            if ( $link->alt_attribute == 0 )  {
                $anchor .= "alt=\"$url\" ";
            }

            if ( $link->title_attribute == 0 )  {
                $anchor .= "title=\"$url\" ";
            }

            if ( $link->style_override != null )  {
                $anchor .= "style=\"" . $link->style_override . "\" ";
            }

            $content = str_replace($match[0],$anchor . ">$text</a>",$content);
        }

        return $content;
    }

}


// Creates and registers the talkwikitome with wordpress
$sign &= new talkwikitome();

////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
