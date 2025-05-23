# Neos Flow Alice connector

Neos Flow package for expressive fixture generation based on [Alice](https://github.com/nelmio/alice).

The Swisscom.AliceConnector simply provides a Context that connects your functional test cases with Alice.

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
      default: '%FLOW_PATH_PACKAGES%Application/Your.Package/Tests/Fixtures/{name}.yaml'
```

### Fixture creation

Create your file based Alice fixtures under the path defined in ```fixtureSets```. 

This basic example stored as ``Company.yaml`` defines a single fixture object for the domain model ``Company``:

```yaml
Your\Package\Domain\Model\Company:
  company1:
    name: 'Swisscom'
```

For a more real case example and documentation reference about the notation go to "[What does Alice?](#What does Alice?)".

### Usage

Use the ``Context`` in your test and load the fixtures. ``$testablePersistenceEnabled`` should be enabled to work with persistence.

```php
<?php
namespace Your\Package\Tests\Functional;

use Swisscom\AliceConnector\Context;
use Neos\Flow\Tests\FunctionalTestCase;

class CompanyTest extends FunctionalTestCase
{

    /**
     * @var Context
     */
    protected $fixtureContext;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixtureContext = new Context($this->objectManager, $this::$testablePersistenceEnabled);
    }
    
    /**
     * @test
     */
    public function companyTest()
    {
        $fixtures = $this->fixtureContext->loadFixture('Company');
        $company = $fixtures['company1'];
        self::assertSame('Swisscom', $company->getName());
    }
}
```

If you have persistence enabled, you could also query the fixture through your repository:

```php
$companyRepository = $this->objectManager->get(CompanyRepository::class);
$company = $companyRepository->findOneByName('Swisscom');
```

This example loads the fixture defined in ```%FLOW_PATH_PACKAGES%/Application/Your.Package/Tests/Fixtures/Company.yaml'```

 

## What does Alice?

Alice allows you to define expressive fixtures based on YAML, JSON or PHP files. 
Alice again is relying on [Faker](https://github.com/FakerPHP/Faker) to create fake data for your fixtures.

The following sample shows you the real gain of Alice creating 10 random ``Person`` entities with account and related entities:
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

Content string from a resourcee:
```php
$string = $faker->fileContent('Dummy.txt');
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

## Notes

- Context and providers are all stored inside the ``Classes`` folder to make them available not only for test cases. 
  Possible use cases are fixture generation on demo environments or even master data imports on prod environments.
- For reference data import see the [Swisscom.ReferenceDataImport](https://github.com/onivaevents/Swisscom.ReferenceDataImport) implementation which is based on this package.
