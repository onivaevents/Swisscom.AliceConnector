<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector\Provider;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Document;
use Neos\Media\Domain\Model\Image;

/**
 * @Flow\Scope("singleton")
 */
class ResourceFakerProvider implements FakerProviderInterface
{

    /**
     * @var array
     */
    protected array $options;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * A persistent resource
     *
     * @param string $fileName
     * @return PersistentResource|null
     */
    public function persistentResource(string $fileName): ?PersistentResource
    {
        if ($this->options['persistenceEnabled'] === true) {
            return $this->resourceManager->importResource($this->options['fixturePath'] . $fileName);
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
}
