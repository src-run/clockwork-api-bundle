<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\ClockworkBundle\Exception;

/**
 * ClockworkAPIException class
 */
class ClockworkAPIException extends ClockworkException
{
    /**
     * @var null|integer
     */
    private $error_number;

    /**
     * @var null|string
     */
    private $error_message;

    /**
     * @param null|integer $error_number
     * @param null|string  $error_message
     */
    public function __construct($error_number = null, $error_message = null)
    {
        $this->error_number  = $error_number;
        $this->error_message = $error_message;

        parent::__construct('Clockwork API Exception '.$this->error_number.': '.$this->error_message);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return serialize([
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->error_number,
            $this->error_message,
        ]);
    }

    /**
     * @param string $string
     */
    public function unserialize($string)
    {
        list(
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
            $this->error_number,
            $this->error_message,
        ) = unserialize($string);
    }

    /**
     * @return string
     */
    public function getMessageKey()
    {
        return 'A clockwork exception occurred.';
    }

    /**
     * @return array
     */
    public function getMessageData()
    {
        return array();
    }
}