<?php

namespace Kematjaya\BackupBundle\Tests\Builder;

use Kematjaya\BackupBundle\Builder\FactoryBuilder;
use Kematjaya\BackupBundle\Exception\FactoryNotFoundException;
use Kematjaya\BackupBundle\Factory\FactoryInterface;
use PHPUnit\Framework\TestCase;
use Spatie\DbDumper\Databases\MySql;

class FactoryBuilderTest extends TestCase
{
    private function makeFactory(string $name): FactoryInterface
    {
        return new readonly class ($name) implements FactoryInterface {
            public function __construct(private readonly string $factoryName) {}

            public function create(): MySql
            {
                return MySql::create();
            }

            public function getName(): string
            {
                return $this->factoryName;
            }
        };
    }

    public function testAddAndGetFactory(): void
    {
        $builder = new FactoryBuilder();
        $factory = $this->makeFactory('mysql');

        $result = $builder->addFactory($factory);

        $this->assertSame($builder, $result);
        $this->assertSame($factory, $builder->getFactory('mysql'));
        $this->assertCount(1, $builder->getAllFactories());
    }

    public function testDuplicateFactoryIsIgnored(): void
    {
        $builder = new FactoryBuilder();
        $factory = $this->makeFactory('mysql');

        $builder->addFactory($factory);
        $builder->addFactory($factory);

        $this->assertCount(1, $builder->getAllFactories());
    }

    public function testUnknownFactoryThrows(): void
    {
        $builder = new FactoryBuilder();

        $this->expectException(FactoryNotFoundException::class);
        $builder->getFactory('nope');
    }
}
