<?php
namespace Aura\Di\Fake;

class FakeChildClassWithUnionTypeParam
{
    public \DateTimeImmutable $moment;

    public function __construct(\DateTimeImmutable|string $moment)
    {
        if (\is_string($moment)) {
            $moment = new \DateTimeImmutable($moment);
        }

        $this->moment = $moment;
    }
}
