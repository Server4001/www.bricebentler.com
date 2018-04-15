<?php declare(strict_types=1);
/**
 * @category     Helpers
 * @package      BriceBentler.com
 * @copyright    Copyright (c) 2018 Bentler Design
 * @author       Brice Bentler <me@bricebentler.com>
 */

namespace BentlerDesign\Helpers;

use InvalidArgumentException;

class Template
{
    public static function render(string $templatePath, array $vars): string
    {
        if (!is_file($templatePath)) {
            throw new InvalidArgumentException("Invalid template path: '{$templatePath}'.");
        }

        extract($vars);

        ob_start();
        require($templatePath);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}
