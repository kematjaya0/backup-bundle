<?php

namespace Kematjaya\BackupBundle\Tests\Builder;

use Kematjaya\BackupBundle\Builder\FactoryBuilder;
use Kematjaya\BackupBundle\Exception\FactoryNotFoundException;
use Kematjaya\BackupBundle\Factory\FactoryInterface;
use PHPUnit\Framework\TestCase;

class FactoryBuilderTest extends TestCase
{
    private function makeFactory(string $name): FactoryInterface
    {
        return new class($name) implements FactoryInterface {
            private string $factoryName;

            public function __construct(string $name)
            {
                $this->factoryName = $name;
            }

            public function create(): \Spatie\DbDumper\DbDumper
            {
                return \Spatie\DbDumper\Databases\MySql::create();
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
