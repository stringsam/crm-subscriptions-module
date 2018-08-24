<?php

namespace Crm\SubscriptionsModule\Extension;

class ExtensionMethodFactory
{
    /** @var array(ExtensionInterface) */
    private $extensions = [];

    public function registerExtension($type, ExtensionInterface $extension)
    {
        $this->extensions[$type] = $extension;
    }

    /**
     * @param string $type
     * @return ExtensionInterface
     * @throws \Exception
     */
    public function getExtension($type)
    {
        if (isset($this->extensions[$type])) {
            return $this->extensions[$type];
        }
        throw new \Exception("Unknown extension type '{$type}'");
    }
}
