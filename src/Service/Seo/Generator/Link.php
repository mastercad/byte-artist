<?php

namespace App\Service\Seo\Generator;

use App\Service\Util\Strings;
use Doctrine\ORM\EntityRepository;

/**
 * This class is responsible for generating unique seo links
 *
 * needed is repository and column name, also the topic name to generate seo link from this.
 */
class Link
{
    private EntityRepository $entityRepository;

    private string $memberName;

    public function __construct(EntityRepository $entityRepository, string $memberName)
    {
        $this->entityRepository = $entityRepository;
        $this->memberName = $memberName;
    }

    public function extendWithSeoLink($entity, $seoLink = null)
    {
        if (empty($seoLink)) {
            $seoLink = (Strings::makeStringLinkSave($entity->{'get'.ucfirst($this->memberName)}()));
        }
        $dbEntity = $this->entityRepository->findOneBy(['seoLink' => $seoLink]);

        if (!$dbEntity) {
            $entity->setSeoLink($seoLink);
            return $entity;
        }

        if ($dbEntity->getId() === $entity->getId()) {
            $entity->setSeoLink($seoLink);
            return $entity;
        }

        return $this->extendWithSeoLink($entity, $this->incrementSeoLink($seoLink));
    }

    private function incrementSeoLink($seoLink)
    {
        if (preg_match('/^(.*?)(\-)([0-9]{1,})$/', $seoLink, $matches)) {
            $newCount = ++$matches[3];
            return $matches[1].'-'.$newCount;
        }
        return $seoLink.'-1';
    }
}
