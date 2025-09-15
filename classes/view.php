<?php

class View
{
    private $template;
    private $variables = [];

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function setVars(array $variables)
    {
        $this->variables = $variables;
    }

    public function render()
    {
        $template = $this->template;

        extract($this->variables);
        ob_start();
        include(VIEW . $template . '.php');
        $contentPage = ob_get_clean();

        include_once(VIEW . 'layout.php');
    }
}
?>