<?php

namespace App\Entity;

use \DateTimeInterface;

interface TimestampableEntityInterface
{

    public function getCreated(): ?DateTimeInterface;

    public function setCreated(DateTimeInterface $created): self;

    public function getChanged(): ?DateTimeInterface;

    public function setChanged(DateTimeInterface $changed): self;
}
