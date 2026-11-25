<?php
declare(strict_types=1);

namespace Aura\Di\ClassScanner;

use Aura\Di\Resolver\Reflector;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\ClassMapGenerator\FileList;

final class ComposerMapGenerator implements MapGeneratorInterface
{
    private array $paths;
    private string $basePath;
    private ?string $excluded;
    private Reflector $reflector;

    public function __construct(
        array $paths,
        string $basePath = '',
        ?string $excluded = null,
    ) {
        $this->paths = $paths;
        $this->basePath = $basePath;
        $this->excluded = $excluded;
        $this->reflector = new Reflector();
    }

    public function generate(): ClassMap
    {
        $generator = new ClassMapGenerator();
        $generator->avoidDuplicateScans();

        foreach ($this->paths as $path) {
            $generator->scanPaths($path, $this->excluded);
        }

        return $this->convertToClassMap(
            new ClassMap(
                $this->paths,
                $this->basePath
            ),
            $generator->getClassMap()->getMap()
        );
    }

    private function convertToClassMap(ClassMap $classMap, array $composerMap): ClassMap
    {
        $basePath = \str_replace('\\', '/', $this->basePath);
        foreach ($composerMap as $class => $path) {
            $path = \str_replace('\\', '/', $path);
            if ($basePath !== '' && \str_starts_with($path, $basePath)) {
                $path = \substr($path, \strlen($basePath));
            }

            try {
                $attributes = [...$this->reflector->yieldAttributes($class)];
                $classMap->addClass(new ClassSpecification($class, $path, $attributes));
            } catch (\Error) {
            }
        }

        return $classMap;
    }

    public function update(ClassMap $classMap, array $updatedFiles): ClassMap
    {
        $deleted = [];
        $skip = [];

        $shouldFullGenerate = false;
        $basePath = \str_replace('\\', '/', $this->basePath);
        foreach ($updatedFiles as $index => $updatedFile) {
            $normalizedFile = \str_replace('\\', '/', $updatedFile);
            if ($basePath !== '' && \str_starts_with($normalizedFile, $basePath)) {
                $checkFile = \substr($normalizedFile, \strlen($basePath));

                if (!\is_file($updatedFile)) {
                    $deleted[] = $checkFile;
                } else {
                    $shouldFullGenerate = $shouldFullGenerate || $classMap->isAttributeClassFile($checkFile);
                }

                $updatedFiles[$index] = $checkFile;
            } else {
                $updatedFiles[$index] = $normalizedFile;
            }
        }

        if ($shouldFullGenerate) {
            return $this->generate();
        }

        foreach ($classMap->getFiles() as $file) {
            if (!\in_array($file, $updatedFiles, true)) {
                $skip[\str_replace('\\', '/', $this->basePath . $file)] = true;
            }
        }

        $fileList = new FileList();
        $fileList->files = $skip;

        $generator = new ClassMapGenerator();
        $generator->avoidDuplicateScans($fileList);

        foreach ($this->paths as $path) {
            $generator->scanPaths($path, $this->excluded);
        }

        $classMap = $classMap->merge(
            $this->convertToClassMap(
                new ClassMap(
                    $this->paths,
                    $this->basePath
                ),
                $generator->getClassMap()->getMap()
            )
        );

        foreach ($deleted as $filename) {
            $classMap->remove($filename);
        }

        return $classMap;
    }
}
