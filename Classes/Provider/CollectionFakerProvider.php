<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class CollectionFakerProvider implements FakerProviderInterface
{

    /**
     * @var array
     */
    protected array $options;

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * A doctrine array collection
     *
     * @param array $elements
     * @return ArrayCollection
     */
    public function arrayCollection(array $elements = []): ArrayCollection
    {
        return new ArrayCollection($elements);
    }
}
