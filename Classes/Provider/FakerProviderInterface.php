<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector\Provider;

interface FakerProviderInterface
{

    public function setOptions(array $options): void;

}