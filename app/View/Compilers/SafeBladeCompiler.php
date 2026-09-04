<?php

namespace App\View\Compilers;

use ErrorException;
use Illuminate\View\Compilers\BladeCompiler;

class SafeBladeCompiler extends BladeCompiler
{
    /**
     * Compile the view at the given path.
     *
     * @param  string|null  $path
     * @return void
     */
    public function compile($path = null)
    {
        try {
            parent::compile($path);
        } catch (ErrorException $e) {
            if (str_contains($e->getMessage(), 'touch(): Utime failed')) {
                $compiledPath = $this->getCompiledPath($this->getPath());
                $contents = $this->compileString($this->files->get($this->getPath()));

                if (! empty($this->getPath())) {
                    $contents = $this->appendFilePath($contents);
                }

                $this->files->replace($compiledPath, $contents);

                return;
            }

            throw $e;
        }
    }
}
