<?php
namespace Aura\Di\Fake;

use Aura\Di\Attribute\AttributeConfigFor;
use Aura\Di\Attribute\BlueprintNamespace;
use Aura\Di\ClassScanner\AttributeConfigInterface;
use Aura\Di\ClassScanner\AttributeSpecification;
use Aura\Di\ClassScanner\ClassSpecification;
use Aura\Di\Container;
use NotInstalled\External\Library\Attribute as NotInstalledAttrtibute;

#[\Attribute]
#[AttributeConfigFor(FakeWorkerAttribute::class)]
#[NotInstalledAttrtibute]
#[BlueprintNamespace(__NAMESPACE__)]
class FakeWorkerAttribute implements AttributeConfigInterface
{
    public const FILE = __FILE__;

    private int $someSetting;

    public function __construct(int $someSetting = 1)
    {
        $this->someSetting = $someSetting;
    }

    public static function define(Container $di, AttributeSpecification $attribute, ClassSpecification $class): void
    {
        /** @var self $instance */
        $instance = $attribute->getAttributeInstance();
        if ($attribute->getAttributeTarget() === \Attribute::TARGET_CLASS) {
            $di->values['worker'] = $di->values['worker'] ?? [];
            $di->values['worker'][] = [
                'someSetting' => $instance->someSetting,
                'className' => $attribute->getClassName(),
            ];
        }
    }
}
