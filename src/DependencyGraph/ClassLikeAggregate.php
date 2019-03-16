<?php
declare(strict_types=1);

namespace DependencyAnalyzer\DependencyGraph;

class ClassLikeAggregate implements \Countable
{
    /**
     * @var ClassLike[]
     */
    private $classLikes;

    public function __construct(array $classLikes = [])
    {
        $this->classLikes = $classLikes;
    }

    /**
     * @return ClassLike[]
     */
    public function getClassLikes(): array
    {
        return $this->classLikes;
    }

    public function haveClassLike(string $name): bool
    {
        foreach ($this->classLikes as $classLike) {
            if ($classLike->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    public function getClassLike(string $name): ?ClassLike
    {
        foreach ($this->classLikes as $classLike) {
            if ($classLike->getName() === $name) {
                return $classLike;
            }
        }

        return null;
    }

    public function merge(ClassLikeAggregate $aggregate): void
    {
        foreach ($aggregate->getClassLikes() as $classLike) {
            if ($this->haveClassLike($classLike->getName())) {
                foreach ($classLike->getDependees() as $dependee) {
                    $this->getClassLike($classLike->getName())->addDependee($dependee);
                }
            } else {
                $this->classLikes[] = $classLike;
            }
        }
    }

    public function toArray()
    {
        $array = [];
        foreach ($this->classLikes as $classLike) {
            foreach ($classLike->getDependees() as $dependee) {
                $array[$classLike->getName()][] = $dependee->getDisplayName();
            }
        }

        return $array;
    }

    public function count(): int
    {
        return count($this->classLikes);
    }
}
