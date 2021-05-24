<?php

namespace App\Service;

use Doctrine\DBAL\Driver\Connection;
use Twig\Error\LoaderError;
use Twig\Source;

/**
 * @class DatabaseTwigLoader
 *
 * @see https://twig.symfony.com/doc/2.x/recipes.html
 *
 * Loader to load content by column direct from database in twig template
 */
class DatabaseTwigLoader // implements LoaderInterface
{
    protected $dbh;

    public function __construct(Connection $dbh)
    {
        $this->dbh = $dbh;
    }

    public function getSourceContext($name)
    {
        if (false === $source = $this->getValue('source', $name)) {
            throw new LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        return new Source($source, $name);
    }

    public function exists($name)
    {
        return $name === $this->getValue('name', $name);
    }

    public function getCacheKey($name)
    {
        return $name;
    }

    public function isFresh($name, $time)
    {
        if (false === $lastModified = $this->getValue('last_modified', $name)) {
            return false;
        }

        return $lastModified <= $time;
    }

    protected function getValue($column, $name)
    {
        $sth = $this->dbh->prepare('SELECT '.$column.' FROM templates WHERE name = :name');
        $sth->execute([':name' => (string) $name]);

        return $sth->fetchColumn();
    }
}
