<?php

namespace Kematjaya\BackupBundle\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Kematjaya\BackupBundle\Exception\FactoryNotFoundException;
use Kematjaya\BackupBundle\Factory\FactoryInterface;

class FactoryBuilder implements FactoryBuilderInterface
{
    private readonly ArrayCollection $factories;

    public function __construct()
    {
        $this->factories = new ArrayCollection();
    }

    public function addFactory(FactoryInterface $factory): FactoryBuilderInterface
    {
        if (!$this->factories->contains($factory)) {
            $this->factories->add($factory);
        }

        return $this;
    }

    public function getAllFactories(): array
    {
        return $this->factories->toArray();
    }

    public function getFactory(string $name): FactoryInterface
    {
        $factories = $this->factories->filter(fn(FactoryInterface $factory): bool => $factory->getName() === $name);

        if ($factories->isEmpty()) {
            throw new FactoryNotFoundException($name);
        }

        return $factories->first();
    }

}
