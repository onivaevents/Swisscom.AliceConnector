<?php
declare(strict_types=1);

namespace Swisscom\AliceConnector;

use Faker\Generator as Faker;
use Nelmio\Alice\Loader\NativeLoader;
use Nelmio\Alice\ObjectSet;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Swisscom\AliceConnector\Provider\FakerProviderFactory;


class Context
{

    /**
     * @var NativeLoader
     */
    protected $loader;

    /**
     * @var Faker
     */
    protected $faker;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $persistenceEnabled;

    /**
     * Constructor to set up the fixture context
     *
     * @param ObjectManagerInterface $objectManager Object manager for constructor injection
     * @param bool $persistenceEnabled Flag to enable persistence of objects
     */
    public function __construct(ObjectManagerInterface $objectManager, bool $persistenceEnabled = false)
    {
        $this->persistenceEnabled = $persistenceEnabled;

        $this->persistenceManager = $objectManager->get(PersistenceManagerInterface::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $fakerProviderFactory = $objectManager->get(FakerProviderFactory::class);

        $this->settings = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Swisscom.AliceConnector');

        $this->loader = new NativeLoader();
        $this->faker = $this->loader->getFakerGenerator();

        foreach ($this->settings['fakerProviders'] as $fakerProviderSetting) {
            $options = $fakerProviderSetting['options'] ?? [];
            $options['persistenceEnabled'] = $persistenceEnabled;
            $provider = $fakerProviderFactory->create($fakerProviderSetting['provider'], $options);
            $this->faker->addProvider($provider);
        }
    }

    /**
     * Faker generator getter to directly call available formatters, i.e. $this->getFaker()->word
     *
     * @return Faker
     */
    public function getFaker(): Faker
    {
        return $this->faker;
    }

    /**
     * Load the fixture objects and persist them through the persistence manager if enabled.
     *
     * @param string $fixtureName Name of the fixture to be loaded (Filename without extension)
     * @param string $fixtureSet Fixture set name. See the config to define different fixture sets.
     * @param array $parameters Alice fixture parameters
     * @return array The loaded objects with object ID as key
     * @throws Exception
     */
    public function loadFixture(string $fixtureName, string $fixtureSet = 'default', array $parameters = []): array
    {
        $objectSet = $this->getFixtureObjectSet($fixtureName, $fixtureSet, $parameters);

        if ($this->persistenceEnabled) {
            $this->persist($objectSet);
        }

        return $objectSet->getObjects();
    }

    protected function getFixtureObjectSet(string $fixtureName, string $fixtureSet, array $parameters): ObjectSet
    {
        if (!isset($this->settings['fixtureSets'][$fixtureSet])) {
            throw new Exception(sprintf('No fixture set with name "%s" available.', $fixtureSet), 1614235658);
        }

        $path = str_replace('{name}', $fixtureName, $this->settings['fixtureSets'][$fixtureSet]);
        if (!($realPath = realpath($path))) {
            throw new Exception(sprintf('No fixture found with path "%s".', $path), 1614235946);
        }

        return $this->loader->loadFile($realPath, $parameters);
    }

    protected function persist(ObjectSet $objects)
    {
        foreach ($objects->getObjects() as $object) {
            if ($this->persistenceManager->isNewObject($object)) {
                $this->persistenceManager->add($object);
            } else {
                $this->persistenceManager->update($object);
            }
        }

        $this->persistenceManager->persistAll();
    }
}
