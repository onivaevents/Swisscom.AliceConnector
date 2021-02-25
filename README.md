# Neos Flow Alice connector

Neos Flow package for expressive fixture generation based on [Alice](https://github.com/nelmio/alice).

The Swisscom.AliceConnector simply provides a Context that connects your test cases with Alice.

## Getting started

Install the package through composer. You likely require it for dev only.

```
composer require --dev swisscom/aliceconnector
```

### Configuration

Set the path to your fixture files in the ``Settings.yaml``:

```yaml
Swisscom:
  AliceConnector:
    fixtureSets:
      default: '%FLOW_PATH_PACKAGES%/Application/Your.Package/Tests/Fixtures/{name}.yaml'
```

### Fixture creation

Create your file based Alice fixtures under the path defined in ```fixtureSets```. 
See examples and documentation reference about the notation in [here](#What does Alice?).

### Usage

Use the ``Context`` in your test and load the fixtures. ``$testablePersistenceEnabled`` should be enabled to work with persistence.

```php
<?php
namespace Your\Package\Tests\Functional;

use Swisscom\AliceConnector\Context;
use Neos\Flow\Tests\FunctionalTestCase;
use Your\Package\Domain\Repository\CompanyRepository;

class CompanyTest extends FunctionalTestCase
{

    protected static $testablePersistenceEnabled = true;

    /**
     * @var Context
     */
    protected $fixtureContext;

    public function setUp()
    {
        parent::setUp();
        $this->fixtureContext = new Context($this->objectManager);
    }
    
    /**
     * @test
     */
    public function companyTest()
    {
        $this->fixtureContext->loadFixture('Company');
        
        $companyRepository = $this->objectManager->get(CompanyRepository::class);
        $company = $companyRepository->findOneByName('Swisscom');
        self::assertSame('Swisscom', $company->getName());
    }
}
```

If you want to work without persistence, you still can get the objects directly:
```php
$objectSet = $this->fixtureContext->getFixtureObjectSet('Company');
```

This example loads the fixture defined in ```%FLOW_PATH_PACKAGES%/Application/Your.Package/Tests/Fixtures/Company.yaml'```

 

## What does Alice?

Alice allows you to define expressive fixtures based on YAML, JSON or PHP files. 
Alice again is relying on [Faker](https://github.com/FakerPHP/Faker) to create fake data for your fixtures.

This basic example defines a single fixture object for the domain model ``Company`` with a static name:

```yaml
Your\Package\Domain\Model\Company:
  swisscom:
    name: 'Swisscom'
```

The following more complex sample shows you the real gain creating 10 random ``Person`` entities with account and related entities:
```yaml
Neos\Flow\Security\Account:
  account{1..10}:
    accountIdentifier: 'user<current()>'
    credentialsSource: <passwordHash('12345678')>
    authenticationProviderName: UsernamePasswordTestingProvider
    roles:
      - <role('Your.Package:User')>

Neos\Party\Domain\Model\PersonName:
  personName{1..10}:
    firstName: '<firstName()>'
    lastName: '<lastName()>'

Neos\Party\Domain\Model\ElectronicAddress:
  electronicAddress{1..10}:
    identifier (unique): '<email()>'
    type: Email

Neos\Party\Domain\Model\Person:
  person{1..10}:
    name: '@personName<current()>'
    primaryElectronicAddress: '@electronicAddress<current()>'
    accounts:
    - '@account<current()>'
```

We use fixture ranges ``{1..10}`` to create the 10 objects each, fixture references (e.g. ``@personName``) as well as 
faked data through faker formatters (e.g. ``firstName()``).

See the [complete Alice reference](https://github.com/nelmio/alice/blob/master/doc/complete-reference.md) for detailed information and more features.


## What does Faker?

Faker simply provides random dummy data for your fixtures by providing formatters which can be used in Alice. 
See the [available Faker formatters](https://fakerphp.github.io/formatters/) for complete reference.

The faker formatters are directly called in the fixture definition file.

Additionally, the ``Context`` exposes the faker generator through ``getFaker()``, so that formatters can be used even directly in your code.


### Custom Flow formatters

The package provides the following custom formatters for your Flow application.

#### Collection

A doctrine array collection:
```php
$collection = $faker->arrayCollection([$item1, $item2]);
```

#### Resource

A persistent resource:
```php
$resource = $faker->persistentResource('Dummy.txt');
```

Image asset with reference to a persistent resource:
```php
$image = $faker->persistentResourceImage('Dummy.png');
```

Document asset with reference to a persistent resource:
```php
$document = $faker->persistentResourceDocument('Dummy.pdf');
```

#### Security

Password hash for the given password:
```php
$image = $faker->hashPassword('12345678');
```

Role object for the identifier:
```php
$image = $faker->role('Your.Package:User');
```
