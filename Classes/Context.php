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
    public $fixtureLoader;

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
     * Constructor to set up the fixture context
     *
     * @param ObjectManagerInterface $objectManager Object manager for constructor injection
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->persistenceManager = $objectManager->get(PersistenceManagerInterface::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $fakerProviderFactory = $objectManager->get(FakerProviderFactory::class);

        $this->settings = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Swisscom.AliceConnector');

        $this->fixtureLoader = new NativeLoader();
        $this->faker = $this->fixtureLoader->getFakerGenerator();

        foreach ($this->settings['fakerProviders'] as $fakerProviderSetting) {
            $options = $fakerProviderSetting['options'] ?? [];
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
     * Load the fixture objects and persist them through the persistence manager
     *
     * @param string $fixtureName Name of the fixture to be loaded (Filename without extension)
     * @param string $fixtureSet Fixture set name. See the config to define different fixture sets.
     */
    public function loadFixture(string $fixtureName, string $fixtureSet = 'default'): void
    {
        $objects = $this->getFixtureObjectSet($fixtureName, $fixtureSet);
        $this->persist($objects);
    }

    /**
     * Load and return the fixtures without persisting
     *
     * @param string $fixtureName Name of the fixture to be loaded (Filename without extension)
     * @param string $fixtureSet Fixture set name. See the config to define different fixture sets.
     * @return ObjectSet
     */
    public function getFixtureObjectSet(string $fixtureName, string $fixtureSet = 'default'): ObjectSet
    {
        if (!isset($this->settings[$fixtureSet])) {
            throw new Exception(sprintf('No fixture set with name "%s" available.', $fixtureSet), 1614235658);
        }

        $path = str_replace($this->settings[$fixtureSet], '{name}', $fixtureName);
        if (!($realPath = realpath($path))) {
            throw new Exception(sprintf('No fixture found with path "%s".', $realPath), 1614235946);
        }

        return $this->fixtureLoader->loadFile($realPath);
    }

    private function persist(ObjectSet $objects)
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
