<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Common;

use UnexpectedValueException;

use function sprintf;

class ChangelogEntry
{
    /** @var string */
    private $contents = '';

    /** @var null|int */
    private $index;

    /** @var int */
    private $length = 0;

    public function __get(string $name)
    {
        switch ($name) {
            case 'contents':
                return $this->contents;
            case 'index':
                return $this->index;
            case 'length':
                return $this->length;
            default:
                throw new UnexpectedValueException(sprintf(
                    'The property "%s" does not exist for class "%s"',
                    $name,
                    static::class
                ));
        }
    }

    public function __set(string $name, $value)
    {
        switch ($name) {
            case 'contents':
                $this->setContents($value);
                break;
            case 'index':
                $this->setIndex($value);
                break;
            case 'length':
                $this->setLength($value);
                break;
            default:
                throw new UnexpectedValueException(sprintf(
                    'The property "%s" does not exist for class "%s"',
                    $name,
                    static::class
                ));
        }
    }

    private function setContents(string $value) : void
    {
        $this->contents = $value;
    }

    private function setIndex(?int $value) : void
    {
        $this->index = $value;
    }

    private function setLength(int $value) : void
    {
        $this->length = $value;
    }
}
