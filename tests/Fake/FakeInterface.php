<?php
namespace Aura\Di\Fake;

interface FakeInterface
{
    public function setFoo($foo);
    public function getFoo();
    public function setMulti($arg1, $arg2, $arg3);
    public function getMulti();
}
