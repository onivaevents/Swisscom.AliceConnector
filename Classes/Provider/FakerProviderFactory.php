<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector\Provider;

use Faker\Generator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Swisscom\AliceConnector\Exception;

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

    /**
     * Creator factory that passes the generator as well as possible options to the provider. This allows creation of
     * extended "Base" fakers as well as own fakers with options defined in the Settings.yaml.
     *
     * @param array<string, mixed>|null $options
     */
    public function create(string $providerClassName, Generator $generator, ?array $options): FakerProviderInterface
    {
        /** @phpstan-ignore-next-line */
        $provider = $this->objectManager->get($providerClassName, $generator, $options);
        if (!$provider instanceof FakerProviderInterface) {
            throw new Exception(
                sprintf('Alice Connector faker providers should implement "%s"', FakerProviderInterface::class)
            );
        }

        return $provider;
    }
}
