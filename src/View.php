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
     * @param string      $viewsDir
     * @param string|null $compiledViewsDir
     */
    public function __construct(string $viewsDir, ?string $compiledViewsDir = null)
    {
        $this->viewsDir = rtrim($viewsDir, '/');

        $this->compiledViewsDir = (null === $compiledViewsDir)
            ? $this->viewsDir
            : rtrim($compiledViewsDir, '/');
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
        $compiledViewPath = $this->compiledViewsDir
            .\DIRECTORY_SEPARATOR
            .$viewName
            .'.compiled.php';

        if (file_exists($compiledViewPath)) {
            $viewPath = $compiledViewPath;
        } else {
            $viewPath = $this->viewsDir
                .\DIRECTORY_SEPARATOR
                .$this->normalizeViewName($viewName);

            $viewFound = false;
            foreach (['.php', '.html'] as $viewExt) {
                if (file_exists($viewPath.$viewExt)) {
                    if ('.php' !== $viewExt) {
                        $compiledView = $this->compile($viewPath.$viewExt);
                        if (false === @file_put_contents($compiledViewPath, $compiledView)) {
                            throw new RuntimeException(
                                'Unable to put the contents of the file: '.$compiledViewPath.'!'
                            );
                        }

                        $viewPath = $compiledViewPath;
                    } else {
                        $viewPath = $viewPath.$viewExt;
                    }

                    $viewFound = true;
                    break;
                }
            }

            if (!$viewFound) {
                throw new InvalidArgumentException(
                    'View does not exists: '.$viewName.'!'
                );
            }
        }

        ob_start();

        extract($viewParameters, \EXTR_SKIP);
        require $viewPath;

        return ob_get_clean();
    }

    /**
     * Compile view into the PHP.
     *
     * @param string $viewPath
     * @return string
     * @throws \RuntimeException
     */
    protected function compile(string $viewPath): string
    {
        $view = @file_get_contents($viewPath);
        if (false === $view) {
            throw new RuntimeException(
                'Unable to get the contents of the file: '.$viewPath.'!'
            );
        }

        //
        // Compile all comments.
        //
        $view = $this->compileComments($view);

        //
        // Compile all placeholders.
        //
        $view = $this->compilePlaceholders($view);

        //
        // Compile all conditions.
        //
        $view = $this->compileIfConditions($view);
        $view = $this->compileForConditions($view);
        $view = $this->compileIssetConditions($view);
        $view = $this->compileEmptyConditions($view);

        //
        // Compile all directives.
        //
        $view = $this->compilePhpDirectives($view);
        $view = $this->compileViewDirectives($view);

        return $view;
    }

    /**
     * Normalize a view name.
     *
     * @param string $viewName
     * @return string
     */
    protected function normalizeViewName(string $viewName): string
    {
        return str_replace('.', \DIRECTORY_SEPARATOR, $viewName);
    }

    /**
     * Compile view comments.
     *
     * @param string $view
     * @return string
     */
    protected function compileComments(string $view): string
    {
        return preg_replace_callback('/\#\#([^#]+)\#\#/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php /* {$data} */ ?>";
        }, $view);
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
     * Compile @if conditions.
     *
     * @param string $view
     * @return string
     */
    protected function compileIfConditions(string $view): string
    {
        $view = preg_replace_callback('/\@if([^\n]+)/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php if ({$data}) { ?>";
        }, $view);

        $view = preg_replace_callback('/\@elseif([^\n]+)/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php } else if ({$data}) { ?>";
        }, $view);

        $view = str_replace('@else', '<?php } else { ?>', $view);
        $view = str_replace('@endif', '<?php } ?>', $view);

        return $view;
    }

    /**
     * Compile @for conditions.
     *
     * @param string $view
     * @return string
     */
    protected function compileForConditions(string $view): string
    {
        $view = preg_replace_callback('/\@for ([^\s]+) in ([^\n]+)/', function ($matches) {
            $value = trim($matches[1]);
            $values = trim($matches[2]);

            return "<?php foreach ({$values} as {$value}) { ?>";
        }, $view);

        $view = preg_replace_callback('/\@for ([^,]+)\, ([^\s]+) in ([^\n]+)/', function ($matches) {
            $key = trim($matches[1]);
            $value = trim($matches[2]);
            $values = trim($matches[3]);

            return "<?php foreach ({$values} as {$key} => {$value}) { ?>";
        }, $view);

        return str_replace('@endfor', '<?php } ?>', $view);
    }

    /**
     * Compile @isset conditions.
     *
     * @param string $view
     * @return string
     */
    protected function compileIssetConditions(string $view): string
    {
        $view = preg_replace_callback('/\@isset([^\n]+)/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php if (isset({$data})) { ?>";
        }, $view);

        return str_replace('@endisset', '<?php } ?>', $view);
    }

    /**
     * Compile @empty conditions.
     *
     * @param string $view
     * @return string
     */
    protected function compileEmptyConditions(string $view): string
    {
        $view = preg_replace_callback('/\@empty([^\n]+)/', function ($matches) {
            $data = trim($matches[1]);
            return "<?php if (empty({$data})) { ?>";
        }, $view);

        return str_replace('@endempty', '<?php } ?>', $view);
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
        return preg_replace_callback('/\@view ([\w.]+)/', function ($matches) {
            $data = $matches[1];
            return "<?php echo \$this->render('{$data}'); ?>";
        }, $view);
    }
}
