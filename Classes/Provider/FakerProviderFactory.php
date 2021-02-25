<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector\Provider;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class FakerProviderFactory
{

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function create(string $providerClassName, array $options): FakerProviderInterface
    {
        /** @var FakerProviderInterface $provider */
        $provider = $this->objectManager->get($providerClassName);
        $provider->setOptions($options);

        return $provider;
    }
}
