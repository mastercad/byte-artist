<?php

namespace App\Service\Filter;

class Url
{

    public function filter($string)
    {
        $registry = \Zend_Registry::getInstance();
        $view = $registry->view;

        if (substr($string, 0, 1) == '/') {
            if (substr($string, -1) != "/") {
                $string .= '/';
            }
            return $string;
        } elseif (trim($string) == '#') {
            return '#';
        } else {
            return $view->url(
                array(
                    'module' => 'default',
                    'controller' => 'index',
                    'action' => 'show',
                    'name' => urlencode($string)
                ),
                null,
                true
            ) . "/";
        }
    }
}
