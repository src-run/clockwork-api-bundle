<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\ClockworkBundle\Component;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Clockwork class
 */
class Clockwork implements ContainerAwareInterface
{
    /**
     * the base url for all api calls
     */
    const API_BASE_URL = 'api.clockworksms.com/xml/';

    /**
     * api method for authentication api calls
     */
    const API_AUTH_METHOD = 'authenticate';

    /**
     * api method for sms api calls
     */
    const API_SMS_METHOD = 'sms';

    /**
     * api method for checking message credit
     */
    const API_CREDIT_METHOD = 'credit';

    /**
     * api method for checking account balance
     */
    const API_BALANCE_METHOD = 'balance';

    /**
     * @var ContainerInterface
     */
    $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->setContainer($container);
    }

    /**
     * @param  ContainerInterface $container
     * @return Clockwork
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }
}