<?php

declare(strict_types=1);

namespace Xklid101\Wprecords\Services;


class Template
{
    /**
     * Filesystem path to the src directory
     * @var string
     */
    private string $baseSrcDir;

    private $routing;

    /**
     * Class constructor
     *
     * @param Routing $routing    [description]
     * @param string $baseSrcDir The main src directory
     */
    public function __construct(Routing $routing, string $baseSrcDir) {
        $this->routing = $routing;
        $this->baseSrcDir = $baseSrcDir;
    }

    public function render(string $viewName, array $params = [])
    {
        if (isset($params['routing'])) {
            throw new RuntimeException('"routing" params is reserved! Choose another name of param!');
        }
        if (isset($params['baseSrcDir'])) {
            throw new RuntimeException('"baseSrcDir" params is reserved! Choose another name of param!');
        }
        $params['routing'] = $this->routing;
        $params['baseSrcDir'] = $this->baseSrcDir;

        (new class {
            public function render(string $viewName, array $params = [])
            {
                foreach ($params as $key => $value) {
                    $$key = $value;
                }
                require $baseSrcDir . '/views/' . $viewName . '.php';

            }
        })->render($viewName, $params);
    }

    public function flashMessage($message, $type)
    {
        if (!in_array($type, ['error', 'warning', 'success', 'info'])) {
            $type = 'info';
        }
        add_action(
            'admin_notices',
            function() use ($message, $type) {
                $this->render('adminNotice', ['message' => $message, 'type' => $type]);
            }
        );
    }
}



