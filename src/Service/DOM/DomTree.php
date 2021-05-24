<?php

 /**
  * DomTree.
  *
  * Dump DomDocument based documents, suiting debugging needs
  *
  * @author hakre <http://hakre.wordpress.com/>
  *
  * @see http://stackoverflow.com/questions/12108324/how-to-get-a-raw-from-a-domnodelist/12108732#12108732
  * @see http://stackoverflow.com/questions/684227/debug-a-domdocument-object-in-php/8631974#8631974
  */

namespace App\Service\DOM;

class DomTree
{
    /**
     * @static
     *
     * @param array|DOMNode|DOMNodeList $nodeOrNodes
     * @param int                       $maxDepth    (optional)
     */
    public static function dump($nodeOrNodes, $maxDepth = 0)
    {
        $iterator = new \DOMRecursiveIterator($nodeOrNodes);
        $decorated = new \DOMRecursiveDecoratorStringAsCurrent($iterator);
        $tree = new \RecursiveTreeIterator($decorated);
        $tree->setPrefixPart(\RecursiveTreeIterator::PREFIX_END_LAST, '`');
        $tree->setPrefixPart(\RecursiveTreeIterator::PREFIX_END_HAS_NEXT, '+');
        $maxDepth && $tree->setMaxDepth($maxDepth);
        foreach ($tree as $key => $value) {
            echo htmlentities($value).'<br />';
        }
    }

    /**
     * @static
     *
     * @param DOMNode $node
     * @param int     $maxDepth (optional)
     *
     * @return string
     */
    public static function asString(\DOMNode $node, $maxDepth = 0)
    {
        ob_start();
        self::dump($node, $maxDepth);

        return ob_get_clean();
    }
}
