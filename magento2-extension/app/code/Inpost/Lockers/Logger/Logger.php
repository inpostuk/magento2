<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Logger;

class Logger extends \Monolog\Logger
{
    public function __construct($name, $handlers = [], $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
    }
}
