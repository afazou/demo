<?php
namespace Kefu\Lib;
class WordWrap
{
    public function wrap($string = '', $cols) {
        $string = trim($string);
        if (strlen($string) > $cols) {
            $lastSpaceIndex = strrpos(substr($string, 0, $cols), ' ');
            if ($lastSpaceIndex !== false && substr($string, $cols, 1) != ' ') {
                return substr($string, 0, $lastSpaceIndex) . "n" . $this->wrap(substr($string, $lastSpaceIndex), $cols);
            } else {
                return substr($string, 0, $cols) . "n" . $this->wrap(substr($string, $cols), $cols);
            }
        }

        return $string;
    }
}

