<?php

declare(strict_types = 1);

namespace Helpers;

class Sanitize
{
    /**
     * Returns a safe filename, for a given platform (OS), by
     * replacing all dangerous characters with an underscore.
     *
     * @param string $dangerousFilename The source filename
     *                                  to be "sanitized"
     * @param string $platform          The target OS
     *
     * @return string A safe version of the input
     *                filename
     */
    public static function sanitizeFileName($dangerousFilename, $platform = 'Unix')
    {
        if (in_array(strtolower($platform), ['unix', 'linux'])) {
            // our list of "dangerous characters", add/remove
            // characters if necessary
            $dangerousCharacters = [' ', '"', "'", '&', '/', '\\', '?', '#'];
        } else {
            // no OS matched? return the original filename then...
            return $dangerousFilename;
        }

        // every forbidden character is replace by an underscore
        return str_replace($dangerousCharacters, '_', $dangerousFilename);
    }
}
