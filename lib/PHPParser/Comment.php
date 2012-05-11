<?php

class PHPParser_Comment
{
    protected $text;

    /**
     * Constructs a comment node.
     *
     * @param string $text Comment text (including comment delimiters like /*)
     */
    public function __construct($text) {
        $this->text = $text;
    }

    /**
     * Gets the comment text.
     *
     * @return string The comment text (including comment delimiters like /*)
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Sets the comment text.
     *
     * @param string $text The comment text (including comment delimiters like /*)
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Gets the comment text.
     *
     * @return string The comment text (including comment delimiters like /*)
     */
    public function __toString() {
        return $this->text;
    }

    /**
     * Gets the reformatted comment text.
     *
     * "Reformatted" here means that we try to clean up the whitespace at the
     * starts of the lines. This is necessary because we receive the comments
     * without trailing whitespace on the first line, but with trailing whitespace
     * on all subsequent lines.
     *
     * @return mixed|string
     */
    public function getReformattedText() {
        $text = trim($this->text);
        if (false === strpos($text, "\n")) {
            // Single line comments don't need further processing
            return $text;
        } elseif (preg_match('((*BSR_ANYCRLF)(*ANYCRLF)^\N*(?:\R\s+\*\N*)+$)', $text)) {
            // Multi line comment of the type
            //
            //     /*
            //      * Some text.
            //      * Some more text.
            //      */
            //
            // is handled by replacing the whitespace sequences before the * by a single space
            return preg_replace('(^\s+\*)m', ' *', $this->text);
        } elseif (preg_match('((*BSR_ANYCRLF)^/\*\*?\s\R)', $text)
                  && preg_match('((*BSR_ANYCRLF)\R(\s*)\*/$)', $text, $matches)
        ) {
            // Multi line comment of the type
            //
            //    /*
            //        Some text.
            //        Some more text.
            //    */
            //
            // is handled by removing the whitespace sequence on the line before the closing
            // */ on all lines. So if the last line is "    */", then "    " is removed at the
            // start of all lines.
            return preg_replace('(^' . preg_quote($matches[1]) . ')m', '', $text);
        } elseif (preg_match('(^/\*\*?\s*(?!\s))', $text, $matches)) {
            // Multi line comment of the type
            //
            //     /* Some text.
            //        Some more text.
            //        Even more text. */
            //
            // is handled by taking the length of the "/* " segment and leaving only that
            // many space characters before the lines. Thus in the above example only three
            // space characters are left at the start of every line.
            return preg_replace('(^\s*(?= {' . strlen($matches[0]) . '}(?!\s)))m', '', $text);
        }

        // No idea how to format this comment, so simply return as is
        return $text;
    }
}