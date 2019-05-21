<?php

/**
 * The PHP View Engine.
 *
 * @package dionchaika/view
 * @version 1.0.0
 * @license MIT
 * @author Dion Chaika <dionchaika@gmail.com>
 */

namespace Dionchaika\View;

use RuntimeException;
use InvalidArgumentException;

class View
{
    /**
     * The views directory.
     *
     * @var string
     */
    protected $viewsDir;

    /**
     * The compiled views directory.
     *
     * @var string
     */
    protected $compiledViewsDir;

    /**
     * @param string $viewsDir
     * @param string $compiledViewsDir
     */
    public function __construct(string $viewsDir, string $compiledViewsDir)
    {
        $this->viewsDir = rtrim($viewsDir, '/');
        $this->compiledViewsDir = rtrim($compiledViewsDir, '/');
    }

    /**
     * Get the views directory.
     *
     * @return string
     */
    public function getViewsDir(): string
    {
        return $this->viewsDir;
    }

    /**
     * Get the compiled views directory.
     *
     * @return string
     */
    public function getCompiledViewsDir(): string
    {
        return $this->compiledViewsDir;
    }

    /**
     * Render view into the HTML.
     *
     * @param string  $viewName
     * @param mixed[] $viewParameters
     * @return string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function render(string $viewName, array $viewParameters = []): string
    {
        //
    }

    /**
     * Compile view into the PHP.
     *
     * @param string $viewPath
     * @return string
     * @throws \RuntimeException
     */
    public function compile(string $viewPath): string
    {
        $view = @file_get_contents($viewPath);
        if (false === $view) {
            throw new RuntimeException(
                'Unable to get the contents of the file: '.$viewPath.'!'
            );
        }

        $view = $this->compilePlaceholders($view);
        $view = $this->compileIfDirectives($view);
        $view = $this->compileForDirectives($view);
        $view = $this->compilePhpDirectives($view);
        $view = $this->compileViewDirectives($view);

        return $view;
    }

    /**
     * Compile placeholders.
     *
     * @param string $view
     * @return string
     */
    protected function compilePlaceholders(string $view): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php echo {$data}; ?>";
        }, $view);
    }

    /**
     * Compile @if directives.
     *
     * @param string $view
     * @return string
     */
    protected function compileIfDirectives(string $view): string
    {
        $view = preg_replace_callback('/\@if([^\n]+)/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php if ({$data}) { ?>";
        }, $view);

        $view = preg_replace_callback('/\@elseif([^\n]+)/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php } else if ({$data}) { ?>";
        }, $view);

        $view = str_replace('@endif', '<?php } ?>', $view);
        $view = str_replace('@else', '<?php } else { ?>', $view);

        return $view;
    }

    /**
     * Compile @for directives.
     *
     * @param string $view
     * @return string
     */
    protected function compileForDirectives(string $view): string
    {
        $view = preg_replace_callback('/\@for ([^\s]+) in ([^\n]+)/', function ($matches) {
            [$value, $values] = [trim($matches[1]), trim($matches[2])];
            return "<?php foreach ({$values} as {$value}) { ?>";
        }, $view);

        $view = preg_replace_callback('/\@for ([^\s]+)\, ([^\s]+) in ([^\n]+)/', function ($matches) {
            [$key, $value, $values] = [trim($matches[1]), trim($matches[2]), trim($matches[3])];
            return "<?php foreach ({$values} as {$key} => {$value}) { ?>";
        }, $view);

        $view = str_replace('@endfor', '<?php } ?>', $view);

        return $view;
    }

    /**
     * Compile @php directives.
     *
     * @param string $view
     * @return string
     */
    protected function compilePhpDirectives(string $view): string
    {
        return preg_replace_callback('/\@php([^@]+)\@endphp/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php {$data} ?>";
        }, $view);
    }

    /**
     * Compile @view directives.
     *
     * @param string $view
     * @return string
     */
    protected function compileViewDirectives(string $view): string
    {
        return preg_replace_callback('/\@view +([\w.]+)/', function ($matches) {
            $data = $matches[1];
            return "<?php echo \$this->render('{$data}'); ?>";
        }, $view);
    }
}
