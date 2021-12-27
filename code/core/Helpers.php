<?php

namespace WCPoczta\Code\Core;

/**
 * A set of useful shortcuts.
 *
 * @author    Leszek Pomianowski <kontakt@rapiddev.pl>
 * @copyright 2021 Leszek Pomianowski
 * @license   GPL-3.0 https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://dev.lepo.co/
 */
final class Helpers
{
  /**
   * Align the directory path
   *
   * There is a method that deal with Sven Arduwie proposal
   * @see https://www.php.net/manual/en/function.realpath.php#84012
   * And runeimp at gmail dot com proposal
   * @see https://www.php.net/manual/en/function.realpath.php#112367
   */
  public static function getAbsolutePath(string $path): string
  {
    // Cleaning path regarding OS
    if (!function_exists('mb_ereg_replace')) {
      $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
      $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
    } else {
      $path = mb_ereg_replace('\\\\|/', DIRECTORY_SEPARATOR, $path, 'msr');
    }

    // Check if path start with a separator (UNIX)
    $startWithSeparator = $path[0] === DIRECTORY_SEPARATOR;

    // Check if start with drive letter
    preg_match('/^[a-z]:/', $path, $matches);
    $startWithLetterDir = isset($matches[0]) ? $matches[0] : false;

    // Get and filter empty sub paths
    $subPaths = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'mb_strlen');

    $absolutes = [];
    foreach ($subPaths as $subPath) {
      if ('.' === $subPath) {
        continue;
      }
      // if $startWithSeparator is false
      // and $startWithLetterDir
      // and (absolutes is empty or all previous values are ..)
      // save absolute cause that's a relative and we can't deal with that and just forget that we want go up
      if (
        '..' === $subPath
        && !$startWithSeparator
        && !$startWithLetterDir
        && empty(array_filter($absolutes, function ($value) {
          return !('..' === $value);
        }))
      ) {
        $absolutes[] = $subPath;
        continue;
      }
      if ('..' === $subPath) {
        array_pop($absolutes);
        continue;
      }
      $absolutes[] = $subPath;
    }

    return (($startWithSeparator ? DIRECTORY_SEPARATOR : $startWithLetterDir) ?
      $startWithLetterDir . DIRECTORY_SEPARATOR : '') . implode(DIRECTORY_SEPARATOR, $absolutes);
  }

  /**
   * @see https://www.php.net/manual/en/function.realpath.php#123783
   */
  public static function resolvePath(?string $path): ?string
  {
    $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);

    $search = explode(DIRECTORY_SEPARATOR, $path);
    $search = array_filter($search, function ($part) {
      return $part !== '.';
    });

    $append = array();
    $match = false;

    while (count($search) > 0) {
      $match = realpath(implode(DIRECTORY_SEPARATOR, $search));
      if ($match !== false) {
        break;
      }
      array_unshift($append, array_pop($search));
    };
    if ($match === false) {
      $match = getcwd();
    }
    if (count($append) > 0) {
      $match .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $append);
    }
    return $match;
  }
}
