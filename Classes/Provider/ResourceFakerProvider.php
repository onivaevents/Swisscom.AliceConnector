<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector\Provider;

use Faker\Generator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Document;
use Neos\Media\Domain\Model\Image;

/**
 * @Flow\Scope("prototype")
 */
class ResourceFakerProvider implements FakerProviderInterface
{

    /**
     * @var array{fixturePath: string, persistenceEnabled: bool}
     */
    protected array $options;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    public function __construct(Generator $generator, array $options)
    {
        $this->options = $options;
    }

    /**
     * A persistent resource
     *
     * @param string $fileName
     * @param bool $withoutPersistenceEnabled
     * @return PersistentResource|null
     */
    public function persistentResource(string $fileName, bool $withoutPersistenceEnabled = false): ?PersistentResource
    {
        if (substr($fileName, 0, 11) !== 'resource://') {
            $fileName = $this->options['fixturePath'] . $fileName;
        }

        if ($this->options['persistenceEnabled'] === true || $withoutPersistenceEnabled === true) {
            return $this->resourceManager->importResource($fileName);
        } else {
            return null;
        }
    }

    /**
     * Document asset with reference to a persistent resource.
     *
     * @param string $fileName
     * @return Document|null
     */
    public function persistentResourceDocument(string $fileName): ?Document
    {
        if ($resource = $this->persistentResource($fileName)) {
            $image = new Document($resource);
            $image->setTitle($fileName);

            return $image;
        }

        return null;
    }

    /**
     * Image asset with reference to a persistent resource.
     *
     * @param string $fileName
     * @return Image|null
     */
    public function persistentResourceImage(string $fileName): ?Image
    {
        if ($resource = $this->persistentResource($fileName)) {
            $image = new Image($resource);
            $image->setTitle($fileName);

            return $image;
        }

        return null;
    }

    /**
     * Content string from a resource.
     *
     * @param string $fileName
     * @return string|null
     */
    public function fileContent(string $fileName): ?string
    {
        $value = file_get_contents($fileName);

        return is_string($value) ? $value : null;
    }
}
