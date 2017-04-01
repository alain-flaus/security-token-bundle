<?php

namespace Yokai\SecurityTokenBundle\Configuration;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class TokenConfigurationRegistry
{
    /**
     * @var TokenConfiguration[]
     */
    private $configurations;

    /**
     * @param TokenConfiguration[] $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = [];
        foreach ($configurations as $configuration) {
            $this->configurations[$configuration->getPurpose()] = $configuration;
        }
    }

    /**
     * @param string $purpose
     *
     * @return TokenConfiguration
     */
    public function get($purpose)
    {
        if (!isset($this->configurations[$purpose])) {
            throw new \RuntimeException;//todo
        }

        return $this->configurations[$purpose];
    }
}
