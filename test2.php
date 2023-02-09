<?php

//The FireDragon species implements the Reptile interface.
//When a ReptileEgg hatches, a new reptile will be created of the same species that laid the egg.
//An Exception is thrown if a ReptileEgg tries to hatch more than once.

interface Reptile
{
    public function layEgg(): ReptileEgg;
}

class FireDragon implements Reptile
{

    public function layEgg(): ReptileEgg
    {
        return new ReptileEgg(get_class($this));
    }
}

class ReptileEgg
{
    /**
     * @var bool
     */
    private bool $hasHatched = false;
    /**
     * @var string
     */
    private string $reptileType;

    /**
     * ReptileEgg constructor.
     * @param string $reptileType
     */
    public function __construct(string $reptileType)
    {
        $this->setReptileType($reptileType);
    }

    /**
     * @param string $reptileType
     */
    public function setReptileType(string $reptileType): void
    {
        $this->reptileType = $reptileType;
    }

    /**
     * @return string
     */
    public function getReptileType(): string
    {
        return $this->reptileType;
    }

    /**
     * @param bool $hasHatched
     */
    public function setHasHatched(bool $hasHatched): void
    {
        $this->hasHatched = $hasHatched;
    }

    /**
     * @return bool
     */
    public function getHasHatched(): bool
    {
        return $this->hasHatched;
    }

    public function hatch(): Reptile
    {
        if ($this->getHasHatched()) {
            throw new Exception("This egg has already hatched and cannot hatch again.");
        } else {
            $this->setHasHatched(true);
        }

        $reptile_type = $this->getReptileType();
        $reptile = $reptile_type;
        return new $reptile;

    }
}