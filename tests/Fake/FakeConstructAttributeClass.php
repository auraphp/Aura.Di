<?php
namespace Aura\Di\Fake;

use Aura\Di\Attribute\Instance;
use Aura\Di\Attribute\Service;
use Aura\Di\Attribute\Value;
use NotInstalled\External\Library\Attribute as NotInstalledAttrtibute;

#[FakeWorkerAttribute(3)]
#[NotInstalledAttrtibute]
class FakeConstructAttributeClass
{
    private FakeInterface $fakeService;
    private FakeInvokableClass $fakeServiceGet;
    private string $string;

    public function __construct(
        #[Service('fake.service')]
        FakeInterface $fakeService,
        #[Service('fake.service', 'getFoo')]
        FakeInvokableClass $fakeServiceGet,
        #[Instance(FakeInterfaceClass2::class)]
        private FakeInterface $fakeInstance,
        #[Value('fake.value')]
        string $string,
    ) {
        $this->fakeService = $fakeService;
        $this->fakeServiceGet = $fakeServiceGet;
        $this->string = $string;
    }

    public function getFakeService(): FakeInterface
    {
        return $this->fakeService;
    }

    public function getFakeServiceGet(): FakeInvokableClass
    {
        return $this->fakeServiceGet;
    }

    public function getFakeInstance(): FakeInterface
    {
        return $this->fakeInstance;
    }

    public function getString(): string
    {
        return $this->string;
    }
}
